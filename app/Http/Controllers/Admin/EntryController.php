<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EntryType;
use App\Http\Controllers\Controller;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The whole catalogue in one table. Editing and deleting use the normal entry pages. */
class EntryController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('manage-entries'), 403);

        $search = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $type = EntryType::tryFrom((string) $request->query('type'));

        return view('admin.entries', [
            'entries' => Entry::query()
                ->withRatings()
                ->with(['user', 'category'])
                ->when($type, fn ($query) => $query->ofType($type))
                ->when($search !== '', fn ($query) => $query->search($search))
                ->latest()->latest('id')
                ->paginate(20)->withQueryString(),
            'search' => $search,
            'type' => $type,
        ]);
    }
}
