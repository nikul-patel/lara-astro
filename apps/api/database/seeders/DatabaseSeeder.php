<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $email = config('app.admin_email');
        $password = config('app.admin_password');
        $generatedPassword = null;

        if (! $password) {
            $generatedPassword = Str::password(16);
            $password = $generatedPassword;
        }

        // updateOrCreate keeps this safe to re-run (e.g. `db:seed` against an
        // already-seeded database), unlike a plain factory create().
        $admin = User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Admin', 'password' => Hash::make($password)]
        );

        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        if ($generatedPassword) {
            $this->command?->warn("Admin user created: {$email} / {$generatedPassword}");
            $this->command?->warn('Set ADMIN_PASSWORD in .env to pin this instead of generating a new one on every fresh seed.');
        }
    }
}
