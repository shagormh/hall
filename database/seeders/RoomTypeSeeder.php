<?php

namespace Database\Seeders;

use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('room_types')->truncate();

        $roomTypes = [
            // Hall 1
            
            [
                'id' => 1,
                'hall_id' => 1,
                'name' => 'Adibasi',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'hall_id' => 1,
                'name' => 'Press Club',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'hall_id' => 1,
                'name' => 'Shangbadik Shomiti',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Hall 2
            [
                'id' => 4,
                'hall_id' => 2,
                'name' => 'Adibasi',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'hall_id' => 2,
                'name' => 'Press Club',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'hall_id' => 2,
                'name' => 'Shangbadik Shomiti',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Hall 3
            [
                'id' => 7,
                'hall_id' => 3,
                'name' => 'Adibasi',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'hall_id' => 3,
                'name' => 'Press Club',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 9,
                'hall_id' => 3,
                'name' => 'Shangbadik Shomiti',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Hall 4
            [
                'id' => 10,
                'hall_id' => 4,
                'name' => 'Adibasi',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 11,
                'hall_id' => 4,
                'name' => 'Press Club',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 12,
                'hall_id' => 4,
                'name' => 'Shangbadik Shomiti',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        RoomType::insert($roomTypes);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
