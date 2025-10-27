<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['approved_by']);
            
            // Remove registration-specific columns
            $table->dropColumn([
                // Personal info (moved to student_registrations)
                'tanggal_lahir',
                'tempat_lahir',
                'jenis_kelamin',
                
                // Parent info (moved to student_registrations)  
                'nama_orang_tua',
                'phone_orang_tua',
                'alamat_orang_tua',
                
                // Document paths (moved to student_registrations)
                'ktp_orang_tua_path',
                'ijazah_path',
                'foto_siswa_path',
                'bukti_pembayaran_path',
                
                // Registration workflow (moved to student_registrations)
                'registration_status',
                'submitted_at',
                'approved_at',
                'approval_notes',
                'approved_by',
            ]);
        });
        
        echo "Successfully removed 15 registration fields from users table.\n";
        echo "Users table is now clean with only core authentication fields.\n";
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add registration fields if rollback needed
            $table->string('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('nama_orang_tua')->nullable();
            $table->string('phone_orang_tua')->nullable();
            $table->text('alamat_orang_tua')->nullable();
            $table->string('ktp_orang_tua_path')->nullable();
            $table->string('ijazah_path')->nullable();
            $table->string('foto_siswa_path')->nullable();
            $table->string('bukti_pembayaran_path')->nullable();
            $table->enum('registration_status', ['pending_documents', 'pending_payment', 'pending_approval', 'approved', 'rejected'])
                  ->default('pending_documents');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }
};