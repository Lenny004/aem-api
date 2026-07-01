<?php

namespace App\Models;

use App\Enums\CompanyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Holding / Consorcio: entidad jurídica corporativa principal.
 * Raíz de la jerarquía — un Company tiene muchas Enterprise asociadas.
 */
class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'companys';

    protected $attributes = [
        'companys_status' => 'active',
    ];

    protected $fillable = [
        'name',
        'doc_number',
        'email',
        'phone',
        'companys_status',
    ];

    protected function casts(): array
    {
        return [
            'companys_status' => CompanyStatus::class,
        ];
    }

    public function enterprises(): HasMany
    {
        return $this->hasMany(Enterprise::class);
    }

    public function scopeActive($query)
    {
        return $query->where('companys_status', CompanyStatus::Active);
    }
}
