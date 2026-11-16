<?php

namespace Tests\Feature;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_opens_even_when_the_shop_is_empty(): void
    {
        $this->get('/')->assertOk()->assertSee('put down');
    }

    public function test_every_type_lives_in_the_same_table_and_has_its_own_shelf(): void
    {
        $book = $this->entry(EntryType::Book, ['title' => 'A Book Title']);
        $video = $this->entry(EntryType::Video, ['title' => 'A Video Title']);
        $audio = $this->entry(EntryType::Audio, ['title' => 'An Audio Title']);
        $article = $this->entry(EntryType::Article, ['title' => 'An Article Title']);

        $this->assertDatabaseCount('entries', 4);

        $this->get('/books')->assertOk()->assertSee('A Book Title')->assertDontSee('A Video Title');
        $this->get('/videos')->assertOk()->assertSee('A Video Title')->assertDontSee('A Book Title');
        $this->get('/audio')->assertOk()->assertSee('An Audio Title')->assertDontSee('An Article Title');
        $this->get('/blog')->assertOk()->assertSee('An Article Title')->assertDontSee('An Audio Title');

        foreach ([$book, $video, $audio, $article] as $entry) {
            $this->get($entry->url())->assertOk()->assertSee($entry->title);
        }
    }

    public function test_an_entry_asked_for_on_the_wrong_shelf_is_sent_to_the_right_address(): void
    {
        $book = $this->entry(EntryType::Book);

        $this->get('/videos/'.$book->slug)->assertRedirect($book->url())->assertStatus(301);
    }

    public function test_search_looks_at_title_creator_description_and_category(): void
    {
        $mystery = Category::factory()->create(['name' => 'Mystery']);
        $this->entry(EntryType::Book, ['title' => 'The Moonstone', 'creator' => 'Wilkie Collins', 'category_id' => $mystery->id]);
        $this->entry(EntryType::Book, ['title' => 'Emma', 'creator' => 'Jane Austen', 'description' => 'A matchmaker in Highbury.']);

        $this->get('/browse?q=moonstone')->assertSee('The Moonstone')->assertDontSee('Emma');
        $this->get('/browse?q=austen')->assertSee('Emma')->assertDontSee('The Moonstone');
        $this->get('/browse?q=highbury')->assertSee('Emma');
        $this->get('/browse?q=mystery')->assertSee('The Moonstone')->assertDontSee('Emma');
        $this->get('/browse?q=zzzz')->assertSee('Nothing on this shelf');
    }

    public function test_a_search_on_one_shelf_never_returns_another_type(): void
    {
        $this->entry(EntryType::Book, ['title' => 'Dracula the book']);
        $this->entry(EntryType::Video, ['title' => 'Dracula the trailer']);

        // The 2023 version built this with a loose "orWhere", which let other rows leak in.
        $this->get('/videos?q=dracula')->assertSee('Dracula the trailer')->assertDontSee('Dracula the book');
    }

    public function test_top_rated_sorts_by_the_average_number_of_stars(): void
    {
        $good = $this->entry(EntryType::Book, ['title' => 'Five Star Book']);
        $poor = $this->entry(EntryType::Book, ['title' => 'One Star Book']);
        $unrated = $this->entry(EntryType::Book, ['title' => 'Unrated Book']);

        Review::factory()->count(2)->create(['entry_id' => $good->id, 'rating' => 5]);
        Review::factory()->count(2)->create(['entry_id' => $poor->id, 'rating' => 1]);

        $this->get('/books?sort=top')->assertSeeInOrder(['Five Star Book', 'One Star Book', 'Unrated Book']);
    }

    public function test_a_category_page_is_open_to_guests(): void
    {
        $category = Category::factory()->create(['name' => 'Adventure']);
        $this->entry(EntryType::Book, ['title' => 'Treasure Island', 'category_id' => $category->id]);
        $this->entry(EntryType::Book, ['title' => 'Somewhere Else']);

        $this->get(route('categories.show', $category))->assertOk()->assertSee('Treasure Island')->assertDontSee('Somewhere Else');
    }

    public function test_an_unpublished_entry_and_an_unknown_address_are_not_found(): void
    {
        $draft = $this->entry(EntryType::Book, ['published_at' => null]);

        $this->get($draft->url())->assertNotFound();
        $this->get('/books/no-such-book')->assertNotFound();
        $this->get('/no-such-page')->assertNotFound()->assertSee('cliffhanger');
    }

    public function test_an_articles_markdown_is_shown_as_html_with_scripts_removed(): void
    {
        $article = $this->entry(EntryType::Article, ['body' => "## Hello there\n\n<script>alert('x')</script>\n\n[a link](javascript:alert(1)) and **bold**"]);

        $this->get($article->url())
            ->assertSee('<h2>Hello there</h2>', false)
            ->assertSee('<strong>bold</strong>', false)
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('javascript:alert', false);
    }
}
