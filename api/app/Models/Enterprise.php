<?php

namespace App\Models;

use App\Enums\EnterpriseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Empresa Asociada: unidad de negocio o marca que pertenece a un Holding.
 * Nivel intermedio de la jerarquía — pertenece a un Company y tiene muchas Branch.
 */
class Enterprise extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'enterprises_status' => 'active',
    ];

    protected $fillable = [
        'company_id',
        'name',
        'doc_number',
        'email',
        'phone',
        'enterprises_status',
    ];

    protected function casts(): array
    {
        return [
            'enterprises_status' => EnterpriseStatus::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branchs(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('enterprises_status', EnterpriseStatus::Active);
    }
}
