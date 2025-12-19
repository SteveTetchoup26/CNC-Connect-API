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
        Schema::create('candidats', function (Blueprint $table) {
            $table->id();
            $table->integer("utilisateur_id");

            // Informations personnelles
            $table->string('adresse');
            $table->string('nationalite');
            $table->enum('sexe', ['M', 'F']);
            $table->integer('age')->nullable();
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('region_origine');
            $table->string('ville_origine');
            $table->string('departement_origine');
            $table->string('statut_matrimonial')->nullable();
            $table->string('handicap')->nullable();

            // Pièce d'identité
            $table->string('numero_cni')->unique()->nullable();
            $table->date('date_delivrance_cni')->nullable();

            // Informations familiales
            $table->string('nom_mere');
            $table->string('telephone_mere');
            $table->string('nom_pere');
            $table->string('telephone_pere');
            $table->string('nom_tuteur')->nullable();
            $table->string('telephone_tuteur')->nullable();

            // Informations scolaires
            $table->string('niveau_scolaire');
            $table->string('filiere_origine')->nullable();
            $table->string('diplome_admission')->nullable();
            $table->string('mention')->nullable();
            $table->date('annee_diplome')->nullable();


            // Paiement
            $table->string('num_recu')->nullable()->unique();

            $table->foreign('utilisateur_id')
                  ->references('id')
                  ->on('utilisateurs')
                  ->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidats');
    }
};
