<?php

namespace Database\Seeders;

use App\Models\Hall;
use App\Models\User;
use App\Constants\Constants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();

        // 1. Super Admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345'),
            'halls' => Hall::pluck('id')->toArray() // সব হলের access
        ]);
        $admin->assignRole(Constants::ROLE_SUPER_ADMIN);

        // 2. Hall Provosts
        $provosts = [
            ['name' => 'Bidrohi Provost', 'email' => 'bidrohi@example.com', 'hall_name' => 'Bidrohi Hall'],
            ['name' => 'Agnibeena Provost', 'email' => 'agnibeena@example.com', 'hall_name' => 'Agnibeena Hall'],
            ['name' => 'Shiulimala Provost', 'email' => 'shiulimala@example.com', 'hall_name' => 'Shiulimala Hall'],
            ['name' => 'Dolonchapa Provost', 'email' => 'dolonchapa@example.com', 'hall_name' => 'Dolonchapa Hall'],
        ];

        foreach ($provosts as $provost) {
            $hallId = Hall::where('name', $provost['hall_name'])->pluck('id')->first();
            $user = User::factory()->create([
                'name' => $provost['name'],
                'email' => $provost['email'],
                'password' => Hash::make('12345'),
                'halls' => [$hallId]
            ]);
            $user->assignRole(Constants::ROLE_HALL_PROVOST);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
