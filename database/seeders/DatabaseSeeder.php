<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\QrCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create guru user
        $guru = User::create([
            'name' => 'Guru Matematika',
            'email' => 'guru@sekolah.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        // Create siswa users
        $siswa1 = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@sekolah.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
        ]);

        $siswa2 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@sekolah.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
        ]);

        $siswa3 = User::create([
            'name' => 'Citra Lestari',
            'email' => 'citra@sekolah.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
        ]);

        // Create student records
        Student::create([
            'user_id' => $siswa1->id,
            'nis' => '2023001',
            'kelas' => 'X IPA 1',
            'nama_ortu' => 'Bapak Wijaya',
            'kontak_ortu' => '081234567890',
        ]);

        Student::create([
            'user_id' => $siswa2->id,
            'nis' => '2023002',
            'kelas' => 'X IPA 1',
            'nama_ortu' => 'Ibu Santoso',
            'kontak_ortu' => '081234567891',
        ]);

        Student::create([
            'user_id' => $siswa3->id,
            'nis' => '2023003',
            'kelas' => 'X IPA 2',
            'nama_ortu' => 'Bapak Lestari',
            'kontak_ortu' => '081234567892',
        ]);

        // Create sample QR Code
        QrCode::create([
            'token' => 'sampletoken123456',
            'expired_at' => now()->addHours(2),
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@sekolah.id / password123');
        $this->command->info('Guru: guru@sekolah.id / password123');
        $this->command->info('Siswa: andi@sekolah.id / password123');
    }
}