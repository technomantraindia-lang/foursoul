<?php

declare(strict_types=1);

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function __construct(private readonly CertificateService $certificateService)
    {
    }

    /**
     * List the authenticated user's certificates.
     */
    public function index()
    {
        return view('learn.certificates', [
            'certificates' => Auth::user()->certificates()->with('course')->latest('issued_on')->get(),
        ]);
    }

    /**
     * Show a certificate for the owner.
     */
    public function show(Course $course, Certificate $certificate)
    {
        abort_unless((int) $certificate->course_id === (int) $course->id, 404);
        abort_unless($certificate->user_id === Auth::id(), 403);

        return view('learn.certificate', [
            'course' => $course,
            'certificate' => $certificate->load(['user', 'template']),
        ]);
    }

    /**
     * Public verification page by certificate code (no auth required).
     */
    public function verify(string $code)
    {
        $certificate = $this->certificateService->verify($code);

        return view('learn.certificate-verify', [
            'certificate' => $certificate,
            'code' => $code,
        ]);
    }
}
