<?php

namespace App\Models;

use App\Enums\BranchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Sucursal: punto físico de recolección de paquetes u operación,
 * asignado a una Enterprise. Hoja de la jerarquía.
 */
class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'branchs';

    protected $attributes = [
        'branchs_status' => 'active',
    ];

    protected $fillable = [
        'enterprise_id',
        'name',
        'address',
        'municipality_codigo',
        'phone',
        'branchs_status',
    ];

    protected function casts(): array
    {
        return [
            'branchs_status' => BranchStatus::class,
        ];
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function scopeActive($query)
    {
        return $query->where('branchs_status', BranchStatus::Active);
    }
}
