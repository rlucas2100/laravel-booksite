<?php

namespace App\Http\Controllers;

use App\Enums\EntryType;
use App\Models\Entry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Hands out covers and files. Uploads are kept outside the public folder, so
 * every request passes through here, where the rules are: books are for
 * members only, and the Content-Type comes from our own list, never the upload.
 */
class MediaController extends Controller
{
    public function cover(Entry $entry): BinaryFileResponse
    {
        abort_unless($entry->cover_path && Storage::disk(Entry::DISK)->exists($entry->cover_path), 404);

        return response()->file(Storage::disk(Entry::DISK)->path($entry->cover_path), [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }

    /** Plays video and audio in the page (with seeking), or downloads the file. */
    public function file(Request $request, Entry $entry): BinaryFileResponse|RedirectResponse
    {
        abort_unless($entry->file_path && Storage::disk(Entry::DISK)->exists($entry->file_path), 404);

        if ($entry->type === EntryType::Book && ! $request->user()) {
            $request->session()->put('url.intended', $entry->url());

            return redirect()->route('login')->with('status', 'Sign in to download books. It\'s free.');
        }

        // Something with a price is only for the member who bought it (or uploaded it).
        if (! $entry->isFree()) {
            if (! $request->user()) {
                $request->session()->put('url.intended', $entry->url());

                return redirect()->route('login')->with('status', 'Sign in to buy this, or to open it if it is already yours.');
            }

            if (! $request->user()->mayOpen($entry)) {
                return redirect()->to($entry->url())->with('status', 'That one is '.$entry->priceLabel().'. Add it to your basket to get it.');
            }
        }

        $path = Storage::disk(Entry::DISK)->path($entry->file_path);
        $headers = [
            'Content-Type' => $entry->fileContentType(),
            'Cache-Control' => 'private, max-age=3600',
        ];

        if ($entry->type === EntryType::Book || $request->boolean('download')) {
            return response()->download($path, $entry->downloadName(), $headers);
        }

        // BinaryFileResponse answers "Range" requests, which is what lets a player skip ahead.
        return response()->file($path, $headers);
    }
}
