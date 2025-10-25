<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécution de la migration.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary()
                ->comment('Identifiant unique du client (UUID)');

            $table->string('code_client', 15)
                ->unique()
                ->comment('Code unique client, ex: CL00000045');

            $table->string('titulaire', 255)
                ->comment('Nom complet du titulaire du compte');

            $table->string('nci', 14)
                ->unique()
                ->comment('Numéro de carte d’identité ou identifiant légal');

            $table->string('email', 255)
                ->unique()
                ->comment('Adresse email du client');

            $table->string('telephone', 20)
                ->unique()
                ->comment('Numéro de téléphone du client');

            $table->string('adresse', 255)
                ->nullable()
                ->comment('Adresse physique du client');

            // $table->string('password')
            //     ->nullable()
            //     ->comment('Mot de passe généré automatiquement à la création');

            // $table->string('code_activation', 10)
            //     ->nullable()
            //     ->comment('Code d’activation envoyé par SMS à la première connexion');

            $table->boolean('actif')
                ->default(true)
                ->comment('État du client : actif/inactif');

            $table->json('metadata')
                ->nullable()
                ->comment('Informations additionnelles (tags, préférences, etc.)');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['titulaire', 'email']);
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
