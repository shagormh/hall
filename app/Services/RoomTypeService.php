<?php

namespace App\Services;

use App\Models\RoomType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomTypeService extends BaseModelService
{
    public function model(): string
    {
        return RoomType::class;
    }

    public function getRoomTypes()
    {
        return $this->model()::with('hall')->get();
    }
    public function getRoomTypeByProvost()
    {
        $user = Auth::user();
        $hallIds = $user->halls;

        return $this->model()::with('hall')->whereIn('hall_id', $hallIds)->get();
    }


    public function getActiveRoomTypes($isActive = true)
    {
        return $this->model()::where('is_active', $isActive)->get();
    }
    public function getRoomActiveTypeByProvost($isActive = true)
    {
        $user = Auth::user();
        $hallIds = $user->halls;

        return $this->model()::with('hall')->where('is_active', $isActive)->whereIn('hall_id', $hallIds)->get();

    }


    public function changeStatus(RoomType $roomType, $isActive)
    {
        $isActive = ( $isActive == true ) ? false : true;
        $this->update($roomType, ['is_active' => $isActive]);
        return $roomType;
    }
    public function createRoomType(array $data)
    {
        return DB::transaction(function () use ($data) {
            $roomType = $this->create($data);
            $user = Auth::user();

            $hallIds = $user->halls;
            if (!empty($hallIds)) {
                $roomType->hall_id = $hallIds[0];
            }

            $roomType->save();
            return $roomType;
        });
    }

    public function updateRoomType($roomType, array $data)
    {
        return DB::transaction(function () use ($roomType, $data) {
            $roomType->fill($data);

            $user = Auth::user();
            $hallIds = is_array($user->halls) ? $user->halls : json_decode($user->halls, true);

            if (!empty($hallIds)) {
                $roomType->hall_id = $hallIds[0];
            }

            $roomType->save();

            return $roomType;
        });
    }
    


}
