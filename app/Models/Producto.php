<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoria_id',
        'laboratorio_id',
        'codigo',
        'descripcion',
        // 'slug',
        'imagen',
        'stock',
        'precio_compra',
        'precio_venta',
        'precio_mayor',
    ];

    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(laboratorio::class);
    }
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }
    // public function last_sale()
    // {
    //     return $this->ventas()->orderByDesc('created_at')->first();
    // }
}
