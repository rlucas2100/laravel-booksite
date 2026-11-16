<?php

namespace App\Http\Controllers;

use App\Enums\EntryType;
use App\Http\Requests\EntryRequest;
use App\Models\Category;
use App\Models\Entry;
use App\Support\Sandbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * A member's own uploads: add, edit, delete. Every type goes through the same
 * four methods; EntryRequest and EntryType know what each type needs.
 */
class EntryController extends Controller
{
    /** "My uploads" (the old "My Books" page). */
    public function mine(Request $request): View
    {
        return view('entries.mine', [
            'entries' => $request->user()->entries()->withRatings()->with('category')->sorted('new')->paginate(config('shop.per_page')),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Entry::class);

        return view('entries.form', [
            'entry' => null,
            'type' => EntryType::tryFrom((string) $request->query('type')) ?? EntryType::Book,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(EntryRequest $request): RedirectResponse
    {
        $this->authorize('create', Entry::class);

        // The public demo gives each visitor a few uploads, so the disk can't be filled.
        if (! Sandbox::unrestricted() && Sandbox::count(Entry::class) >= config('shop.sandbox_max_uploads')) {
            throw ValidationException::withMessages([
                'title' => 'The demo allows '.config('shop.sandbox_max_uploads').' uploads per visitor. Delete one of yours to add another.',
            ]);
        }

        $entry = new Entry($request->safe()->only(['title', 'creator', 'category_id', 'year', 'description', 'body', 'link']));
        $entry->type = $request->entryType();
        $entry->user_id = $request->user()->id;        // the uploader; never changed by a later edit
        $entry->slug = Entry::uniqueSlug($entry->title);
        $entry->published_at = now();

        $this->storeFiles($request, $entry);
        $entry->save();

        return redirect()->to($entry->url())->with('status', 'It\'s on the shelf. Thanks for sharing!');
    }

    public function edit(Entry $entry): View
    {
        $this->authorize('update', $entry);

        return view('entries.form', [
            'entry' => $entry,
            'type' => $entry->type,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(EntryRequest $request, Entry $entry): RedirectResponse
    {
        $this->authorize('update', $entry);

        $entry->fill($request->safe()->only(['title', 'creator', 'category_id', 'year', 'description', 'body', 'link']));

        // The address only changes when the title does.
        if ($entry->isDirty('title')) {
            $entry->slug = Entry::uniqueSlug($entry->title, $entry->id);
        }

        if ($request->boolean('remove_cover') && $entry->cover_path) {
            Storage::disk(Entry::DISK)->delete($entry->cover_path);
            $entry->cover_path = null;
        }

        $this->storeFiles($request, $entry);
        $entry->save();

        return redirect()->to($entry->url())->with('status', 'Changes saved.');
    }

    public function destroy(Request $request, Entry $entry): RedirectResponse
    {
        $this->authorize('delete', $entry);

        $entry->delete();   // its reviews, comments and files go with it

        $back = $request->user()->id === $entry->user_id ? route('entries.mine') : route('admin.entries');

        return redirect()->to($back)->with('status', '"'.$entry->title.'" was taken off the shelf.');
    }

    /** Saves a newly chosen cover and file under random names, replacing any old ones. */
    private function storeFiles(EntryRequest $request, Entry $entry): void
    {
        $disk = Storage::disk(Entry::DISK);

        if ($cover = $request->file('cover')) {
            if ($entry->cover_path) {
                $disk->delete($entry->cover_path);
            }

            $entry->cover_path = $cover->store('covers', Entry::DISK);
        }

        if ($entry->type->hasFile() && ($file = $request->file('file'))) {
            if ($entry->file_path) {
                $disk->delete($entry->file_path);
            }

            $entry->file_path = $file->store('files/'.$entry->type->value, Entry::DISK);
            $entry->file_size = $file->getSize();
            $entry->duration = null;
        }

        // Video and audio length is read by the browser when the file is chosen.
        if (in_array($entry->type, [EntryType::Video, EntryType::Audio], true) && $request->filled('duration')) {
            $entry->duration = $request->integer('duration');
        }
    }
}
