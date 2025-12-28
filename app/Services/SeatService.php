<?php

namespace App\Services;

use App\Models\Seat;
use Illuminate\Support\Facades\Auth;

class SeatService extends BaseModelService
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
        return Seat::class;
    }

    public function getEmptySeats()
    {
        return $this->model()::where('status', 'empty')->get();
    }
    public function getEmptySeatByHallProvost()
    {
        $user = Auth::user();

        // Decode halls (cast in model if possible)
        $hallIds = [];
        if ($user && is_array($user->halls)) {
            $hallIds = $user->halls;
        } elseif ($user && is_string($user->halls) && $user->halls !== '') {
            $decoded = json_decode($user->halls, true);
            $hallIds = is_array($decoded) ? $decoded : [];
        }

        return $this->model()::where('status', 'empty')
            ->whereHas('room', function ($q) use ($hallIds) {
                $q->whereIn('hall_id', $hallIds)
                  ->where(function($q2) {
                      // Allow rooms with NULL room_type OR active room types from SAME hall
                      $q2->whereNull('room_type_id')
                         ->orWhereHas('roomType', function($q3) {
                             $q3->where('is_active', true)
                                ->whereColumn('room_types.hall_id', 'rooms.hall_id');
                         });
                  });
            })
            ->with('room')
            ->get();
    }

    public function getSeatsForEdit($currentSeatId = null)
    {
        $user = Auth::user();
        $hallIds = [];

        // Decode halls
        if (is_array($user->halls)) {
            $hallIds = $user->halls;
        } elseif (is_string($user->halls) && $user->halls !== '') {
            $decoded = json_decode($user->halls, true);
            $hallIds = is_array($decoded) ? $decoded : [];
        }

        $query = $this->model()::where(function($q) use ($hallIds) {
        $q->where('status', 'empty')
          ->whereHas('room', function($q2) use ($hallIds) {
              $q2->whereIn('hall_id', $hallIds)
                 ->where(function($q3) {
                     // Allow rooms with NULL room_type OR active room types from SAME hall
                     $q3->whereNull('room_type_id')
                        ->orWhereHas('roomType', function($q4) {
                            $q4->where('is_active', true)
                               ->whereColumn('room_types.hall_id', 'rooms.hall_id');
                        });
                 });
          });
    });

        // Include current seat even if it's occupied
        if ($currentSeatId) {
            $query->orWhere('id', $currentSeatId);
        }

        return $query->get();
    }



}
