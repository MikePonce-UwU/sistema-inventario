<?php

namespace App\Filament\Resources\SalidaResource\Pages;

use App\Filament\Resources\SalidaResource;
use App\Models\Producto;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSalida extends CreateRecord
{
    protected static string $resource = SalidaResource::class;

    protected function beforeCreate(): void
    {
        // dd($this->data);
        foreach ($this->data['productos'] as $key => $prod) :
            // dump($prod);
            $producto = Producto::findOrFail($prod['producto_id']);
            $producto->stock -= intval($prod['cantidad']);
            $producto->save();

            $segundo = Producto::where('descripcion', $producto->descripcion)->where('laboratorio_id', $this->data['to_id'])->first();
            if ($segundo) {
                $segundo->update([
                    'stock' => $prod['cantidad'] + $segundo->stock ?? 0,
                ]);
            } else {
                $segundo = Producto::create([
                    'descripcion' => $producto->descripcion,
                    'categoria_id' => $producto->categoria_id,
                    'laboratorio_id' => $this->data['to_id'],
                    'codigo' => $producto->codigo,
                    'imagen' => $producto->imagen,
                    'stock' => $prod['cantidad'],
                    'precio_compra' => $producto->precio_compra,
                    'precio_venta' => $producto->precio_venta,
                    'precio_mayor' => $producto->precio_mayor,
                ]);
            }
            // dump($segundo);
        endforeach;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }
}
