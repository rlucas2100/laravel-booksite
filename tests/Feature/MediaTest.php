<?php

namespace Tests\Feature;

use App\Enums\EntryType;
use App\Models\Entry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Files are kept outside the public folder and handed out by MediaController. */
class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Entry::DISK);
    }

    public function test_a_book_is_downloaded_by_members_only(): void
    {
        $book = $this->entry(EntryType::Book, ['file_path' => 'files/book/abc.pdf']);
        Storage::disk(Entry::DISK)->put('files/book/abc.pdf', '%PDF-1.4 a book');

        // A guest is sent to sign in, and afterwards lands back on the book's page.
        $this->get(route('media.file', $book))->assertRedirect('/login')->assertSessionHas('url.intended', $book->url());

        $response = $this->actingAs($this->member())->get(route('media.file', $book));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString($book->slug.'.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_audio_plays_for_everyone_and_can_be_skipped_through(): void
    {
        $audio = $this->entry(EntryType::Audio, ['file_path' => 'files/audio/abc.mp3']);
        Storage::disk(Entry::DISK)->put('files/audio/abc.mp3', str_repeat('x', 5000));

        $this->get(route('media.file', $audio))->assertOk()->assertHeader('Content-Type', 'audio/mpeg');

        // A player asks for a slice of the file when you drag the slider.
        $slice = $this->get(route('media.file', $audio), ['Range' => 'bytes=1000-1999']);
        $slice->assertStatus(206);
        $this->assertSame('bytes 1000-1999/5000', $slice->headers->get('Content-Range'));
    }

    public function test_the_content_type_comes_from_our_list_not_from_the_upload(): void
    {
        // Somebody slipped an HTML page past as a "video": it must never be served as a web page.
        $video = $this->entry(EntryType::Video, ['file_path' => 'files/video/evil.html']);
        Storage::disk(Entry::DISK)->put('files/video/evil.html', '<script>alert(1)</script>');

        $this->get(route('media.file', $video))
            ->assertHeader('Content-Type', 'application/octet-stream')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_a_missing_file_or_cover_is_not_found(): void
    {
        $entry = $this->entry(EntryType::Audio, ['file_path' => 'files/audio/gone.mp3']);

        $this->get(route('media.file', $entry))->assertNotFound();
        $this->get(route('media.cover', $entry))->assertNotFound();
    }

    public function test_an_entry_without_a_picture_gets_a_drawn_cover(): void
    {
        $book = $this->entry(EntryType::Book, ['title' => 'A Very Particular Title', 'creator' => 'Some Author']);

        $this->get($book->url())->assertSee('<svg class="cover-art"', false)->assertSee('Cover of A Very Particular Title by Some Author');
    }

    public function test_every_page_sends_the_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString("script-src 'self'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("object-src 'none'", $response->headers->get('Content-Security-Policy'));
    }
}
