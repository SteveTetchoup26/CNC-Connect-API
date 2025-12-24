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
        Schema::create('ecoles', function (Blueprint $table) {
            $table->id();
            $table->string('libelle_ecole');
            $table->string('code_ecole')->unique();
            $table->string('localisation');
            $table->string('logo_url')->nullable();
            $table->string('bp_ecole')->nullable();
            $table->string('email_ecole')->unique();
            $table->string('siteweb_ecole')->nullable();
            $table->string('devise')->nullable();
            $table->string('telephone_ecole')->nullable();
            $table->string('embleme_ecole')->nullable();
            $table->timestamps();

            $table->index('code_ecole');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecoles');
    }
};
