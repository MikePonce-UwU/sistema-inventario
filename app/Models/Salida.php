<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salida extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'from_id',
        'to_id',
        'codigo',
        'descripcion',
        'productos',
    ];

    protected $casts = [
        'productos' => 'json',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function from(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class, 'from_id');
    }
    public function to(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class, 'to_id');
    }
}
