@php
    use App\Models\Comment;
@endphp
<section id="comments" class="shell mt-16">
    <h2 class="flex items-center gap-3 text-3xl font-black sm:text-4xl">
        Conversation <span class="chip chip-tone text-sm">{{ $entry->comments_count }}</span>
    </h2>

    <div class="mt-6 max-w-3xl">
        @auth
            <form method="POST" action="{{ route('comments.store', $entry) }}" class="flex gap-3" x-data="counted({{ Comment::MAX_LENGTH }})">
                @csrf
                <x-avatar :user="auth()->user()"/>
                <div class="min-w-0 flex-1">
                    <label class="sr-only" for="comment-body">Add a comment</label>
                    <textarea id="comment-body" class="input" name="body" rows="3" maxlength="{{ Comment::MAX_LENGTH }}" required x-ref="text" @input="count"
                              placeholder="Ask a question, share a thought, start an argument…">{{ old('parent_id') ? '' : old('body') }}</textarea>
                    <x-error for="body" bag="comment"/>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <p class="hint mt-0 tabular-nums"><span x-text="used">0</span> / {{ Comment::MAX_LENGTH }}</p>
                        <button type="submit" class="btn btn-tone"><x-icon name="chat" size="h-4.5 w-4.5"/> Post comment</button>
                    </div>
                </div>
            </form>
        @else
            <p class="card flex flex-wrap items-center gap-3 p-4">
                <x-icon name="chat"/>
                <span class="flex-1">Want to join in?</span>
                <a class="btn btn-sm btn-primary" href="{{ route('login') }}">Sign in</a>
                <a class="btn btn-sm" href="{{ route('register') }}">Join free</a>
            </p>
        @endauth

        @if ($comments->isEmpty())
            <p class="mt-8 text-soft">Nobody has said anything yet. Start the conversation.</p>
        @else
            <ul class="mt-8 space-y-6">
                @foreach ($comments as $comment)
                    <li id="comment-{{ $comment->id }}" class="flex gap-3" x-data="{ replying: {{ (int) old('parent_id') === $comment->id ? 'true' : 'false' }} }">
                        <x-avatar :user="$comment->user"/>

                        <div class="min-w-0 flex-1">
                            @include('catalogue.partials.comment-bubble', ['comment' => $comment])

                            <div class="mt-1.5 flex flex-wrap items-center gap-4 pl-1 text-sm font-bold">
                                @auth
                                    <button type="button" class="inline-flex cursor-pointer items-center gap-1 hover:underline" @click="replying = ! replying; if (replying) $nextTick(() => $refs.reply.focus())">
                                        <x-icon name="reply" size="h-4 w-4"/> Reply
                                    </button>
                                @endauth
                                @include('catalogue.partials.comment-delete', ['comment' => $comment])
                            </div>

                            {{-- Replies, one level deep --}}
                            @if ($comment->replies->isNotEmpty())
                                <ul class="mt-4 space-y-4">
                                    @foreach ($comment->replies as $reply)
                                        <li id="comment-{{ $reply->id }}" class="flex gap-3">
                                            <x-avatar :user="$reply->user" small/>
                                            <div class="min-w-0 flex-1">
                                                @include('catalogue.partials.comment-bubble', ['comment' => $reply])
                                                <div class="mt-1.5 pl-1 text-sm font-bold">
                                                    @include('catalogue.partials.comment-delete', ['comment' => $reply])
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @auth
                                <form method="POST" action="{{ route('comments.store', $entry) }}" class="mt-4 flex gap-3" x-show="replying" x-cloak>
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <x-avatar :user="auth()->user()" small/>
                                    <div class="min-w-0 flex-1">
                                        <label class="sr-only" for="reply-{{ $comment->id }}">Reply to {{ $comment->user->name }}</label>
                                        <textarea id="reply-{{ $comment->id }}" class="input min-h-0" name="body" rows="2" maxlength="{{ Comment::MAX_LENGTH }}" required x-ref="reply"
                                                  placeholder="Reply to {{ $comment->user->name }}…">{{ (int) old('parent_id') === $comment->id ? old('body') : '' }}</textarea>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button" class="btn btn-sm btn-plain" @click="replying = false">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-tone">Post reply</button>
                                        </div>
                                    </div>
                                </form>
                            @endauth
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
