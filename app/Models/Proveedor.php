<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedores';
    protected $fillable = [
        'nombre',
        'nombre_contacto',
        'nombre_comercial',
        'email',
        'documento',
        'telefono',
        'direccion',
    ];
}
