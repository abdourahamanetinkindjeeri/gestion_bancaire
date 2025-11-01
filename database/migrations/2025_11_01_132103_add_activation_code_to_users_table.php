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
        Schema::table('users', function (Blueprint $table) {
            $table->string('activation_code', 6)->nullable()
                ->comment('Code d\'activation à 6 chiffres pour la création de compte');
            $table->timestamp('activation_code_expires_at')->nullable()
                ->comment('Date d\'expiration du code d\'activation');
            $table->boolean('is_activated')->default(false)
                ->comment('Indique si le compte utilisateur est activé');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['activation_code', 'activation_code_expires_at', 'is_activated']);
        });
    }
};
