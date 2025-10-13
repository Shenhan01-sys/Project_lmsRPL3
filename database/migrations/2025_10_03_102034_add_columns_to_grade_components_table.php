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
        Schema::table('grade_components', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->after('id');
            $table->string('name')->after('course_id'); // Nama komponen: UTS, UAS, Tugas 1, dll
            $table->text('description')->nullable()->after('name'); // Deskripsi komponen
            $table->decimal('weight', 5, 2)->after('description'); // Bobot dalam persen (0.00 - 100.00)
            $table->decimal('max_score', 8, 2)->nullable()->after('weight'); // Nilai maksimal default
            $table->boolean('is_active')->default(true)->after('max_score'); // Status aktif/nonaktif

            // Foreign key constraints
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            // Indexes
            $table->index(['course_id', 'is_active']);
            $table->index('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_components', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropIndex(['course_id', 'is_active']);
            $table->dropIndex(['course_id']);
            $table->dropColumn([
                'course_id',
                'name',
                'description', 
                'weight',
                'max_score',
                'is_active'
            ]);
        });
    }
};
