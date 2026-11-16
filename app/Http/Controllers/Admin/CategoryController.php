<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Entry;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        return view('admin.categories', [
            'categories' => Category::query()->withCount('entries')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $data = $this->validated($request);

        Category::create($data + ['slug' => Category::uniqueSlug($data['name'])]);

        return back()->with('status', 'Category "'.$data['name'].'" added.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $data = $this->validated($request, $category);

        $category->update($data + ['slug' => Category::uniqueSlug($data['name'], $category->id)]);

        return back()->with('status', 'Category saved.');
    }

    /**
     * A category with entries in it can't just vanish: they are moved to
     * another category first, then the empty one is deleted.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        // Counted without the demo filter, so entries hidden from this visitor are moved too.
        $entries = Entry::withoutGlobalScopes()->where('category_id', $category->id);

        if ($entries->exists()) {
            $request->validate([
                'move_to' => ['required', 'integer', Rule::notIn([$category->id]), function (string $attribute, mixed $value, Closure $fail) {
                    if (! Category::query()->whereKey($value)->exists()) {
                        $fail('Choose a category to move the entries to.');
                    }
                }],
            ], ['move_to.required' => 'Choose a category to move the entries to.']);

            $entries->update(['category_id' => $request->integer('move_to')]);
        }

        $category->delete();

        return back()->with('status', 'Category "'.$category->name.'" deleted.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:40', function (string $attribute, mixed $value, Closure $fail) use ($category) {
                $taken = Category::query()
                    ->whereRaw('lower(name) = ?', [mb_strtolower((string) $value)])
                    ->when($category, fn ($query) => $query->whereKeyNot($category->id))
                    ->exists();

                if ($taken) {
                    $fail('There is already a category with that name.');
                }
            }],
            'blurb' => ['nullable', 'string', 'max:160'],
        ]);
    }
}
