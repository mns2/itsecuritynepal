<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([ContentImportSeeder::class, CareerPathwaysSeeder::class]);

        // Admin account from environment variables only (never hard-coded). See .env.example.
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        if ($email && $password) {
            User::updateOrCreate(['email' => $email], [
                'name' => env('ADMIN_NAME', 'Site administrator'),
                'password' => Hash::make($password),
            ]);
            $this->command?->info("Admin user ready: {$email}");
        } else {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set: no admin user created. Use `php artisan make:filament-user`.');
        }
    }
}
