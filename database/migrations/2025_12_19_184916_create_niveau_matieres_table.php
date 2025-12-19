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
        Schema::create('niveau_matieres', function (Blueprint $table) {
            $table->id();
            $table->integer('niveau_id');
            $table->integer('matiere_id');
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
            $table->foreign('matiere_id')->references('id')->on('matieres')->onDelete('cascade');
            $table->unique(["niveau_id", "matiere_id"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveau_matieres');
    }
};
