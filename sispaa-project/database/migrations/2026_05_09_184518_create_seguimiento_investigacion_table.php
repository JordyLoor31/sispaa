<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimiento_investigacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigacion_id')->constrained('investigaciones')->cascadeOnDelete();
            $table->string('pregunta');
            $table->text('respuesta')->nullable();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();
 
            $table->index(['investigacion_id', 'orden']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_investigacion');
    }
};