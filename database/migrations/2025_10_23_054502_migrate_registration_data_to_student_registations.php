<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing registration data from users to student_registrations
        $calonSiswaUsers = DB::table('users')
            ->where('role', 'calon_siswa')
            ->get();

        foreach ($calonSiswaUsers as $user) {
            // Only insert if record doesn't exist (prevent duplicates)
            $exists = DB::table('student_registrations')
                ->where('user_id', $user->id)
                ->exists();
                
            if (!$exists) {
                DB::table('student_registrations')->insert([
                    'user_id' => $user->id,
                    'tanggal_lahir' => $user->tanggal_lahir,
                    'tempat_lahir' => $user->tempat_lahir,
                    'jenis_kelamin' => $user->jenis_kelamin,
                    'nama_orang_tua' => $user->nama_orang_tua,
                    'phone_orang_tua' => $user->phone_orang_tua,
                    'alamat_orang_tua' => $user->alamat_orang_tua,
                    'ktp_orang_tua_path' => $user->ktp_orang_tua_path,
                    'ijazah_path' => $user->ijazah_path,
                    'foto_siswa_path' => $user->foto_siswa_path,
                    'bukti_pembayaran_path' => $user->bukti_pembayaran_path,
                    'registration_status' => $user->registration_status ?? 'pending_documents',
                    'submitted_at' => $user->submitted_at,
                    'approved_at' => $user->approved_at,
                    'approval_notes' => $user->approval_notes,
                    'approved_by' => $user->approved_by,
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ]);
            }
        }
        
        // Log successful migration
        $count = DB::table('student_registrations')->count();
        echo "Successfully migrated {$count} calon siswa registration records.\n";
    }

    public function down(): void
    {
        // Clear student_registrations table if rollback needed
        DB::table('student_registrations')->truncate();
        echo "Rolled back student_registrations data.\n";
    }
};