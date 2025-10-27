<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CalonSiswaRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test calon siswa accounts for different registration stages
        
        // 1. Fresh registration - no documents uploaded yet
        User::create([
            'name' => 'Ahmad Calon Siswa',
            'email' => 'ahmad.calon@test.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'calon_siswa',
            'registration_status' => 'pending_documents',
            'tanggal_lahir' => '2005-03-15',
            'tempat_lahir' => 'Jakarta',
            'jenis_kelamin' => 'L',
            'nama_orang_tua' => 'Budi Santoso',
            'phone_orang_tua' => '081234567891',
            'alamat_orang_tua' => 'Jl. Test No. 1, Jakarta',
        ]);

        // 2. Documents uploaded - pending admin approval
        User::create([
            'name' => 'Siti Calon Siswa',
            'email' => 'siti.calon@test.com',
            'password' => Hash::make('password'),
            'phone' => '081234567892',
            'role' => 'calon_siswa',
            'registration_status' => 'pending_approval',
            'tanggal_lahir' => '2005-05-20',
            'tempat_lahir' => 'Bandung',
            'jenis_kelamin' => 'P',
            'nama_orang_tua' => 'Andi Pratama',
            'phone_orang_tua' => '081234567893',
            'alamat_orang_tua' => 'Jl. Test No. 2, Bandung',
            'ktp_orang_tua_path' => 'registration_documents/2_ktp_orang_tua_test.jpg',
            'ijazah_path' => 'registration_documents/2_ijazah_test.jpg',
            'foto_siswa_path' => 'registration_documents/2_foto_siswa_test.jpg',
            'bukti_pembayaran_path' => 'registration_documents/2_bukti_pembayaran_test.jpg',
            'submitted_at' => now(),
        ]);

        // 3. Approved registration
        $approver = User::where('role', 'admin')->first();
        
        User::create([
            'name' => 'Rudi Calon Siswa',
            'email' => 'rudi.calon@test.com',
            'password' => Hash::make('password'),
            'phone' => '081234567894',
            'role' => 'calon_siswa',
            'registration_status' => 'approved',
            'tanggal_lahir' => '2005-07-10',
            'tempat_lahir' => 'Surabaya',
            'jenis_kelamin' => 'L',
            'nama_orang_tua' => 'Dewi Lestari',
            'phone_orang_tua' => '081234567895',
            'alamat_orang_tua' => 'Jl. Test No. 3, Surabaya',
            'ktp_orang_tua_path' => 'registration_documents/3_ktp_orang_tua_test.jpg',
            'ijazah_path' => 'registration_documents/3_ijazah_test.jpg',
            'foto_siswa_path' => 'registration_documents/3_foto_siswa_test.jpg',
            'bukti_pembayaran_path' => 'registration_documents/3_bukti_pembayaran_test.jpg',
            'submitted_at' => now()->subDays(2),
            'approved_by' => $approver?->id,
            'approved_at' => now()->subDay(),
            'approval_notes' => 'Semua dokumen lengkap dan valid.',
        ]);

        // 4. Rejected registration
        User::create([
            'name' => 'Adi Calon Siswa',
            'email' => 'adi.calon@test.com',
            'password' => Hash::make('password'),
            'phone' => '081234567896',
            'role' => 'calon_siswa',
            'registration_status' => 'rejected',
            'tanggal_lahir' => '2005-09-25',
            'tempat_lahir' => 'Medan',
            'jenis_kelamin' => 'L',
            'nama_orang_tua' => 'Sri Wahyuni',
            'phone_orang_tua' => '081234567897',
            'alamat_orang_tua' => 'Jl. Test No. 4, Medan',
            'ktp_orang_tua_path' => 'registration_documents/4_ktp_orang_tua_test.jpg',
            'ijazah_path' => 'registration_documents/4_ijazah_test.jpg',
            'foto_siswa_path' => 'registration_documents/4_foto_siswa_test.jpg',
            'bukti_pembayaran_path' => 'registration_documents/4_bukti_pembayaran_test.jpg',
            'submitted_at' => now()->subDays(3),
            'approved_by' => $approver?->id,
            'approved_at' => now()->subDays(2),
            'approval_notes' => 'Foto ijazah tidak jelas, mohon upload ulang dengan kualitas lebih baik.',
        ]);

        $this->command->info('Calon Siswa registration test data created successfully!');
    }
}
