<?php

namespace App\Services;

use App\Models\Department;

class DepartmentService extends BaseModelService
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
        return Department::class;
    }

    public function getDepartments()
    {
        return $this->model()::all();
    }

    public function getActiveDepartments($isActive = true)
    {
        return $this->model()::where('is_active', $isActive)->get();
    }

    public function changeStatus(Department $department, $isActive)
    {
        $isActive = ( $isActive == true ) ? false : true;
        $this->update($department, ['is_active' => $isActive]);
        return $department;
    }
}
