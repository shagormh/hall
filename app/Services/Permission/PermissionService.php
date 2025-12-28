<?php

namespace App\Services\Permission;

use App\Services\BaseModelService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionService extends BaseModelService
{
    public function model(): string
    {
        return Permission::class;
    }

    public function getPermissions()
    {
        $permissions = $this->model()::where('guard_name', 'web')->get()->groupBy('group_name');
        return $permissions;
    }

    public function getAllPermissions()
    {
        $permissions = $this->model()::all();
        return $permissions;
    }

    public function getGroups()
    {
        return $this->model()::select('group_name')->groupBy('group_name')->get();
    }

    public function createPermission($validatedData)
    {
        $result = DB::transaction(function () use ($validatedData) {
            $permission = $this->create($validatedData);
            return $permission;
        });
        return $result;
    }

    public function updatePermission(Permission $permission, $validatedData)
    {
        $result = DB::transaction(function () use ($permission, $validatedData) {
            $oldPermission= clone $permission;
            $permission->update($validatedData);
            return $permission;
        });
        return $result;
    }

    public function changeStatus(Permission $permission, $isActive)
    {
        $result = DB::transaction(function () use ($permission, $isActive) {
            $oldPermission = clone $permission;
            $isActive = ($isActive == true) ? false : true;
            $permission->update(['is_active' => $isActive]);

            return $permission;
        });
        return $result;
    }

    private function extractPermissionProperties(Permission $permission, $event = null)
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'group_name' => $permission->group_name,
            'display_name' => $permission->display_name,
            'is_active' => ($event === 'created') ? 1 : $permission->is_active,
        ];
    }
}
