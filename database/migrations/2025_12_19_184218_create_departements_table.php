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
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->string('libelle_departement');
            $table->string('code_departement')->unique();
            $table->string('description_departement');
            $table->integer('ecole_id');
            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('cascade');
            $table->timestamps();

            $table->index("code_departement");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departements');
    }
};
