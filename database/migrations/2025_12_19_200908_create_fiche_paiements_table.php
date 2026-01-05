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
        Schema::create('fiche_paiements', function (Blueprint $table) {
            $table->id();
            $table->string("fichier_url");
            $table->integer("candidat_id");

            $table->boolean('ocr_verifie')->default(false);
            $table->json('ocr_donnees')->nullable();
            $table->timestamp('ocr_date_verification')->nullable();
            $table->text('ocr_erreurs')->nullable();

            $table->foreign('candidat_id')->references('id')->on('candidats')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiche_paiements');
    }
};
