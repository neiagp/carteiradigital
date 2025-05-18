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
        Schema::create('transacoes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('remetente_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('destinatario_id')->nullable()->constrained('users')->nullOnDelete();
            $t->decimal('valor', 10, 2);
            $t->enum('tipo', ['deposito', 'transferencia']);
            $t->boolean('revertida')->default(false);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes');
    }
};
