<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre_contacto',
        'cedula_contacto',
        'correo_contacto',
        'telefono_contacto',
        'nombre_comercial',
        'documento_comercial',
        'direccion',
        'monto',
        'dias',
    ];
}
