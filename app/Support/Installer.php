<?php

namespace App\Support;

use App\Models\Entry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

/**
 * Sets the site up without a command line (shared hosting): creates the SQLite
 * file, runs the migrations and the seeders, then leaves a marker holding the
 * site's version. When a newer version is uploaded (config shop.version) the
 * demo rebuilds itself: its content is only sample content.
 */
class Installer
{
    public static function marker(): string
    {
        return storage_path('app/installed');
    }

    /** Installed, and by this version of the site? */
    public static function installed(): bool
    {
        return is_file(static::marker()) && trim((string) file_get_contents(static::marker())) === (string) config('shop.version');
    }

    public static function run(): void
    {
        $lock = fopen(storage_path('app/install.lock'), 'c');

        // A second visitor arriving mid-install waits here until the first has finished.
        if (! $lock || ! flock($lock, LOCK_EX)) {
            return;
        }

        try {
            if (static::installed()) {
                return;
            }

            @set_time_limit(300);

            $database = config('database.connections.sqlite.database');

            if (config('database.default') === 'sqlite' && $database !== ':memory:' && ! is_file($database)) {
                @mkdir(dirname($database), 0775, true);
                touch($database);
            }

            // A fresh start: the old sample files go, then the tables are rebuilt and filled.
            Storage::disk(Entry::DISK)->deleteDirectory('seed');
            Storage::disk(Entry::DISK)->deleteDirectory('files');
            Storage::disk(Entry::DISK)->deleteDirectory('covers');

            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);

            file_put_contents(static::marker(), (string) config('shop.version'));
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
