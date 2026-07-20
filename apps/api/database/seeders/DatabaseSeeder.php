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
        $configuredPassword = config('app.admin_password');
        $admin = User::where('email', $email)->first();

        if ($admin) {
            // Re-running `db:seed` against an already-seeded database must not
            // silently rotate the admin's password out from under them. Only
            // touch it when ADMIN_PASSWORD is explicitly set to a new value.
            if ($configuredPassword) {
                $admin->update(['password' => Hash::make($configuredPassword)]);
            }
        } else {
            $generatedPassword = $configuredPassword ?: Str::password(16);
            $admin = User::create([
                'name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($generatedPassword),
            ]);

            if (! $configuredPassword) {
                $this->command?->warn("Admin user created: {$email} / {$generatedPassword}");
                $this->command?->warn('Set ADMIN_PASSWORD in .env to pin this instead of generating a new one on every fresh seed.');
            }
        }

        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }
    }
}
