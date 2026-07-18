<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Admin panel roles per PRD §5.2: Admin (full access), Astrologer (own
     * bookings/availability/courses only), Editor (blog/content only).
     */
    public function run(): void
    {
        foreach (['Admin', 'Astrologer', 'Editor'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
