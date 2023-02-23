<?php

namespace App\Models;

use App\Models\Traits\CUDLogTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaterialOut extends Model
{
    use HasFactory, CUDLogTrait;

    protected $guarded = ['id'];

    protected $appends = [
        'id_for_human'
    ];

    protected $dates = [
        'at'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(MaterialOutDetail::class);
    }

    public function manufacture(): HasOne
    {
        return $this->hasOne(Manufacture::class);
    }

    public function getIdForHumanAttribute(): string
    {
        return $this->code ?? $this->at->format('d-m-Y') ?? null;
    }
}
