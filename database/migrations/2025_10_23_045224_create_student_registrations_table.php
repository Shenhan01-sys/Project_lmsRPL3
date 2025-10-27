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
        Schema::create('student_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Personal Information
            $table->string('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            
            // Parent Information
            $table->string('nama_orang_tua')->nullable();
            $table->string('phone_orang_tua')->nullable();
            $table->text('alamat_orang_tua')->nullable();
            
            // Document Paths
            $table->string('ktp_orang_tua_path')->nullable();
            $table->string('ijazah_path')->nullable();
            $table->string('foto_siswa_path')->nullable();
            $table->string('bukti_pembayaran_path')->nullable();
            
            // Registration Status and Workflow
            $table->enum('registration_status', ['pending_documents', 'pending_payment', 'pending_approval', 'approved', 'rejected'])
                  ->default('pending_documents');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            
            // Foreign key for approver
            $table->foreign('approved_by')->references('id')->on('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_registrations');
    }
};
