<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     public function up(): void
//     {
//         Schema::connection('neon')->create('comptes_bloque', function (Blueprint $table) {
//             $table->uuid('id')->primary()->comment('Identifiant unique du compte bloqué (UUID)');
//             $table->string('numero_compte', 30)->comment('Numéro de compte original');
//             $table->enum('type', ['cheque', 'epargne', 'courant'])->default('cheque');
//             $table->decimal('solde_initial', 15, 2)->default(0);
//             $table->string('devise', 10)->default('FCFA');
//             $table->string('statut', 20)->default('bloque');
//             $table->timestamp('debut_blocage')->nullable()->comment('Date de début du blocage');
//             $table->timestamp('fin_blocage')->nullable()->comment('Date de fin du blocage');
//             $table->foreignUuid('client_id')->comment('Client titulaire du compte');
//             $table->json('metadata')->nullable()->comment('Données additionnelles');
//             $table->timestamps();
//         });
//     }

//     public function down(): void
//     {
//         Schema::connection('neon')->dropIfExists('comptes_bloque');
//     }
// };
