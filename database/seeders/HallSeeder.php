<?php

namespace Database\Seeders;

use App\Models\Hall;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('halls')->truncate();

        $halls = [
            [
                'id' => 1,
                'name' => 'Agnibeena Hall',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null
            ],
            [
                'id' => 2,
                'name' => 'DolonCapa Hall',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null
            ],
            [
                'id' => 3,
                'name' => 'Bidrohi Hall',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null
            ],
            [
                'id' => 4,
                'name' => 'Shiulimala Hall',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null
            ],
        ];

        Hall::insert($halls);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
