<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Crée 3 admins liés à un user
        \App\Models\User::factory()->count(3)->create()->each(function ($user) {
            \App\Models\Admin::factory()->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
