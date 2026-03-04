<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_bn',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dispatchBatches()
    {
        return $this->hasMany(CourtDispatchBatch::class, 'court_id');
    }

    public function movements()
    {
        return $this->hasMany(FileMovement::class, 'court_id');
    }

    public function displayName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return $locale === 'bn' ? ($this->name_bn ?: $this->name_en) : ($this->name_en ?: $this->name_bn);
    }
}
