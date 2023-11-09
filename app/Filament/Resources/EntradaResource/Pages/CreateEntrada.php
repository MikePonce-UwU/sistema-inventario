<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use App\Filament\Resources\EntradaResource;
use App\Models\Producto;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEntrada extends CreateRecord
{
    protected static string $resource = EntradaResource::class;

    protected function beforeCreate(): void
    {
        // dd($this->data);
        foreach ($this->data['productos'] as $key => $prod) :
            // dump($prod);
            $producto = Producto::findOrFail($prod['producto_id']);
            $producto->stock += intval($prod['cantidad']);
            // dump($producto);
            $producto->save();
        endforeach;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }
}
