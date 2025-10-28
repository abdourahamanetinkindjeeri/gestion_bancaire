<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     public function up(): void
//     {
//         Schema::connection('neon')->create('transactions_bloque', function (Blueprint $table) {
//             $table->uuid('id')->primary()->comment('Identifiant unique de la transaction (UUID)');
//             $table->string('numero', 20)->comment('Numéro unique de la transaction');
//             $table->foreignUuid('compte_id')->constrained('comptes_bloque')->cascadeOnDelete();
//             $table->enum('type', ['depot', 'retrait', 'transfert']);
//             $table->decimal('montant', 15, 2);
//             $table->string('devise', 10)->default('FCFA');
//             $table->enum('statut', ['en_attente', 'complete', 'echouee'])->default('complete');
//             $table->date('date_transaction')->useCurrent();
//             $table->json('metadata')->nullable();
//             $table->timestamps();

//             $table->index(['numero', 'compte_id', 'type', 'statut']);
//         });
//     }

//     public function down(): void
//     {
//         Schema::connection('neon')->dropIfExists('transactions_bloque');
//     }
// };
