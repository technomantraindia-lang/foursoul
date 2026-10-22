<?php

declare(strict_types=1);

namespace App\Enums;

enum PlanCourseAccessType: string
{
    case AllCourses = 'all_courses';
    case SelectedCourses = 'selected_courses';

    public function label(): string
    {
        return match ($this) {
            self::AllCourses => 'All Courses',
            self::SelectedCourses => 'Selected Courses',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}