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
        Schema::create('frais_concours', function (Blueprint $table) {
            $table->id();
            $table->double("montant_frais");
            $table->string("nom_banque");
            $table->string("numero_compte");
            $table->integer('concours_id');

            $table->foreign('concours_id')->references('id')->on('concours')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frais_concours');
    }
};
