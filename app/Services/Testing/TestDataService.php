<?php

declare(strict_types=1);

namespace App\Services\Testing;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Quiz;
use App\Models\User;
use App\Enums\UserStatus;
use Database\Seeders\LocalDemoSeeder;
use Illuminate\Support\Facades\DB;

class TestDataService
{
    public function ensure(): array
    {
        User::withTrashed()->whereIn('email', ['student@foursouls.test', 'locked@foursouls.test', 'third@test.foursouls.local'])->restore();
        Course::withTrashed()->where('slug', 'local-demo-course')->restore();
        BlogPost::withTrashed()->where('slug', 'local-welcome')->restore();
        app(LocalDemoSeeder::class)->run();
        $third = User::firstOrCreate(['email' => 'third@test.foursouls.local'], [
            'full_name' => 'Test Student Three',
            'password' => 'password',
        ]);
        $third->forceFill(['status' => UserStatus::Active])->save();

        return $this->counts();
    }

    public function counts(): array
    {
        return [
            'students' => User::whereIn('email', ['student@foursouls.test', 'locked@foursouls.test', 'third@test.foursouls.local'])->count(),
            'courses' => Course::where('slug', 'local-demo-course')->count(),
            'enrollments' => Enrollment::where('source', 'subscription')->whereHas('user', fn ($q) => $q->whereIn('email', ['student@foursouls.test', 'locked@foursouls.test']))->count(),
            'orders' => Order::where('source', 'local_demo')->count(),
            'quizzes' => Quiz::where('title', 'Local Demo Quiz')->count(),
            'blog_posts' => BlogPost::where('slug', 'local-welcome')->count(),
        ];
    }

    public function reset(): void
    {
        DB::transaction(function (): void {
            $users = User::whereIn('email', ['student@foursouls.test', 'locked@foursouls.test'])->get();
            Enrollment::whereIn('user_id', $users->pluck('id'))->delete();
            User::whereIn('id', $users->pluck('id'))->delete();
            User::where('email', 'third@test.foursouls.local')->delete();
            Course::where('slug', 'local-demo-course')->delete();
            Quiz::where('title', 'Local Demo Quiz')->delete();
            BlogPost::where('slug', 'local-welcome')->delete();
        });
    }
}
