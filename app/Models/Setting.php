<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Prompt 1 (Step 8): simple key/value settings model.
 *
 * Values are stored as text with an explicit type tag so the SettingsService
 * can cast them back to their native PHP types on read.
 */
class Setting extends Model
{
    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'float';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_JSON = 'json';

    protected $fillable = ['group', 'key', 'value', 'type'];

    /**
     * Decode a stored value according to its type tag.
     */
    public function decodedValue(): mixed
    {
        return match ($this->type) {
            self::TYPE_INTEGER => (int) $this->value,
            self::TYPE_FLOAT => (float) $this->value,
            self::TYPE_BOOLEAN => filter_var($this->value, FILTER_VALIDATE_BOOL),
            self::TYPE_JSON => json_decode((string) $this->value, true),
            default => $this->value,
        };
    }

    /**
     * Encode a PHP value into its storable string form + type tag.
     *
     * @return array{value: ?string, type: string}
     */
    public static function encodeValue(mixed $value): array
    {
        if ($value === null) {
            return ['value' => null, 'type' => self::TYPE_STRING];
        }

        return match (true) {
            is_bool($value) => ['value' => $value ? '1' : '0', 'type' => self::TYPE_BOOLEAN],
            is_int($value) => ['value' => (string) $value, 'type' => self::TYPE_INTEGER],
            is_float($value) => ['value' => (string) $value, 'type' => self::TYPE_FLOAT],
            is_array($value) => ['value' => json_encode($value), 'type' => self::TYPE_JSON],
            default => ['value' => (string) $value, 'type' => self::TYPE_STRING],
        };
    }
}