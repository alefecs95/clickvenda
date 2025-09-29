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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            
            // Identificação do veículo/equipamento
            $table->string('plate')->nullable(); // Placa (para veículos)
            $table->string('model')->nullable(); // Modelo
            $table->string('brand')->nullable(); // Marca
            $table->string('year')->nullable(); // Ano
            $table->string('serial_number')->nullable(); // Número de série
            $table->string('chassis_number')->nullable(); // Número do chassi
            $table->string('engine_number')->nullable(); // Número do motor
            
            // Tipo de veículo/equipamento
            $table->enum('type', [
                'veiculo',      // Veículo
                'equipamento',  // Equipamento
                'maquina',      // Máquina
                'outro'         // Outro
            ])->default('veiculo');
            
            // Características
            $table->string('color')->nullable(); // Cor
            $table->string('fuel_type')->nullable(); // Tipo de combustível
            $table->integer('mileage')->nullable(); // Quilometragem
            $table->text('observations')->nullable(); // Observações
            
            // Status
            $table->boolean('active')->default(true);
            
            $table->timestamps();
            
            // Índices
            $table->index(['customer_id', 'active']);
            $table->index(['plate', 'active']);
            $table->index(['serial_number', 'active']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};