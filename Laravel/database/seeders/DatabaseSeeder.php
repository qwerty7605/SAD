<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OrganizationSeeder::class,
            AcademicTermSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            OrganizationAdminSeeder::class,
            SystemAdminSeeder::class,
            StudentClearanceSeeder::class,
            ClearanceItemSeeder::class,
        ]);
    }
}
