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
        $this->call([
            // CountrySeeder::class,
            // LanguageSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            HallSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,

            // DivisionSeeder::class,
            // DistrictSeeder::class,
            // UpazilaSeeder::class,
            // UnionSeeder::class,
            RoomsSeeder::class,
            DepartmentSeeder::class,
            RoomTypeSeeder::class,
        ]);
    }
}
