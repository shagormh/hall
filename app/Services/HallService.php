<?php

namespace App\Services;

use App\Models\Hall;

class HallService extends BaseModelService
{
    public function model(): string
    {
        return Hall::class;
    }

    public function getHalls()
    {
        return $this->model()::all();
    }

    public function changeStatus(Hall $hall, $isActive)
    {
        $isActive = ( $isActive == true ) ? false : true;
        $this->update($hall, ['is_active' => $isActive]);
        return $hall;
    }
}
