<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained();
            $table->foreignId('laboratorio_id')->constrained();
            $table->string('codigo');
            $table->string('descripcion');
            // $table->string('slug');
            $table->text('imagen')->nullable();
            $table->string('stock')->nullable();
            $table->string('precio_compra');
            $table->string('precio_venta');
            $table->string('precio_mayor');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
