<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Entry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /** Add a comment to an entry, or a reply to one of its comments. */
    public function store(Request $request, Entry $entry): RedirectResponse
    {
        $data = $request->validateWithBag('comment', [
            'body' => ['required', 'string', 'max:'.Comment::MAX_LENGTH],
            'parent_id' => ['nullable', 'integer'],
        ], [
            'body.required' => 'Write something first.',
        ]);

        $comment = new Comment(['body' => $data['body']]);
        $comment->entry_id = $entry->id;
        $comment->user_id = $request->user()->id;

        if (! empty($data['parent_id'])) {
            // The parent must be a comment on this same entry. A reply to a reply joins the same thread.
            $parent = $entry->comments()->findOrFail($data['parent_id']);
            $comment->parent_id = $parent->parent_id ?? $parent->id;
        }

        $comment->save();

        return redirect()->to($entry->url().'#comment-'.$comment->id)->with('status', 'Comment posted.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();   // replies to it go too

        return back()->with('status', 'The comment was removed.');
    }
}
