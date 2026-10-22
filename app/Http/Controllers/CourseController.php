<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\PricingCurrencyResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CourseController extends Controller
{
    public function __construct(private readonly PricingCurrencyResolver $currencyService) {}

    public function index(Request $request): View
    {
        $currency = $this->currencyService->resolve($request, session('currency'));
        $courses = Course::query()->published()->withCount('lessons')->latest('published_at')->get();
        $enrollments = $request->user()?->enrollments()->get()->keyBy('course_id') ?? collect();

        return view('courses.index', compact('courses', 'currency', 'enrollments'));
    }
}
