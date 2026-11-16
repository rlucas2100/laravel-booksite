<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->refuseIfProtected($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($data);

        return back()->with('status', 'Profile saved.');
    }

    public function password(Request $request): RedirectResponse
    {
        $this->refuseIfProtected($request);

        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update(['password' => $data['password']]);   // hashed by the model's cast

        return back()->with('status', 'Password changed.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->refuseIfProtected($request);

        $request->validateWithBag('deletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();   // uploads, reviews and comments go with the account

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Your account was deleted. Goodbye, and thanks for reading.');
    }

    /** The shared demo accounts stay as they are, so the next visitor can still sign in. */
    private function refuseIfProtected(Request $request): void
    {
        abort_if($request->user()->protected, 403, 'This is a shared demo account, so its details can\'t be changed. Create your own free account to try this.');
    }
}
