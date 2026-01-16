<?php

namespace App\Services;

use App\Models\FeeConfiguration;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FeeConfigurationService
{
    /**
     * Get paginated fee configurations based on filters
     *
     * @param array $filters
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFeeConfigurations(array $filters, User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = FeeConfiguration::with(['hall', 'creator', 'updater']);

        // Filter by hall for non-super-admins
        if (!$user->hasRole('super admin')) {
            $hallIds = $user->halls ?? []; // Halls is already an array of IDs
            $query->whereIn('hall_id', $hallIds);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('fee_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Hall filter
        if (!empty($filters['hall_id'])) {
            $query->where('hall_id', $filters['hall_id']);
        }

        // Fee type filter
        if (!empty($filters['fee_type'])) {
            $query->where('fee_type', $filters['fee_type']);
        }

        // Status filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new fee configuration
     *
     * @param array $data
     * @param User $user
     * @return FeeConfiguration
     */
    public function createFeeConfiguration(array $data, User $user): FeeConfiguration
    {
        return DB::transaction(function () use ($data, $user) {
            // Handle active status - deactivate existing if this one is active
            if ($data['is_active'] ?? true) {
                $this->deactivateExistingConfigs($data['hall_id'] ?? null, $data['fee_type'], $user->id);
            }

            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            $data['is_active'] = $data['is_active'] ?? true;

            return FeeConfiguration::create($data);
        });
    }

    /**
     * Update a fee configuration
     *
     * @param FeeConfiguration $feeConfiguration
     * @param array $data
     * @param User $user
     * @return FeeConfiguration
     */
    public function updateFeeConfiguration(FeeConfiguration $feeConfiguration, array $data, User $user): FeeConfiguration
    {
        return DB::transaction(function () use ($feeConfiguration, $data, $user) {
            // If activating this configuration, deactivate others
            if (($data['is_active'] ?? false) && !$feeConfiguration->is_active) {
                $this->deactivateExistingConfigs(
                    $data['hall_id'] ?? $feeConfiguration->hall_id,
                    $data['fee_type'] ?? $feeConfiguration->fee_type,
                    $user->id,
                    $feeConfiguration->id
                );
            }

            $data['updated_by'] = $user->id;
            $feeConfiguration->update($data);

            return $feeConfiguration;
        });
    }

    /**
     * Delete a fee configuration
     *
     * @param FeeConfiguration $feeConfiguration
     * @return bool|null
     */
    public function deleteFeeConfiguration(FeeConfiguration $feeConfiguration): ?bool
    {
        return $feeConfiguration->delete();
    }

    /**
     * Deactivate existing active configurations for the same hall and fee type
     *
     * @param int|null $hallId
     * @param string $feeType
     * @param int $userId
     * @param int|null $excludeId
     * @return void
     */
    protected function deactivateExistingConfigs(?int $hallId, string $feeType, int $userId, ?int $excludeId = null): void
    {
        $query = FeeConfiguration::where('hall_id', $hallId)
            ->where('fee_type', $feeType)
            ->where('is_active', true);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $query->update(['is_active' => false, 'updated_by' => $userId]);
    }
}
