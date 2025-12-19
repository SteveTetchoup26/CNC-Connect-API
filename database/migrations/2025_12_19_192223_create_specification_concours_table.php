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
        Schema::create('specification_concours', function (Blueprint $table) {
            $table->id();
            $table->integer('concours_id');
            $table->string('desc_infos_concours')->nullable();
            $table->boolean('carte_nationale_identite')->default(false);
            $table->boolean('diplomes')->default(false);
            $table->boolean('certificat_nationalite')->default(false);
            $table->boolean('releve_notes')->default(false);
            $table->boolean('acte_naissance')->default(false);
            $table->boolean('photo')->default(false);
            $table->foreign('concours_id')->references('id')->on('concours')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specification_concours');
    }
};
