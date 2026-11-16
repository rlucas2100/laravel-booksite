<?php

namespace Tests;

use App\Enums\EntryType;
use App\Models\Entry;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();                 // pages render without needing "npm run build"
        config(['shop.demo' => false]);       // each test switches the public-demo rules on when it wants them
    }

    protected function member(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function admin(): User
    {
        $this->seed(RoleSeeder::class);

        return tap(User::factory()->create(), fn (User $user) => $user->assignRole(User::ADMIN_ROLE));
    }

    protected function owner(): User
    {
        $this->seed(RoleSeeder::class);

        return tap(User::factory()->create(), fn (User $user) => $user->assignRole(User::OWNER_ROLE));
    }

    protected function entry(EntryType $type = EntryType::Book, array $attributes = []): Entry
    {
        return Entry::factory()->type($type)->create($attributes);
    }
}
