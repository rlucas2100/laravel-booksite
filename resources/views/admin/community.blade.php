<x-admin title="Reviews & comments">
    <div class="grid gap-8 xl:grid-cols-2">
        <section>
            <h2 class="text-2xl font-black">Reviews <span class="chip align-middle">{{ $reviews->total() }}</span></h2>

            <ul class="mt-4 space-y-4">
                @forelse ($reviews as $review)
                    <li class="card p-4">
                        <div class="flex items-start gap-3">
                            <x-avatar :user="$review->user" small/>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm"><b>{{ $review->user->name }}</b> on <a class="font-bold hover:underline" href="{{ $review->entry->url() }}#review-{{ $review->id }}">{{ $review->entry->title }}</a></p>
                                <p class="flex flex-wrap items-center gap-2 text-sm text-soft">
                                    <x-stars :value="$review->rating" size=".9rem"/> {{ $review->rating }}/5 · {{ $review->created_at->diffForHumans() }}
                                    @if ($review->isSandboxed()) <span class="chip bg-sun text-night">a visitor’s</span> @endif
                                </p>
                            </div>
                            @can('delete', $review)
                                <form method="POST" action="{{ route('reviews.destroy', $review) }}" data-confirm="Remove this review?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            @else
                                <span class="text-soft" title="Locked in the public demo"><x-icon name="lock" size="h-4 w-4"/></span>
                            @endcan
                        </div>
                        @if ($review->title)
                            <p class="mt-2 font-bold">{{ $review->title }}</p>
                        @endif
                        @if ($review->body)
                            <p class="mt-1 text-sm">{{ Str::limit($review->body, 220) }}</p>
                        @endif
                    </li>
                @empty
                    <li class="text-soft">No reviews yet.</li>
                @endforelse
            </ul>

            {{ $reviews->links() }}
        </section>

        <section>
            <h2 class="text-2xl font-black">Comments <span class="chip align-middle">{{ $comments->total() }}</span></h2>

            <ul class="mt-4 space-y-4">
                @forelse ($comments as $comment)
                    <li class="card p-4">
                        <div class="flex items-start gap-3">
                            <x-avatar :user="$comment->user" small/>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm"><b>{{ $comment->user->name }}</b> {{ $comment->parent_id ? 'replied' : 'commented' }} on <a class="font-bold hover:underline" href="{{ $comment->entry->url() }}#comment-{{ $comment->id }}">{{ $comment->entry->title }}</a></p>
                                <p class="text-sm text-soft">
                                    {{ $comment->created_at->diffForHumans() }}
                                    @if ($comment->isSandboxed()) <span class="chip bg-sun text-night">a visitor’s</span> @endif
                                </p>
                            </div>
                            @can('delete', $comment)
                                <form method="POST" action="{{ route('comments.destroy', $comment) }}" data-confirm="Delete this comment?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            @else
                                <span class="text-soft" title="Locked in the public demo"><x-icon name="lock" size="h-4 w-4"/></span>
                            @endcan
                        </div>
                        <p class="mt-2 text-sm">{{ Str::limit($comment->body, 220) }}</p>
                    </li>
                @empty
                    <li class="text-soft">No comments yet.</li>
                @endforelse
            </ul>

            {{ $comments->links() }}
        </section>
    </div>
</x-admin>
