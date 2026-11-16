<?php

namespace App\Enums;

/**
 * Every kind of thing the shop can hold. They all live in ONE table ("entries")
 * and this enum is the "type" column. Everything that differs between a book,
 * a video, an audio episode and an article is answered here, so adding a new
 * kind later means adding one case, not a new table, model and controller.
 */
enum EntryType: string
{
    case Book = 'book';
    case Video = 'video';
    case Audio = 'audio';
    case Article = 'article';

    /** "Book", "Video"... */
    public function label(): string
    {
        return match ($this) {
            self::Book => 'Book',
            self::Video => 'Video',
            self::Audio => 'Audio',
            self::Article => 'Article',
        };
    }

    /** The name of the shelf in the menu. */
    public function shelf(): string
    {
        return match ($this) {
            self::Book => 'Books',
            self::Video => 'Video',
            self::Audio => 'Audio',
            self::Article => 'Blog',
        };
    }

    /** The first part of the address: /books, /videos, /audio, /blog. */
    public function segment(): string
    {
        return match ($this) {
            self::Book => 'books',
            self::Video => 'videos',
            self::Audio => 'audio',
            self::Article => 'blog',
        };
    }

    public static function fromSegment(string $segment): ?self
    {
        foreach (self::cases() as $type) {
            if ($type->segment() === $segment) {
                return $type;
            }
        }

        return null;
    }

    /** @return list<string> */
    public static function segments(): array
    {
        return array_map(fn (self $type) => $type->segment(), self::cases());
    }

    public function intro(): string
    {
        return match ($this) {
            self::Book => 'Classics from the public domain, free to download and keep.',
            self::Video => 'Trailers and short films about books worth your evening.',
            self::Audio => 'Poems, stories and podcasts read aloud. Press play.',
            self::Article => 'Notes from the desk: reading lists, how-tos and opinions.',
        };
    }

    /** What you do with one: the word on the main button. */
    public function verb(): string
    {
        return match ($this) {
            self::Book, self::Article => 'Read',
            self::Video => 'Watch',
            self::Audio => 'Listen',
        };
    }

    /** What the "creator" column means for this type: the label on the upload form. */
    public function creatorLabel(): string
    {
        return match ($this) {
            self::Book => 'Author',
            self::Video => 'Made by',
            self::Audio => 'Author or host',
            self::Article => 'Written by',
        };
    }

    /** The word before the creator's name on an entry's page: "by Bram Stoker". */
    public function byline(): string
    {
        return $this === self::Video ? 'made by' : 'by';
    }

    /** The colour that stands for this type across the site (a CSS token). */
    public function colour(): string
    {
        return match ($this) {
            self::Book => 'tomato',
            self::Video => 'cobalt',
            self::Audio => 'mint',
            self::Article => 'grape',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Book => 'book',
            self::Video => 'play',
            self::Audio => 'headphones',
            self::Article => 'pen',
        };
    }

    /** Articles are written in the form; the others come with a file. */
    public function hasFile(): bool
    {
        return $this !== self::Article;
    }

    /**
     * File extensions accepted for this type, each with the Content-Type we
     * send when the file is played or downloaded.
     *
     * @return array<string, string>
     */
    public function fileTypes(): array
    {
        return match ($this) {
            self::Book => ['pdf' => 'application/pdf'],
            self::Video => ['mp4' => 'video/mp4', 'webm' => 'video/webm'],
            self::Audio => ['mp3' => 'audio/mpeg', 'm4a' => 'audio/mp4', 'ogg' => 'audio/ogg', 'wav' => 'audio/wav'],
            self::Article => [],
        };
    }

    /** For the file input's "accept" attribute. */
    public function accept(): string
    {
        return implode(',', array_map(fn (string $ext) => '.'.$ext, array_keys($this->fileTypes())));
    }

    public function maxKb(): int
    {
        return $this->hasFile() ? (int) config('shop.max_kb.'.$this->value) : 0;
    }

    /** Cover shape: tall for books, square for audio, wide for the rest. */
    public function coverShape(): string
    {
        return match ($this) {
            self::Book => 'tall',
            self::Audio => 'square',
            self::Video, self::Article => 'wide',
        };
    }
}
