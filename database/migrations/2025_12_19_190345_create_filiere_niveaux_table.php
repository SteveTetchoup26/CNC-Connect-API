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
        Schema::create('filiere_niveaux', function (Blueprint $table) {
            $table->id();
            $table->integer('niveau_id');
            $table->integer('filiere_id');
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
            $table->foreign('filiere_id')->references('id')->on('filieres')->onDelete('cascade');
            $table->unique(["niveau_id", "filiere_id"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filiere_niveaux');
    }
};
