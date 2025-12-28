<?php

namespace App\Services;

use App\Models\StudentBlockList;
use Illuminate\Support\Facades\DB;

class StudentBlockListService extends BaseModelService
{
    public function model(): string
    {
        return StudentBlockList::class;
    }

    public function getAll()
    {
        return $this->model()::with(['student', 'blockedBy'])
            ->whereNull('deleted_at')
            ->orderByDesc('blocked_at')
            ->get();
    }

    public function findById($id)
    {
        return $this->model()::with(['student', 'blockedBy'])->findOrFail($id);
    }

    public function createEntry(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->model()::create($data);
        });
    }

    public function updateEntry($id, array $data)
    {
        $entry = $this->model()::findOrFail($id);
        return $this->update($entry, $data);
    }

    public function deleteEntry($id)
    {
        return $this->delete($id);
    }
}
