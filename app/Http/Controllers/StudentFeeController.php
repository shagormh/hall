<?php

namespace App\Http\Controllers;

use App\Services\StudentFeeService;
use App\Services\HallService;
use App\Services\StudentService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\StudentFee;

class StudentFeeController extends Controller implements HasMiddleware
{
    protected StudentFeeService $feeService;
    protected HallService $hallService;
    protected StudentService $studentService;

    public function __construct(StudentFeeService $feeService, HallService $hallService, StudentService $studentService)
    {
        $this->feeService = $feeService;
        $this->hallService = $hallService;
        $this->studentService = $studentService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:can-view-student-fee', only: ['index']),
            new Middleware('permission:can-create-student-fee', only: ['create', 'store', 'parseVoucher']),
            new Middleware('permission:can-edit-student-fee', only: ['edit', 'update']),
            new Middleware('permission:can-delete-student-fee', only: ['destroy']),
            new Middleware('permission:can-approve-student-fee', only: ['updateStatus']),
        ];
    }

    /**
     * Display the Hall Fee Checker page.
     */
    public function checker(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('hallFeeChecker');
        return Inertia::render('StudentFee/Checker', [
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Hall Fee Checker',
        ]);
    }

    /**
     * API: Search students for the checker.
     */
    public function searchStudents(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json([]);
        
        $students = $this->feeService->searchStudentsForChecker($query);
        return response()->json($students);
    }

    /**
     * API: Get fee summary for a specific student.
     */
    public function getSummary($studentId)
    {
        $summary = $this->feeService->getFeeSummary($studentId);
        return response()->json($summary);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $breadcrumbs = Breadcrumbs::generate('studentFees');
        
        $params = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'hall_id' => $request->query('hall_id'),
        ];

        // Scoping for Provosts (overwrites request hall_id if not super admin)
        $user = Auth::user();
        if (!$user->hasRole('super admin')) {
            $params['hall_id'] = $user->halls;
        }

        $fees = $this->feeService->getAllFees($params);
        $halls = $this->hallService->getHalls();

        return Inertia::render('StudentFee/Index', [
            'fees' => $fees,
            'halls' => $halls,
            'filters' => $request->only(['search', 'status', 'hall_id']),
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Student Fees',
            'canApprove' => $user->can('can-approve-student-fee'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumbs = Breadcrumbs::generate('payStudentFee');
        $students = $this->studentService->getStudents();
        $halls = $this->hallService->getHalls();

        return Inertia::render('StudentFee/Create', [
            'students' => $students,
            'halls' => $halls,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => 'Pay Student Fee',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'hall_id' => 'required|exists:halls,id',
            'transaction_id' => 'required|unique:student_fees,transaction_id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'fee_details' => 'nullable|string',
            'voucher_path' => 'nullable|string',
        ], [
            'transaction_id.required' => 'লেনদেন আইডি প্রয়োজন।',
            'transaction_id.unique' => 'এই লেনদেন আইডি দিয়ে ইতিমধ্যে ফি জমা দেওয়া হয়েছে। অনুগ্রহ করে সঠিক লেনদেন আইডি নিশ্চিত করুন।',
            'student_id.required' => 'শিক্ষার্থী নির্বাচন করুন।',
            'student_id.exists' => 'নির্বাচিত শিক্ষার্থী খুঁজে পাওয়া যায়নি।',
            'hall_id.required' => 'হল নির্বাচন করুন।',
            'hall_id.exists' => 'নির্বাচিত হল খুঁজে পাওয়া যায়নি।',
            'amount.required' => 'টাকার পরিমাণ প্রয়োজন।',
            'amount.numeric' => 'টাকার পরিমাণ সংখ্যা হতে হবে।',
            'amount.min' => 'টাকার পরিমাণ ১ টাকার বেশি হতে হবে।',
            'payment_date.required' => 'পরিশোধের তারিখ প্রয়োজন।',
            'payment_date.date' => 'সঠিক তারিখ প্রদান করুন।',
        ]);

        // Security Check 1: Students can only submit their own fees
        $currentUser = Auth::user();
        $student = $this->studentService->findOrFail($validated['student_id']);
        
        // If the current user is a student (not admin/provost), ensure they can only submit their own fee
        if ($currentUser->hasRole('student') && $student->user_id !== $currentUser->id) {
            return redirect()->back()
                ->withErrors(['student_id' => 'আপনি শুধুমাত্র নিজের ফি জমা দিতে পারবেন। অন্য কারো পক্ষে ফি জমা দেওয়া নিষিদ্ধ।'])
                ->withInput();
        }

        // Security Check 2: Verify voucher belongs to the selected student
        if (!empty($validated['voucher_path'])) {
            $voucherFullPath = Storage::disk('public')->path($validated['voucher_path']);
            
            if (file_exists($voucherFullPath)) {
                try {
                    $content = shell_exec("pdftotext -layout {$voucherFullPath} -");
                    if ($content) {
                        $hasRoll = str_contains($content, $student->roll);
                        $hasRegistration = $student->registration && str_contains($content, $student->registration);
                        
                        if (!$hasRoll && !$hasRegistration) {
                            // Delete the fraud attempt file
                            Storage::disk('public')->delete($validated['voucher_path']);
                            
                            return redirect()->back()
                                ->withErrors(['voucher_path' => 'ভাউচারে আপনার রোল নম্বর (' . $student->roll . ') বা রেজিস্ট্রেশন নম্বর পাওয়া যায়নি। অনুগ্রহ করে শুধুমাত্র নিজের ভাউচার জমা দিন।'])
                                ->withInput();
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Voucher verification failed: " . $e->getMessage());
                    // Continue if PDF reading fails - manual verification will catch fraud
                }
            }
        }

        $validated['months_count'] = floor($validated['amount'] / 150);
        $validated['status'] = 'pending';

        $this->feeService->createFee($validated);

        return redirect()->route('student-fees.index')->with('success', 'Fee payment submitted for verification.');
    }

    /**
     * Parse the uploaded voucher image.
     */
    public function parseVoucher(Request $request)
    {
        $request->validate([
            'voucher' => 'required|mimes:jpeg,png,jpg,pdf|max:4096',
            'student_id' => 'required|exists:students,id',
        ]);

        $student = $this->studentService->findOrFail($request->student_id);
        $parsingResult = $this->feeService->processVoucherUpload($request->file('voucher'), $student);

        return response()->json($parsingResult);
    }

    /**
     * Update the status of a fee (approve/reject).
     */
    public function updateStatus(Request $request, StudentFee $studentFee)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
        ]);

        $this->feeService->updateStatus(
            $studentFee->id,
            $request->status,
            Auth::id(),
            $request->rejection_reason
        );

        return redirect()->back()->with('success', 'Fee status updated successfully.');
    }
}
