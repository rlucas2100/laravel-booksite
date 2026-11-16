{{-- One comment or reply, drawn as a speech bubble. Needs $comment. --}}
<div class="bubble">
    <p class="flex flex-wrap items-center gap-x-2 text-sm">
        <b>{{ $comment->user->name }}</b>
        <time class="text-soft" datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
        @if ($comment->isSandboxed())
            <span class="chip bg-sun text-night" title="This is the public demo: only your browser shows this comment.">Only you can see this</span>
        @endif
    </p>
    <p class="mt-1 leading-relaxed whitespace-pre-line">{{ $comment->body }}</p>
</div>
