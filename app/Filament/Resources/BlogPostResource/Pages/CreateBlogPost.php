<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Enums\PageStatus;
use App\Filament\Resources\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_admin_id'] = auth('admin')->id();

        if (($data['status'] ?? null) === PageStatus::Published->value && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Blog post created';
    }
}
