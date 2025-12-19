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
        Schema::create('historique_candidatures', function (Blueprint $table) {
            $table->id();
            $table->dateTime("date_etat");
            $table->integer('etat_id');
            $table->integer('candidature_id');
            $table->foreign('etat_id')->references('id')->on('etat_candidatures')->onDelete('cascade');
            $table->foreign('candidature_id')->references('id')->on('candidatures')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_candidatures');
    }
};
