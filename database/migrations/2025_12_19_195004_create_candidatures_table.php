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
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();

            $table->integer('candidat_id');
            $table->integer('concours_id');

            $table->string('code_candidat_temp')->nullable(); // Code temporaire
            $table->string('code_candidat_def')->nullable()->unique(); // Code définitif

            // Dates importantes
            $table->dateTime('date_candidature')->useCurrent(); // Date de création
            $table->date('date_inscription')->nullable(); // Date d'inscription au concours
            $table->date('date_depot_physique')->nullable(); // Date dépôt dossier physique
            $table->timestamp('date_validation')->nullable(); // Date validation dossier

            // QR Code et token
            $table->string('qr_code')->nullable()->unique();

            $table->text('motif_rejet')->nullable();

            $table->foreign('candidat_id')
                  ->references('id')
                  ->on('candidats')
                  ->onDelete('cascade');

            $table->foreign('concours_id')
                  ->references('id')
                  ->on('concours')
                  ->onDelete('cascade');

            $table->timestamps();

            $table->index('code_candidat_temp');
            $table->index('code_candidat_def');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
