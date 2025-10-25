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
        Schema::create('comptes', function (Blueprint $table) {
            // --- Identifiant principal ---
            $table->uuid('id')->primary()
                ->comment('Identifiant unique du compte (UUID)');

            // --- Données principales ---
            $table->string('numero_compte', 30)
                ->unique()
                ->comment('Numéro de compte généré automatiquement via mutator ou service');

            $table->enum('type', ['cheque', 'epargne', 'courant'])
                ->default('cheque')
                ->comment('Type de compte : chèque, épargne ou courant');

            $table->decimal('solde_initial', 15, 2)
                ->default(0)
                ->comment('Solde de départ à la création du compte');

            // $table->decimal('solde', 15, 2)
            //     ->default(0)
            //     ->comment('Solde courant du compte (calculé à partir des opérations)');

            $table->string('devise', 10)
                ->default('FCFA')
                ->comment('Devise du compte, ex: FCFA, EUR, USD');

            $table->string('statut', 20)
                ->default('actif')
                ->comment('Statut du compte : actif, suspendu, clôturé, etc.');

            // --- Relation Client ---
            $table->foreignUuid('client_id')
                ->constrained('clients')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Client titulaire du compte');

            // --- Métadonnées facultatives ---
            $table->json('metadata')
                ->nullable()
                ->comment('Données additionnelles du compte : tags, préférences, etc.');

            // --- Timestamps et Soft Delete ---
            $table->timestamps();
            $table->softDeletes();

            // --- Index pour performance ---
            $table->index('client_id', 'idx_compte_client');
            $table->index('statut', 'idx_compte_statut');
            $table->index(['numero_compte', 'client_id'], 'idx_compte_numero_client');
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
