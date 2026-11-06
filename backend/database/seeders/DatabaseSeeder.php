<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Creates the first admin from ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD in .env.
     * Add more admins later with:  php artisan admin:create
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set in .env — no admin created. Use: php artisan admin:create');
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => env('ADMIN_NAME', 'Admin'), 'password' => $password],
        );

        $this->command?->info("Admin ready: {$email}");
    }
}
