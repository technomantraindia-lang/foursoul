<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaqCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'faq_categories';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'sort_order',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FaqItem::class)->orderBy('sort_order');
    }

    public function visibleItems(): HasMany
    {
        return $this->items()->where('is_visible', true);
    }
}