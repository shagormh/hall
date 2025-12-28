<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRoomTypeHallMismatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:fix-type-mismatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix room type hall_id mismatches by assigning correct room types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for room type hall mismatches...');

        // Find all rooms with mismatched hall_id
        $mismatches = DB::table('rooms as r')
            ->join('room_types as rt', 'r.room_type_id', '=', 'rt.id')
            ->where('r.hall_id', '!=', 'rt.hall_id')
            ->whereNotNull('r.room_type_id')
            ->select('r.id as room_id', 'r.room_number', 'r.hall_id as room_hall_id', 'rt.name as type_name', 'rt.hall_id as type_hall_id')
            ->get();

        if ($mismatches->isEmpty()) {
            $this->info('✅ No mismatches found! All rooms have correct room types.');
            return 0;
        }

        $this->warn("Found {$mismatches->count()} room(s) with mismatched hall_id:");
        
        $fixedCount = 0;
        $skippedCount = 0;

        foreach ($mismatches as $mismatch) {
            $this->line("Room {$mismatch->room_number}: Type '{$mismatch->type_name}' from Hall {$mismatch->type_hall_id} but room is in Hall {$mismatch->room_hall_id}");

            // Find correct room type for this room's hall
            $correctType = RoomType::where('name', $mismatch->type_name)
                ->where('hall_id', $mismatch->room_hall_id)
                ->first();

            if ($correctType) {
                Room::where('id', $mismatch->room_id)
                    ->update(['room_type_id' => $correctType->id]);
                
                $this->info("  ✅ Fixed: Assigned to '{$mismatch->type_name}' (Hall {$mismatch->room_hall_id})");
                $fixedCount++;
            } else {
                $this->error("  ❌ Skipped: No '{$mismatch->type_name}' room type found for Hall {$mismatch->room_hall_id}");
                $this->warn("  💡 Suggestion: Create '{$mismatch->type_name}' room type for Hall {$mismatch->room_hall_id} first");
                $skippedCount++;
            }
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("  ✅ Fixed: {$fixedCount}");
        if ($skippedCount > 0) {
            $this->warn("  ⚠️  Skipped: {$skippedCount}");
        }
        
        $this->info('🎉 Room type hall mismatch fix complete!');
        return 0;
    }
}
