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
        ]);

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
