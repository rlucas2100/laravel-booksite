<?php

namespace Tests\Feature;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Entry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Entry::DISK);
    }

    /** What a filled-in upload form sends. */
    private function form(EntryType $type, array $overrides = []): array
    {
        return $overrides + [
            'type' => $type->value,
            'title' => 'My '.$type->label(),
            'creator' => 'A. Writer',
            'category_id' => Category::factory()->create()->id,
            'description' => 'A description that is long enough to pass.',
        ];
    }

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get('/upload')->assertRedirect('/login');
        $this->post('/upload', [])->assertRedirect('/login');
        $this->get('/my-uploads')->assertRedirect('/login');   // crashed for guests in the 2023 version
    }

    public function test_a_member_uploads_a_book_as_a_pdf_with_a_cover(): void
    {
        $member = $this->member();

        $this->actingAs($member)->post('/upload', $this->form(EntryType::Book, [
            'title' => 'Northanger Abbey',
            'file' => UploadedFile::fake()->create('northanger.pdf', 300, 'application/pdf'),
            'cover' => UploadedFile::fake()->image('cover.jpg', 600, 900),
        ]))->assertRedirect('/books/northanger-abbey');

        $entry = Entry::query()->firstOrFail();

        $this->assertSame(EntryType::Book, $entry->type);
        $this->assertSame($member->id, $entry->user_id);
        $this->assertNotNull($entry->published_at);
        Storage::disk(Entry::DISK)->assertExists([$entry->file_path, $entry->cover_path]);

        $this->get('/books/northanger-abbey')->assertOk()->assertSee('Northanger Abbey');
        $this->actingAs($member)->get('/my-uploads')->assertSee('Northanger Abbey');
    }

    public function test_each_type_only_accepts_its_own_kind_of_file(): void
    {
        $member = $this->member();
        $mp3 = fn () => UploadedFile::fake()->create('song.mp3', 500, 'audio/mpeg');
        $mp4 = fn () => UploadedFile::fake()->create('film.mp4', 900, 'video/mp4');
        $pdf = fn () => UploadedFile::fake()->create('book.pdf', 200, 'application/pdf');

        $this->actingAs($member);

        // The wrong file for the type is turned away...
        $this->post('/upload', $this->form(EntryType::Book, ['file' => $mp3()]))->assertSessionHasErrors('file');
        $this->post('/upload', $this->form(EntryType::Audio, ['file' => $pdf()]))->assertSessionHasErrors('file');
        $this->post('/upload', $this->form(EntryType::Video, ['file' => $mp3()]))->assertSessionHasErrors('file');
        $this->post('/upload', $this->form(EntryType::Video))->assertSessionHasErrors('file');
        $this->assertDatabaseCount('entries', 0);

        // ...and the right one goes on the shelf.
        $this->post('/upload', $this->form(EntryType::Audio, ['file' => $mp3(), 'duration' => 185]))->assertSessionHasNoErrors();
        $this->post('/upload', $this->form(EntryType::Video, ['file' => $mp4(), 'duration' => 42]))->assertSessionHasNoErrors();

        $this->assertSame(185, Entry::query()->where('type', 'audio')->value('duration'));
        $this->assertSame(42, Entry::query()->where('type', 'video')->value('duration'));
    }

    public function test_an_article_needs_its_text_and_no_file(): void
    {
        $this->actingAs($this->member());

        $this->post('/upload', $this->form(EntryType::Article))->assertSessionHasErrors('body');

        $this->post('/upload', $this->form(EntryType::Article, ['title' => 'On Reading', 'body' => '## Start here']))
            ->assertRedirect('/blog/on-reading');

        $this->assertNull(Entry::query()->firstOrFail()->file_path);
    }

    public function test_a_file_that_is_too_big_or_a_cover_that_is_not_a_picture_is_refused(): void
    {
        config(['shop.max_kb.book' => 100]);
        $this->actingAs($this->member());

        $this->post('/upload', $this->form(EntryType::Book, ['file' => UploadedFile::fake()->create('big.pdf', 101, 'application/pdf')]))
            ->assertSessionHasErrors('file');

        $this->post('/upload', $this->form(EntryType::Book, [
            'file' => UploadedFile::fake()->create('ok.pdf', 50, 'application/pdf'),
            'cover' => UploadedFile::fake()->create('cover.svg', 10, 'image/svg+xml'),
        ]))->assertSessionHasErrors('cover');
    }

    public function test_two_entries_with_the_same_title_get_different_addresses(): void
    {
        $this->actingAs($this->member());

        foreach ([1, 2, 3] as $n) {
            $this->post('/upload', $this->form(EntryType::Article, ['title' => 'Same Title', 'body' => 'Text '.$n]))->assertSessionHasNoErrors();
        }

        $this->assertSame(['same-title', 'same-title-2', 'same-title-3'], Entry::query()->orderBy('id')->pluck('slug')->all());
    }

    public function test_editing_keeps_the_file_and_the_uploader(): void
    {
        $uploader = $this->member();
        $admin = $this->admin();
        $entry = $this->entry(EntryType::Book, ['user_id' => $uploader->id, 'file_path' => 'files/book/original.pdf', 'file_size' => 10]);
        Storage::disk(Entry::DISK)->put('files/book/original.pdf', '%PDF');

        // An admin edits someone else's book without choosing a new file. (In 2023 this took the book over and demanded both files again.)
        $this->actingAs($admin)->put('/entries/'.$entry->slug, [
            'title' => 'A Better Title',
            'creator' => $entry->creator,
            'category_id' => $entry->category_id,
            'description' => 'An improved description for the book.',
        ])->assertRedirect('/books/a-better-title');

        $entry->refresh();

        $this->assertSame('A Better Title', $entry->title);
        $this->assertSame($uploader->id, $entry->user_id);
        $this->assertSame('files/book/original.pdf', $entry->file_path);
        $this->assertSame(EntryType::Book, $entry->type);
    }

    public function test_only_the_uploader_or_an_admin_can_edit_and_delete(): void
    {
        $uploader = $this->member();
        $stranger = $this->member();
        $entry = $this->entry(EntryType::Book, ['user_id' => $uploader->id, 'file_path' => 'files/book/mine.pdf']);
        Storage::disk(Entry::DISK)->put('files/book/mine.pdf', '%PDF');

        $this->actingAs($stranger)->get('/entries/'.$entry->slug.'/edit')->assertForbidden();
        $this->actingAs($stranger)->put('/entries/'.$entry->slug, [])->assertForbidden();
        $this->actingAs($stranger)->delete('/entries/'.$entry->slug)->assertForbidden();
        $this->assertModelExists($entry);

        $this->actingAs($uploader)->get('/entries/'.$entry->slug.'/edit')->assertOk();
        $this->actingAs($uploader)->delete('/entries/'.$entry->slug)->assertRedirect('/my-uploads');

        $this->assertModelMissing($entry);
        Storage::disk(Entry::DISK)->assertMissing('files/book/mine.pdf');   // the file goes with the entry
    }
}
