<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $search = is_string($request->query('q')) ? trim($request->query('q')) : '';

        return view('admin.users.index', [
            'users' => User::query()
                ->visibleHere()   // in the public demo: seeded accounts plus the ones this visitor made
                ->with('roles')
                ->withCount(['entries', 'reviews', 'comments'])
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')))
                ->orderBy('name')
                ->paginate(20)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // Leave the password boxes empty to keep the one they have.
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['nullable', Rule::in(['member', 'admin'])],
        ]);

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];   // hashed by the model's cast
        }

        $user->save();

        // Only the owner hands out the Admin role, and the owner's own role never changes here.
        if (isset($data['role']) && ! $user->isOwner() && $request->user()->can('changeRole', $user)) {
            $user->syncRoles($data['role'] === 'admin' ? [User::ADMIN_ROLE] : []);
        }

        return redirect()->route('admin.users.index')->with('status', $user->name.' was updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        abort_if($user->is($request->user()), 403, 'You can\'t delete the account you are signed in with.');

        $user->delete();   // their uploads, reviews and comments go too

        return redirect()->route('admin.users.index')->with('status', $user->name.' was deleted.');
    }
}
