<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;

/**
 * Sets the site up without a command line (shared hosting): creates the SQLite
 * file, runs the migrations and the seeders, then leaves a marker so it never
 * runs again. Delete the marker and the database to start afresh.
 */
class Installer
{
    public static function marker(): string
    {
        return storage_path('app/installed');
    }

    public static function installed(): bool
    {
        return is_file(static::marker());
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

            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);

            file_put_contents(static::marker(), date('c'));
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
