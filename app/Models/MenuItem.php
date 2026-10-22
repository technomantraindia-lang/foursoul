<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';

    protected $fillable = [
        'uuid',
        'menu_id',
        'parent_id',
        'label',
        'url',
        'link_type',
        'linkable_id',
        'linkable_type',
        'opens_in_new_tab',
        'is_visible',
        'permission',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'opens_in_new_tab' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function visibleChildren(): HasMany
    {
        return $this->children()->where('is_visible', true);
    }
}