<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Secure avatar upload handling (§18).
 *
 * - Validates MIME type and extension (enforced by the FormRequest).
 * - Stores via the Laravel Storage abstraction (never absolute paths).
 * - Generates a random, non-guessable filename.
 * - Deletes the previous avatar to avoid orphaned files.
 */
final class AvatarService
{
    public function __construct(
        private readonly Filesystem $disk,
        private readonly string $directory = 'avatars',
    ) {
    }

    /**
     * Persist the uploaded avatar and return the relative storage path.
     *
     * @return string Path relative to the disk root (e.g. "avatars/abc.jpg").
     */
    public function store(UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $this->disk->putFileAs($this->directory, $file, $filename);
    }

    /**
     * Remove the avatar at the given relative path, if it exists.
     */
    public function delete(?string $path): void
    {
        if ($path && $this->disk->exists($path)) {
            $this->disk->delete($path);
        }
    }

    /**
     * Replace a user's avatar, deleting the previous file.
     */
    public function replace(User $user, UploadedFile $file): string
    {
        $previous = $user->avatar_path;
        $path = $this->store($file);
        $user->forceFill(['avatar_path' => $path])->save();
        $this->delete($previous);

        return $path;
    }
}
