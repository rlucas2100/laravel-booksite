<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Entry;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/** The sample readers' reviews (0 to 5 stars) and the comment threads under the entries. */
class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $data = require __DIR__.'/data/community.php';

        $readers = User::query()->where('email', 'like', '%@example.com')->get()
            ->keyBy(fn (User $user) => strstr($user->email, '@', true));     // 'maeve' => User, 'desk' => User...
        $entries = Entry::query()->get()->keyBy('slug');

        // Only for an empty site: never add to, or replace, what real readers have written.
        if (Review::query()->exists() || Comment::query()->exists()) {
            return;
        }

        foreach ($data['reviews'] as $slug => $reviews) {
            if (! $entry = $entries->get($slug)) {
                continue;   // e.g. a video whose file wasn't there to seed
            }

            foreach ($reviews as $i => [$reader, $stars, $title, $body]) {
                $when = $this->sometimeAfter($entry, $slug.$reader, $i);

                (new Review)->forceFill([
                    'entry_id' => $entry->id,
                    'user_id' => $readers[$reader]->id,
                    'rating' => $stars,
                    'title' => $title,
                    'body' => $body,
                    'created_at' => $when,
                    'updated_at' => $when,
                ])->save();
            }
        }

        foreach ($data['comments'] as $slug => $threads) {
            if (! $entry = $entries->get($slug)) {
                continue;
            }

            foreach ($threads as $i => [$reader, $body, $replies]) {
                $when = $this->sometimeAfter($entry, $slug.$reader.'c', $i);
                $comment = $this->comment($entry, $readers[$reader], $body, $when);

                foreach ($replies as $n => [$replier, $reply]) {
                    $this->comment($entry, $readers[$replier], $reply, $when->copy()->addHours(2 + $n * 7)->min(now()), $comment);
                }
            }
        }
    }

    private function comment(Entry $entry, User $user, string $body, Carbon $when, ?Comment $parent = null): Comment
    {
        $comment = (new Comment)->forceFill([
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'parent_id' => $parent?->id,
            'body' => $body,
            'created_at' => $when,
            'updated_at' => $when,
        ]);

        $comment->save();

        return $comment;
    }

    /** A time between the entry going up and now: different for each reader, the same on every run. */
    private function sometimeAfter(Entry $entry, string $key, int $order): Carbon
    {
        $span = max(2, (int) $entry->published_at->diffInHours(now()));
        $hours = 1 + (crc32($key) + $order * 13) % ($span - 1);

        return $entry->published_at->copy()->addHours($hours)->addMinutes(crc32($key.'m') % 60)->min(now()->subMinutes(5));
    }
}
