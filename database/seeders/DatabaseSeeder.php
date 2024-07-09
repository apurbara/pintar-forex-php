<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;


use DateTimeImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Resources\Uuid;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('Admin')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $options = [
            'cost' => 10
        ];
        $adminPassword = password_hash('password123', PASSWORD_DEFAULT, $options);
        DB::table('Admin')->insert([
            'id' => Uuid::generateUuid4(),
            'disabled' => false,
            'createdTime' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            'aSuperUser' => true,
            'name' => 'su admin',
            'email' => 'admin@email.org',
            'password' => $adminPassword,
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
