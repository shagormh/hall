<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        DB::table('seats')->truncate();
        DB::table('rooms')->truncate();

        $rooms = [];
        $seats = [];

        // Floors 2 to 10 → 201–236, 301–336, ..., 1001–1036
        for ($floor = 2; $floor <= 10; $floor++) {
            $start = $floor * 100 + 1;   // 201, 301, ...
            $end   = $floor * 100 + 36;  // 236, 336, ...

            for ($roomNumber = $start; $roomNumber <= $end; $roomNumber++) {

                // Prepare room data
                $rooms[] = [
                    'hall_id'      => 3,
                    'room_type_id' => null,
                    'room_number'  => $roomNumber,
                    'capacity'     => 4,
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ];
            }
        }

        // Insert all rooms first
        DB::table('rooms')->insert($rooms);

        // Fetch room IDs after insert
        $insertedRooms = DB::table('rooms')->select('id','room_number')->get();

        // Prepare seats for each room
        foreach ($insertedRooms as $room) {
            foreach (['A', 'B', 'C', 'D'] as $label) {
                $seats[] = [
                    'room_id'    => $room->id,
                    'seat_label' => $label,
                    'seat_code'  => $room->room_number . '-' . $label,
                    'status'     => 'empty',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // Insert all seats
        DB::table('seats')->insert($seats);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
