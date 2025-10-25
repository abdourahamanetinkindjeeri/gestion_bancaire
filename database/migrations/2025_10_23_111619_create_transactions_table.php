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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')
                ->primary()
                ->comment('Identifiant unique de la transaction (UUID)');

            $table->string('numero', 20)
                ->unique()
                ->comment('Numéro unique de la transaction, ex: T001234');

            $table->foreignUuid('compte_id')
                ->constrained('comptes')
                ->onDelete('cascade')
                ->comment('Référence UUID vers le compte concerné');

            $table->enum('type', ['depot', 'retrait', 'transfert'])
                ->comment('Type d’opération : dépôt, retrait ou transfert');

            $table->decimal('montant', 15, 2)
                ->comment('Montant de la transaction');

            $table->string('devise', 10)
                ->default('FCFA')
                ->comment('Devise utilisée pour la transaction');

            $table->enum('statut', ['en_attente', 'complete', 'echouee'])
                ->default('complete')
                ->comment('Statut de la transaction');

            $table->date('date_transaction')
                ->useCurrent()
                ->comment('Date effective de l’opération');

            $table->json('metadata')
                ->nullable()
                ->comment('Informations additionnelles sur la transaction (tags, références, etc.)');

            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les recherches
            $table->index(['numero', 'compte_id', 'type', 'statut']);
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
