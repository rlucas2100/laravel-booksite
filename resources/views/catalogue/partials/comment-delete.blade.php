{{-- The delete link under a comment, for its writer and for moderators. Needs $comment. --}}
@can('delete', $comment)
    <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline" data-confirm="Delete this comment?">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex cursor-pointer items-center gap-1 text-soft hover:text-ink hover:underline">
            <x-icon name="trash" size="h-4 w-4"/> Delete
        </button>
    </form>
@endcan
