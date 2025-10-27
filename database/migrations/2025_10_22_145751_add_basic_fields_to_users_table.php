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
            // Update role enum to include calon_siswa and parent
            $table->enum('role', ['student', 'instructor', 'admin', 'parent', 'calon_siswa'])->change();
            
            // Add missing fields for calon siswa registration
            $table->string('tanggal_lahir')->nullable(); // birth date string format
            $table->string('tempat_lahir')->nullable()->after('tanggal_lahir'); // birth place
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tempat_lahir'); // gender
            $table->string('nama_orang_tua')->nullable()->after('jenis_kelamin'); // parent name
            $table->string('phone_orang_tua')->nullable()->after('nama_orang_tua'); // parent phone
            $table->text('alamat_orang_tua')->nullable()->after('phone_orang_tua'); // parent address
            $table->timestamp('submitted_at')->nullable()->after('alamat_orang_tua'); // submission timestamp
            $table->timestamp('approved_at')->nullable()->after('submitted_at'); // approval timestamp
            $table->text('approval_notes')->nullable()->after('approved_at'); // approval/rejection notes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['student', 'instructor', 'admin'])->change();
            $table->dropColumn([
                'tanggal_lahir',
                'tempat_lahir', 
                'jenis_kelamin',
                'nama_orang_tua',
                'phone_orang_tua',
                'alamat_orang_tua',
                'submitted_at',
                'approved_at',
                'approval_notes'
            ]);
        });
    }
};
