<?php

namespace App\Services;

use App\Models\HallAttachment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class HallAttachmentService extends BaseModelService
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
        return HallAttachment::class;
    }

    public function getHallAttachments()
    {
        return $this->model()::with(['hall', 'student'])->get();
    }

    public function getHallAttachmentByProvost()
    {
        $user = auth()->user();
        $hallIds = $user->halls;

        return $this->model()::with(['hall', 'student'])
            ->whereIn('hall_id', $hallIds)
            ->get();
    }

    public function createHallAttachment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $hallAttachment = $this->create($data);
            $student = Student::find($hallAttachment->student_id);
            if($student) {
                $student->hall_id = $hallAttachment->hall_id;
                $student->hall_status = "attachment";
                $student->save();
            }
            return $hallAttachment;
        });
    }

    public function updateHallAttachment($hallAttachment, array $data)
    {
        return DB::transaction(function () use ($hallAttachment, $data) {
            $updateAttachment = $this->update($hallAttachment,$data);
            $student = Student::find($hallAttachment->student_id);
            if($student) {
                $student->hall_id = $hallAttachment->hall_id;
                $student->hall_status = "attachment";
                $student->save();
            }
            return $hallAttachment;
        });
    }

    public function deleteHallAttachment($hallAttachment)
    {
        return DB::transaction(function () use ($hallAttachment) {
            $student = Student::find($hallAttachment->student_id);
            if($student) {
                $student->hall_id = null;
                $student->hall_status = null;
                $student->save();
            }
            return $this->delete($hallAttachment->id);
        });
    }

    public function changeStatus(HallAttachment $hallAttachment, $isActive)
    {
        $isActive = ( $isActive == true ) ? false : true;
        $this->update($hallAttachment, ['is_active' => $isActive]);
        return $hallAttachment;
    }
}
