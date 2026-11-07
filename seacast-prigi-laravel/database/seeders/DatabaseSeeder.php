<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's development database with demo personas.
     * Strictly restricted to non-production environments.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DatabaseSeeder dengan akun demo dilarang dijalankan pada lingkungan produksi.');
        }

        $personas = [
            [
                'name' => 'Nakhoda Sukirno (Nelayan)',
                'email' => 'nelayan@seacast.id',
                'role' => 'nelayan',
                'password' => Hash::make('seacast2026'),
            ],
            [
                'name' => 'Kapten Bambang (Syahbandar PPN Prigi)',
                'email' => 'syahbandar@seacast.id',
                'role' => 'syahbandar',
                'password' => Hash::make('seacast2026'),
            ],
            [
                'name' => 'Dr. Suryo Prasojo (Peneliti Oseanografi)',
                'email' => 'peneliti@seacast.id',
                'role' => 'peneliti',
                'password' => Hash::make('seacast2026'),
            ],
            [
                'name' => 'Admin SEACAST Prigi',
                'email' => 'admin@seacast.id',
                'role' => 'admin',
                'password' => Hash::make('seacast2026'),
            ],
        ];

        foreach ($personas as $p) {
            User::updateOrCreate(
                ['email' => $p['email']],
                $p
            );
        }
    }
}
