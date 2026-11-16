<?php

namespace Tests\Feature;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountsAndAdminTest extends TestCase
{
    use RefreshDatabase;

    /* ------------------------------------------------------------ joining and signing in */

    public function test_someone_can_join_sign_out_and_sign_in_again(): void
    {
        $this->post('/register', [
            'name' => 'Nora Reader',
            'email' => 'nora@example.com',
            'password' => 'a-long-password',
            'password_confirmation' => 'a-long-password',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $user = User::query()->where('email', 'nora@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('a-long-password', $user->password));   // stored hashed, never as typed
        $this->assertFalse($user->isAdmin());

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', ['email' => 'nora@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'nora@example.com', 'password' => 'a-long-password'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_two_people_can_share_a_name_but_not_an_email(): void
    {
        $this->member(['name' => 'John Smith', 'email' => 'john@example.com']);

        $form = ['name' => 'John Smith', 'password' => 'a-long-password', 'password_confirmation' => 'a-long-password'];

        $this->post('/register', $form + ['email' => 'john@example.com'])->assertSessionHasErrors('email');
        $this->post('/register', $form + ['email' => 'other.john@example.com'])->assertSessionHasNoErrors();   // the 2023 table refused this
    }

    public function test_signing_in_is_slowed_down_after_five_wrong_passwords(): void
    {
        $this->member(['email' => 'target@example.com']);

        foreach (range(1, 5) as $try) {
            $this->post('/login', ['email' => 'target@example.com', 'password' => 'guess-'.$try]);
        }

        $this->post('/login', ['email' => 'target@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_a_member_changes_their_details_and_password_and_can_delete_their_account(): void
    {
        $member = $this->member(['password' => 'old-password-1']);
        $entry = $this->entry(attributes: ['user_id' => $member->id]);

        $this->actingAs($member)->patch('/profile', ['name' => 'New Name', 'email' => 'new@example.com'])->assertSessionHasNoErrors();
        $this->assertSame('New Name', $member->fresh()->name);

        $this->put('/profile/password', ['current_password' => 'wrong', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasErrorsIn('password', 'current_password');
        $this->put('/profile/password', ['current_password' => 'old-password-1', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-password-1', $member->fresh()->password));

        $this->delete('/profile', ['password' => 'new-password-1'])->assertRedirect('/');
        $this->assertGuest();
        $this->assertModelMissing($member);
        $this->assertModelMissing($entry);   // their uploads go with them
    }

    public function test_a_protected_demo_account_cannot_be_changed(): void
    {
        $demo = $this->member(['password' => 'demo-password']);
        $demo->forceFill(['protected' => true])->save();

        $this->actingAs($demo)->patch('/profile', ['name' => 'Hijacked', 'email' => 'mine@example.com'])->assertForbidden();
        $this->put('/profile/password', ['current_password' => 'demo-password', 'password' => 'changed-it-1', 'password_confirmation' => 'changed-it-1'])->assertForbidden();
        $this->delete('/profile', ['password' => 'demo-password'])->assertForbidden();

        $this->assertTrue(Hash::check('demo-password', $demo->fresh()->password));
    }

    /* ------------------------------------------------------------ the admin area */

    public function test_the_admin_area_is_for_admins(): void
    {
        $pages = ['/admin', '/admin/catalogue', '/admin/community', '/admin/users', '/admin/categories'];

        foreach ($pages as $page) {
            $this->get($page)->assertRedirect('/login');
        }

        $member = $this->member();
        foreach ($pages as $page) {
            $this->actingAs($member)->get($page)->assertForbidden();
        }

        $this->entry();
        $admin = $this->admin();
        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }

    public function test_an_admin_manages_members_but_not_other_admins(): void
    {
        $admin = $this->admin();
        $member = $this->member(['name' => 'Old Name']);
        $otherAdmin = $this->admin();
        $owner = $this->owner();

        $this->actingAs($admin)->put(route('admin.users.update', $member), ['name' => 'Fixed Name', 'email' => $member->email])->assertRedirect(route('admin.users.index'));
        $this->assertSame('Fixed Name', $member->fresh()->name);

        // Leaving the password boxes empty keeps the member's password. (In 2023 the form demanded a new one.)
        $this->assertSame($member->password, $member->fresh()->password);

        $this->put(route('admin.users.update', $otherAdmin), ['name' => 'Nope', 'email' => $otherAdmin->email])->assertForbidden();
        $this->delete(route('admin.users.destroy', $owner))->assertForbidden();
        $this->delete(route('admin.users.destroy', $admin))->assertForbidden();   // not yourself either

        $this->delete(route('admin.users.destroy', $member))->assertRedirect(route('admin.users.index'));
        $this->assertModelMissing($member);
    }

    public function test_only_the_owner_hands_out_the_admin_role(): void
    {
        $admin = $this->admin();
        $owner = $this->owner();
        $member = $this->member();
        $form = ['name' => $member->name, 'email' => $member->email, 'role' => 'admin'];

        $this->actingAs($admin)->put(route('admin.users.update', $member), $form);
        $this->assertFalse($member->fresh()->isAdmin());

        $this->actingAs($owner)->put(route('admin.users.update', $member), $form);
        $this->assertTrue($member->fresh()->isAdmin());

        $this->actingAs($owner)->put(route('admin.users.update', $member), ['role' => 'member'] + $form);
        $this->assertFalse($member->fresh()->isAdmin());
    }

    public function test_an_admin_adds_renames_and_deletes_categories(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.categories.store'), ['name' => 'Westerns', 'blurb' => 'Dust and horses.'])->assertSessionHasNoErrors();
        $this->post(route('admin.categories.store'), ['name' => 'westerns'])->assertSessionHasErrors('name');   // already there

        $westerns = Category::query()->where('slug', 'westerns')->firstOrFail();
        $this->put(route('admin.categories.update', $westerns), ['name' => 'Wild West'])->assertSessionHasNoErrors();
        $this->assertSame('wild-west', $westerns->refresh()->slug);

        // Deleting a category with books in it moves them to another one first.
        $keep = Category::factory()->create();
        $book = $this->entry(EntryType::Book, ['category_id' => $westerns->id]);

        $this->delete(route('admin.categories.destroy', $westerns))->assertSessionHasErrors('move_to');
        $this->delete(route('admin.categories.destroy', $westerns), ['move_to' => $keep->id])->assertSessionHasNoErrors();

        $this->assertModelMissing($westerns);
        $this->assertSame($keep->id, $book->fresh()->category_id);
    }

    public function test_the_seeders_fill_a_working_shop(): void
    {
        $this->seed();

        $this->assertSame(28, Entry::query()->where('type', 'book')->count());
        $this->assertSame(5, Entry::query()->where('type', 'audio')->count());
        $this->assertSame(4, Entry::query()->where('type', 'article')->count());
        $this->assertDatabaseHas('reviews', ['rating' => 0]);   // somebody couldn't finish Meditations

        $this->get('/')->assertOk()->assertSee('Dracula');
        $this->get('/books/dracula')->assertOk()->assertSee('Mina is the real hero');
        $this->get('/blog/what-the-stars-mean')->assertOk();

        // The two demo accounts work, and the admin one opens the admin area.
        $this->post('/login', config('shop.demo_accounts.admin'))->assertRedirect('/');
        $this->get('/admin')->assertOk();
    }
}
