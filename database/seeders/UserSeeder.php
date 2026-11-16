<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Sandbox;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // One hash of a password nobody knows, shared by every account that is never signed in to.
        $locked = Hash::make(Str::random(40));

        // The staff account that owns everything the seeder puts on the shelves.
        $this->account('The Cliffhanger desk', 'desk@example.com', $locked, protected: true);

        // The sample readers behind the reviews and comments.
        foreach ((require __DIR__.'/data/community.php')['readers'] as $key => $name) {
            $this->account($name, $key.'@example.com', $locked);
        }

        // Two shared accounts for visitors to try. Never seeded on a real (non-demo) site.
        if (Sandbox::enabled() || app()->environment('local', 'testing')) {
            foreach (config('shop.demo_accounts') as $demo) {
                $user = $this->account($demo['name'], $demo['email'], Hash::make($demo['password']), protected: true);

                if ($demo['role']) {
                    $user->assignRole($demo['role']);
                }
            }
        }

        // The owner, from the .env file.
        $owner = config('shop.owner');

        if ($owner['email'] && ($owner['password_hash'] || $owner['password'])) {
            $this->account($owner['name'], $owner['email'], $owner['password_hash'] ?: Hash::make($owner['password']))
                ->assignRole(User::OWNER_ROLE);
        }
    }

    private function account(string $name, string $email, string $passwordHash, bool $protected = false): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'password' => $passwordHash,
            'protected' => $protected,
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }
}
