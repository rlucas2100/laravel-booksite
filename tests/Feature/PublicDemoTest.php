<?php

namespace Tests\Feature;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Entry;
use App\Models\Review;
use App\Models\User;
use App\Support\Sandbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * With SHOP_DEMO=true the site can be left open to the public: whatever a
 * visitor makes is only shown to their own browser, the shop's own content is
 * locked, and visitors' things are cleared out after a day.
 */
class PublicDemoTest extends TestCase
{
    use RefreshDatabase;

    private const ALICE = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';   // two visitors' cookies

    private const BOB = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';

    private User $reader;   // the shared demo account both visitors sign in with

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Entry::DISK);

        // Made while the demo is off, so these count as the shop's own (public) records.
        $this->reader = $this->member();

        config(['shop.demo' => true]);
    }

    private function as(string $visitor, ?User $user = null): static
    {
        return $this->actingAs($user ?? $this->reader)->withCookie(Sandbox::COOKIE, $visitor);
    }

    public function test_a_visitors_review_and_comment_are_only_visible_in_their_own_browser(): void
    {
        $entry = Sandbox::pause(fn () => $this->entry());

        $this->as(self::ALICE)->post(route('reviews.store', $entry), ['rating' => 0, 'title' => 'Alice was here']);
        $this->as(self::ALICE)->post(route('comments.store', $entry), ['body' => 'A comment from Alice']);

        $this->assertSame(self::ALICE, Review::withoutGlobalScopes()->value('sandbox'));

        $this->as(self::ALICE)->get($entry->url())->assertSee('Alice was here')->assertSee('A comment from Alice')->assertSee('Only you can see this');

        // Bob uses the very same demo account from another browser, and sees none of it.
        $this->as(self::BOB)->get($entry->url())->assertDontSee('Alice was here')->assertDontSee('A comment from Alice');

        // Nor does somebody who isn't signed in.
        $this->app['auth']->forgetGuards();
        $this->withUnencryptedCookies([])->get($entry->url())->assertDontSee('Alice was here');
    }

    public function test_each_visitor_gets_their_own_review_on_a_shared_account(): void
    {
        $entry = Sandbox::pause(fn () => $this->entry());

        $this->as(self::ALICE)->post(route('reviews.store', $entry), ['rating' => 1]);
        $this->as(self::BOB)->post(route('reviews.store', $entry), ['rating' => 5]);

        $this->assertSame(2, Review::withoutGlobalScopes()->count());   // Bob's did not overwrite Alice's
        $this->as(self::ALICE)->get($entry->url())->assertSee('1 review');
    }

    public function test_the_shops_own_content_is_locked_even_for_the_demo_admin(): void
    {
        $admin = Sandbox::pause(fn () => $this->admin());
        $entry = Sandbox::pause(fn () => $this->entry(EntryType::Book, ['title' => 'A Shop Book']));
        $review = Sandbox::pause(fn () => Review::factory()->create(['entry_id' => $entry->id]));
        $category = Sandbox::pause(fn () => Category::factory()->create());
        $member = Sandbox::pause(fn () => $this->member());

        $this->as(self::ALICE, $admin);

        $this->get('/entries/'.$entry->slug.'/edit')->assertForbidden();
        $this->delete('/entries/'.$entry->slug)->assertForbidden();
        $this->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->put(route('admin.categories.update', $category), ['name' => 'Vandalised'])->assertForbidden();
        $this->delete(route('admin.users.destroy', $member))->assertForbidden();

        $this->assertModelExists($entry);
        $this->assertModelExists($review);
        $this->assertModelExists($member);

        $this->get('/admin/catalogue')->assertOk()->assertSee('A Shop Book')->assertSee('Locked');
    }

    public function test_a_visitor_can_still_edit_and_delete_what_they_made_themselves(): void
    {
        $admin = Sandbox::pause(fn () => $this->admin());
        $category = Sandbox::pause(fn () => Category::factory()->create());

        $this->as(self::ALICE)->post('/upload', [
            'type' => 'article', 'title' => 'Alice Writes', 'creator' => 'Alice', 'category_id' => $category->id,
            'description' => 'A post written in the public demo.', 'body' => 'Hello.',
        ])->assertRedirect('/blog/alice-writes');

        // Alice sees it on the shelf; Bob and guests don't, even with the address.
        $this->as(self::ALICE)->get('/blog')->assertSee('Alice Writes');
        $this->as(self::BOB)->get('/blog')->assertDontSee('Alice Writes');
        $this->as(self::BOB)->get('/blog/alice-writes')->assertNotFound();

        // As the demo admin in the same browser she can manage her own upload...
        $this->as(self::ALICE, $admin)->get('/entries/alice-writes/edit')->assertOk();
        // ...but an admin in another browser cannot even find it.
        $this->as(self::BOB, $admin)->get('/entries/alice-writes/edit')->assertNotFound();

        $this->as(self::ALICE)->delete('/entries/alice-writes')->assertRedirect();
        $this->assertSame(0, Entry::withoutGlobalScopes()->count());
    }

    public function test_a_visitor_can_only_upload_a_few_things(): void
    {
        config(['shop.sandbox_max_uploads' => 2]);
        $category = Sandbox::pause(fn () => Category::factory()->create());
        $form = fn (int $n) => ['type' => 'article', 'title' => 'Post '.$n, 'creator' => 'Me', 'category_id' => $category->id, 'description' => 'A description long enough.', 'body' => 'Text'];

        $this->as(self::ALICE)->post('/upload', $form(1))->assertSessionHasNoErrors();
        $this->as(self::ALICE)->post('/upload', $form(2))->assertSessionHasNoErrors();
        $this->as(self::ALICE)->post('/upload', $form(3))->assertSessionHasErrors('title');

        $this->as(self::BOB)->post('/upload', $form(4))->assertSessionHasNoErrors();   // Bob has his own allowance
    }

    public function test_the_admins_member_list_never_shows_another_visitors_account(): void
    {
        $admin = Sandbox::pause(fn () => $this->admin());

        // Alice registers in her browser.
        $this->withCookie(Sandbox::COOKIE, self::ALICE)->post('/register', [
            'name' => 'Alice Private', 'email' => 'alice.private@example.com',
            'password' => 'a-long-password', 'password_confirmation' => 'a-long-password',
        ])->assertRedirect('/');

        $alice = User::query()->where('email', 'alice.private@example.com')->firstOrFail();
        $this->assertSame(self::ALICE, $alice->sandbox);

        $this->as(self::BOB, $admin)->get('/admin/users')->assertOk()->assertDontSee('alice.private@example.com');
        $this->as(self::BOB, $admin)->get(route('admin.users.edit', $alice))->assertForbidden();

        $this->as(self::ALICE, $admin)->get('/admin/users')->assertSee('alice.private@example.com');
    }

    public function test_the_owner_sees_everything_and_their_own_posts_are_public(): void
    {
        $owner = Sandbox::pause(fn () => $this->owner());
        $entry = Sandbox::pause(fn () => $this->entry());

        $this->as(self::ALICE)->post(route('comments.store', $entry), ['body' => 'From a visitor']);
        $this->as(self::BOB, $owner)->post(route('comments.store', $entry), ['body' => 'From the owner']);

        $this->as(self::BOB, $owner)->get($entry->url())->assertSee('From a visitor')->assertSee('From the owner');
        $this->as(self::ALICE)->get($entry->url())->assertSee('From the owner');

        $this->assertNull(Comment::withoutGlobalScopes()->where('body', 'From the owner')->value('sandbox'));
    }

    public function test_visitors_things_are_cleared_out_after_a_day_and_the_shop_is_left_alone(): void
    {
        $category = Sandbox::pause(fn () => Category::factory()->create());
        $shopBook = Sandbox::pause(fn () => $this->entry(EntryType::Book, ['title' => 'Stays']));

        $this->as(self::ALICE)->post('/upload', [
            'type' => 'book', 'title' => 'Goes', 'creator' => 'Alice', 'category_id' => $category->id,
            'description' => 'Uploaded by a visitor to the demo.',
            'file' => UploadedFile::fake()->create('goes.pdf', 50, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $this->as(self::ALICE)->post(route('reviews.store', $shopBook), ['rating' => 3]);

        $upload = Entry::withoutGlobalScopes()->where('title', 'Goes')->firstOrFail();
        Storage::disk(Entry::DISK)->assertExists($upload->file_path);

        // Nothing is old enough yet.
        $this->assertSame(0, Sandbox::prune());

        $this->travel(25)->hours();
        $this->assertSame(2, Sandbox::prune());

        $this->assertSame(['Stays'], Entry::withoutGlobalScopes()->pluck('title')->all());
        $this->assertSame(0, Review::withoutGlobalScopes()->count());
        Storage::disk(Entry::DISK)->assertMissing($upload->file_path);
        $this->assertModelExists($this->reader);
    }

    public function test_none_of_this_applies_when_the_demo_is_switched_off(): void
    {
        config(['shop.demo' => false]);
        $entry = $this->entry();

        $this->actingAs($this->reader)->post(route('comments.store', $entry), ['body' => 'An ordinary comment']);

        $this->assertNull(Comment::query()->value('sandbox'));
        $this->app['auth']->forgetGuards();
        $this->get($entry->url())->assertSee('An ordinary comment');
    }
}
