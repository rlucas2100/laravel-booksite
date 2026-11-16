<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewsAndCommentsTest extends TestCase
{
    use RefreshDatabase;

    /* ------------------------------------------------------------ reviews: 0 to 5 stars */

    public function test_guests_cannot_review(): void
    {
        $entry = $this->entry();

        $this->post(route('reviews.store', $entry), ['rating' => 5])->assertRedirect('/login');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_a_member_can_give_any_whole_number_of_stars_from_0_to_5(): void
    {
        $entry = $this->entry();

        foreach (range(0, 5) as $stars) {
            $this->actingAs($this->member())
                ->post(route('reviews.store', $entry), ['rating' => $stars, 'title' => 'My verdict', 'body' => 'Some thoughts.'])
                ->assertRedirect($entry->url().'#reviews');
        }

        $this->assertSame([0, 1, 2, 3, 4, 5], Review::query()->orderBy('rating')->pluck('rating')->all());

        // (0+1+2+3+4+5) / 6 = 2.5
        $this->get($entry->url())->assertOk()->assertSee('2.5')->assertSee('6 reviews');
    }

    public function test_stars_outside_0_to_5_are_refused(): void
    {
        $entry = $this->entry();
        $this->actingAs($this->member());

        foreach ([6, -1, 2.5, 'lots', null] as $bad) {
            $this->post(route('reviews.store', $entry), ['rating' => $bad])->assertSessionHasErrors('rating');
        }

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_a_rating_needs_no_words_and_reviewing_again_replaces_the_first_review(): void
    {
        $entry = $this->entry();
        $member = $this->member();

        $this->actingAs($member)->post(route('reviews.store', $entry), ['rating' => 2])->assertSessionHasNoErrors();
        $this->actingAs($member)->post(route('reviews.store', $entry), ['rating' => 5, 'title' => 'I was wrong'])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', ['user_id' => $member->id, 'rating' => 5, 'title' => 'I was wrong']);
    }

    public function test_nobody_reviews_their_own_upload(): void
    {
        $uploader = $this->member();
        $entry = $this->entry(attributes: ['user_id' => $uploader->id]);

        $this->actingAs($uploader)->post(route('reviews.store', $entry), ['rating' => 5])->assertForbidden();
    }

    public function test_a_review_is_deleted_by_its_writer_or_a_moderator_and_nobody_else(): void
    {
        $writer = $this->member();
        $review = Review::factory()->create(['user_id' => $writer->id]);

        $this->actingAs($this->member())->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->assertModelExists($review);

        $this->actingAs($this->admin())->delete(route('reviews.destroy', $review))->assertRedirect();
        $this->assertModelMissing($review);

        $second = Review::factory()->create(['user_id' => $writer->id]);
        $this->actingAs($writer)->delete(route('reviews.destroy', $second))->assertRedirect();
        $this->assertModelMissing($second);
    }

    /* ------------------------------------------------------------ comments and replies */

    public function test_guests_cannot_comment_but_can_read_the_conversation(): void
    {
        $comment = Comment::factory()->create(['body' => 'What a twist at the end.']);

        $this->post(route('comments.store', $comment->entry), ['body' => 'Hello'])->assertRedirect('/login');
        $this->get($comment->entry->url())->assertSee('What a twist at the end.');
    }

    public function test_a_member_comments_and_another_replies(): void
    {
        $entry = $this->entry();
        $asker = $this->member();

        $this->actingAs($asker)->post(route('comments.store', $entry), ['body' => 'Is it worth reading?'])->assertSessionHasNoErrors();
        $question = Comment::query()->firstOrFail();

        $this->actingAs($this->member())->post(route('comments.store', $entry), ['body' => 'Yes, start tonight.', 'parent_id' => $question->id])->assertSessionHasNoErrors();
        $answer = Comment::query()->latest('id')->firstOrFail();

        $this->assertSame($question->id, $answer->parent_id);

        // A reply to a reply joins the same thread: replies stay one level deep.
        $this->actingAs($asker)->post(route('comments.store', $entry), ['body' => 'Thanks!', 'parent_id' => $answer->id]);
        $this->assertSame($question->id, Comment::query()->latest('id')->value('parent_id'));

        $this->get($entry->url())->assertSeeInOrder(['Is it worth reading?', 'Yes, start tonight.', 'Thanks!']);
    }

    public function test_a_comment_cannot_be_empty_too_long_or_a_reply_to_another_entrys_comment(): void
    {
        $entry = $this->entry();
        $elsewhere = Comment::factory()->create();
        $this->actingAs($this->member());

        $this->post(route('comments.store', $entry), ['body' => ''])->assertSessionHasErrorsIn('comment', 'body');
        $this->post(route('comments.store', $entry), ['body' => str_repeat('a', Comment::MAX_LENGTH + 1)])->assertSessionHasErrorsIn('comment', 'body');
        $this->post(route('comments.store', $entry), ['body' => 'Sneaky', 'parent_id' => $elsewhere->id])->assertNotFound();

        $this->assertDatabaseCount('comments', 1);
    }

    public function test_deleting_a_comment_takes_its_replies_with_it(): void
    {
        $writer = $this->member();
        $comment = Comment::factory()->create(['user_id' => $writer->id]);
        $reply = Comment::factory()->create(['entry_id' => $comment->entry_id, 'parent_id' => $comment->id]);

        $this->actingAs($this->member())->delete(route('comments.destroy', $comment))->assertForbidden();
        $this->actingAs($writer)->delete(route('comments.destroy', $comment))->assertRedirect();

        $this->assertModelMissing($comment);
        $this->assertModelMissing($reply);
    }

    public function test_what_people_type_is_shown_as_text_never_run_as_html(): void
    {
        $entry = $this->entry();

        $this->actingAs($this->member())->post(route('comments.store', $entry), ['body' => '<script>alert("x")</script>']);

        $this->get($entry->url())->assertDontSee('<script>alert("x")</script>', false)->assertSee('<script>alert("x")</script>');
    }
}
