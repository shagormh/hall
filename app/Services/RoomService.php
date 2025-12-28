<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoomService extends BaseModelService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function model(): string
    {
        return Room::class;
    }

    public function getRooms()
    {
        return $this->model()::where(function($query) {
                // Include rooms with NULL room_type OR active room types from SAME hall
                $query->whereNull('room_type_id')
                      ->orWhereHas('roomType', function($q) {
                          $q->where('is_active', true)
                            ->whereColumn('room_types.hall_id', 'rooms.hall_id');
                      });
            })
            ->with(['seats', 'hall', 'roomType'])
            ->get();
    }
    public function getRoomByProvost()
    {
        $user = Auth::user();
        $hallIds = $user->halls;

        return $this->model()::whereIn('hall_id', $hallIds)
            ->where(function($query) {
                // Include rooms with NULL room_type OR active room types from SAME hall
                $query->whereNull('room_type_id')
                      ->orWhereHas('roomType', function($q) {
                          $q->where('is_active', true)
                            ->whereColumn('room_types.hall_id', 'rooms.hall_id');
                      });
            })
            ->with(['seats', 'hall', 'roomType'])
            ->get();
    }

    public function createRoom(array $data): Room
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $hallIds = $user->halls;

            if(!empty($hallIds)){
                $data['hall_id'] = $hallIds[0];
            }

            $room = $this->create($data);

            // Auto generate seats
            $capacity = $data['capacity'] ?? 4;
            $seatLabels = range('A', chr(64 + $capacity)); // A, B, C...

            foreach ($seatLabels as $label) {
                Seat::create([
                    'room_id' => $room->id,
                    'seat_label' => $label,
                    'seat_code' => $room->room_number .'-'.$label,
                    'status' => 'empty',
                ]);
            }

            return $room;
        });
    }


    public function updateRoom($room, array $data)
    {
        return DB::transaction(function () use ($room, $data) {
            $room->update($data);
            $room->seats()->each(function ($seat) use ($room) {
                $seat->update([
                    'seat_code' => $room->room_number . '-' . $seat->seat_label
                ]);
            });

            return $room->fresh('seats');
        });
    }

    public function deleteRoom($room)
    {
        return DB::transaction(function () use ($room) {
            $room->delete();
            return true;
        });
    }
}
