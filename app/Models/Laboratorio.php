<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Laboratorio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre'
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
