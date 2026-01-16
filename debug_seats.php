<?php

use App\Services\HallAllotmentService;
use App\Models\Seat;
use App\Models\Hall;

$service = app(HallAllotmentService::class);

echo "Checking seats for Hall ID 3...\n";

// Check if Hall exists
$hall = Hall::find(3);
if (!$hall) {
    echo "Hall 3 not found!\n";
    exit;
}
echo "Hall Name: " . $hall->name . "\n";

// Count total empty seats in this hall
$emptySeats = Seat::whereHas('room', function($q) {
    $q->where('hall_id', 3);
})->where('status', 'empty')->count();
echo "Total Empty Seats in Hall 3 (ignoring allotments): $emptySeats\n";

// Check valid empty seats that satisfy room rules (active room type or null)
$validRulesSeats = Seat::whereHas('room', function($query) {
        $query->where('hall_id', 3)
              ->where(function($q) {
                  $q->whereNull('room_type_id')
                    ->orWhereHas('roomType', function($q2) {
                        $q2->where('is_active', true)
                           ->whereColumn('room_types.hall_id', 'rooms.hall_id');
                    });
              });
    })
    ->where('status', 'empty')
    ->count();
echo "Empty Seats passing Room Rules: $validRulesSeats\n";


// Test Service method for Feb 2026
$date = '2026-02-01';
echo "Testing getAvailableSeats for date: $date\n";
$seats = $service->getAvailableSeats(3, $date);

echo "Service returned " . count($seats) . " seats.\n";

if ($seats->isEmpty() && $emptySeats > 0) {
    echo "Seats exist but service filtered them out. Checking allotments...\n";
    
    // Check overlapping allotments
    $occupiedQuery = \App\Models\HallAllotment::where('hall_id', 3)
        ->where(function($q) {
            $q->where('status', 'active')
              ->orWhere('status', 'cancelled');
        })
        ->where('starting_month', '<=', $date)
        ->where(function($query) use ($date) {
            $query->where('ending_month', '>=', $date)
                  ->orWhereNull('ending_month');
        });
        
    echo "Occupied/Blocking Allotments Count: " . $occupiedQuery->count() . "\n";
    foreach($occupiedQuery->get() as $allot) {
        echo " - Allotment ID: {$allot->id}, Seat: {$allot->seat_id}, Status: {$allot->status}, Start: {$allot->starting_month}, End: {$allot->ending_month}\n";
    }
} else {
    foreach($seats as $seat) {
        echo " - Seat: {$seat->seat_code} (ID: {$seat->id})\n";
    }
}
