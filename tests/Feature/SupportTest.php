<?php

namespace Tests\Feature;

use App\Enums\EntryType;
use App\Models\Entry;
use App\Support\CoverArt;
use App\Support\SamplerPdf;
use Tests\TestCase;

/** The helpers that draw covers, write sampler PDFs and describe each type. */
class SupportTest extends TestCase
{
    public function test_every_type_has_a_shelf_address_that_leads_back_to_it(): void
    {
        foreach (EntryType::cases() as $type) {
            $this->assertSame($type, EntryType::fromSegment($type->segment()));
            $this->assertSame($type->hasFile(), $type->fileTypes() !== []);
        }

        $this->assertNull(EntryType::fromSegment('comics'));
        $this->assertSame(['books', 'videos', 'audio', 'blog'], EntryType::segments());
    }

    public function test_the_same_entry_always_gets_the_same_drawn_cover(): void
    {
        $entry = new Entry(['title' => 'The Hound of the Baskervilles', 'creator' => 'Arthur Conan Doyle']);
        $entry->type = EntryType::Book;
        $entry->slug = 'the-hound-of-the-baskervilles';

        $this->assertSame(CoverArt::design($entry), CoverArt::design($entry));

        $entry->cover_design = 'waves:cobalt';
        $this->assertSame(['waves', 'cobalt'], CoverArt::design($entry));

        $entry->cover_design = 'nonsense:colours';   // anything unknown falls back to the worked-out look
        $this->assertContains(CoverArt::design($entry)[0], CoverArt::MOTIFS);
    }

    public function test_a_drawn_cover_fits_any_title_and_escapes_it(): void
    {
        foreach (EntryType::cases() as $type) {
            foreach (['Emma', 'A <b>bold</b> & "quoted" title', str_repeat('Supercalifragilistic ', 12)] as $title) {
                foreach (CoverArt::MOTIFS as $motif) {
                    $entry = new Entry(['title' => $title, 'creator' => 'Somebody']);
                    $entry->type = $type;
                    $entry->slug = 'x';
                    $entry->cover_design = $motif.':tomato';

                    $svg = (string) CoverArt::render($entry);

                    $this->assertStringStartsWith('<svg', $svg);
                    $this->assertStringNotContainsString('<b>', $svg);
                    $this->assertNotFalse(simplexml_load_string($svg), "The $motif cover for \"$title\" is not valid SVG.");
                }
            }
        }
    }

    public function test_the_sampler_is_a_real_pdf(): void
    {
        $pdf = SamplerPdf::make('Cliffhanger', 'Brontë’s “Jane Eyre”', 'Charlotte Brontë', "There was no possibility of taking a walk that day.\nSecond paragraph (with brackets).", 'https://www.gutenberg.org/ebooks/1260');

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringEndsWith("%%EOF\n", $pdf);
        $this->assertStringContainsString('\\(with brackets\\)', $pdf);

        // The cross-reference table must point at the start of each object.
        preg_match('/startxref\n(\d+)/', $pdf, $match);
        $this->assertSame('xref', substr($pdf, (int) $match[1], 4));
        preg_match_all('/^(\d{10}) 00000 n /m', $pdf, $offsets);
        foreach ($offsets[1] as $i => $offset) {
            $this->assertStringStartsWith(($i + 1).' 0 obj', substr($pdf, (int) $offset, 12));
        }
    }
}
