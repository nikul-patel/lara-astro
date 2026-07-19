<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = User::query()->firstOrNew(['email' => 'admin@example.com']);

        if (! $admin->exists) {
            $admin->fill(User::factory()->raw([
                'name' => 'Admin',
                'email' => 'admin@example.com',
            ]));
            $admin->save();
        }

        $admin->assignRole('Admin');

        // Fictional "Jyotish Path" demo dataset (issue #19 / PRD §1).
        $this->call(DemoContentSeeder::class);
    }
}
