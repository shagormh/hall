<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Services\Permission\RoleService;

class UserService extends BaseModelService
{
    private $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function model(): string
    {
        return User::class;
    }

    /**
     * Get users and their roles
     * Convert the last_login into human readable format
     */
    public function getUsers()
    {
        $users = $this->model()::with('roles')->get()->map(function ($user) {
            if ($user->last_login_at) {
                $lastLogin = Carbon::parse($user->last_login_at);
                $user->last_login_at = $lastLogin->diffForHumans();
            } else {
                $user->last_login_at = "Never logged in";
            }
            return $user;
        });
        return $users;
    }

    /**
     * Create user
     * Assign Roles
     */
    public function createUser($validatedData)
    {
        $validatedData['password'] = Hash::make($validatedData['password']);
        $result = DB::transaction(function () use ($validatedData) {
            $user = $this->create($validatedData);
            if ($validatedData['roles']) {
                $user->assignRole($validatedData['roles']);
            }
            return $user;
        });

        return $result;
    }

    public function updateUser(User $user, $validatedData)
    {
        $result = DB::transaction(function () use ($user, $validatedData) {

            $oldUser = clone $user;
            $updatedName = $validatedData['name'] ?? $oldUser->name;
            $updatedEmail = $validatedData['email'] ?? $oldUser->email;
            $user->update($validatedData);

            if (array_key_exists('roles', $validatedData)) {
                $user->syncRoles($validatedData['roles']);
            }

            $isRoleRemoved = array_diff($this->roleIdsToArray($oldUser), $this->roleIdsToArray($user));
            $isRoleAdded = array_diff($this->roleIdsToArray($user), $this->roleIdsToArray($oldUser));


            return $user;
        });
        return $result ?? false;
    }

    public function deleteUser(User $user)
    {
        // Check if the user is referred to in other tables
        if ($this->hasReferences($user)) {
            return false;
        }

        // if user is assigned any role remove data from pivot table when deleting user.
        $result = DB::transaction(function () use ($user) {
            $oldUser = clone $user;
            $isDeleted = $this->delete($user->id);
            if ($isDeleted) {
                $user->roles()->detach();
            }
            return true;
        });
        return $result ? true : false;
    }

    private function hasReferences(User $user)
    {
        // Check for references
    }

    public function assignRoleToUser($validatedData)
    {
        $userId = $validatedData['user_id'];
        $roleId = $validatedData['role_id'];

        $user = $this->getUserById($userId);
        $role = $this->roleService->getRoleById($roleId);

        if ($user->hasRole($role)) {
            return false;
        }

        $user->assignRole($role);
        return true;
    }

    public function removeRoleFromUser($validatedData)
    {
        $userId = $validatedData['user_id'];
        $roleId = $validatedData['role_id'];

        $user = $this->getUserById($userId);
        $role = $this->roleService->getRoleById($roleId);

        if ($user->hasRole($role)) {
            $user->removeRole($role);
            return true;
        }
        return false;
    }

    public function getUserById($userId)
    {
        return User::find($userId)->with('roles');
    }

    public function getUserDetails(User $user)
    {
        $user = $user->load('roles');
        return $user;
    }

    public function updatePassword(User $user, $validatedData)
    {
        $oldUser = clone $user;
        $password = Hash::make($validatedData['password']);
        $user->update(['password' => $password]);
        return $user;
    }

    public function changeStatus(User $user, $isActive)
    {
        $oldUser = clone $user;
        $isActive = ($isActive == true) ? false : true;
        $user->update(['is_active' => $isActive]);
        return $user;
    }

    private function roleIdsToArray(User $user)
    {
        return $user->roles->pluck('id')->toArray();
    }

    private function extractUserProperties($user, $event = null)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => ($event === 'created') ? 1 : $user->is_active,
            'last_login_at' => $user->last_login_at,
            'role_ids' => $this->roleIdsToArray($user),
        ];
    }
}
