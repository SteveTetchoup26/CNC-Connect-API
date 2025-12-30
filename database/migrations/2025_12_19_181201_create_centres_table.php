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
        Schema::create('centres', function (Blueprint $table) {
            $table->id();
            $table->string('libelle_centre');
            $table->enum("type_centre", ["CENTRE_DE_DEPÔT", "CENTRE_EXAMEN"]);
            $table->string('ville_centre');
            $table->integer('capacite');
            $table->integer('region_id');
            
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
            $table->timestamps();

            $table->index('type_centre');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centres');
    }
};
