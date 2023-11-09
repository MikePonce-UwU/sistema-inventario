<?php

namespace App\Filament\Resources\VentaResource\Pages;

use App\Filament\Resources\VentaResource;
use App\Models\Producto;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateVenta extends CreateRecord
{
    protected static string $resource = VentaResource::class;

    protected function beforeCreate(): void
    {
        // dump($this->data);
        foreach ($this->data['productos'] as $key => $prod) :
            // dump($prod);
            $producto = Producto::findOrFail($prod['producto_id']);
            $producto->stock -= intval($prod['cantidad']);
            // dump($producto);
            $producto->save();
        endforeach;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }
}
