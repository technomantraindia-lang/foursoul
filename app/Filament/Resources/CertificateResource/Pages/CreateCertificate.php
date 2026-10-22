<?php

declare(strict_types=1);

namespace App\Filament\Resources\CertificateResource\Pages;

use App\Filament\Resources\CertificateResource;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\User;
use App\Services\CertificateService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCertificate extends CreateRecord
{
    protected static string $resource = CertificateResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = User::query()->findOrFail($data['user_id']);
        $course = Course::query()->findOrFail($data['course_id']);
        $template = ! empty($data['certificate_template_id'])
            ? CertificateTemplate::query()->find($data['certificate_template_id'])
            : null;

        return app(CertificateService::class)->issue($user, $course, $template);
    }
}
