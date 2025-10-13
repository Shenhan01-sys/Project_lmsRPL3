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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('grade_component_id')->constrained('grade_components')->onDelete('cascade');
            $table->decimal('score', 8, 2); // Nilai yang diperoleh
            $table->decimal('max_score', 8, 2); // Nilai maksimal saat input
            $table->text('notes')->nullable(); // Catatan guru
            $table->timestamp('graded_at')->nullable(); // Kapan dinilai
            $table->foreignId('graded_by')->nullable()->constrained('users'); // Siapa yang menilai
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['student_id', 'grade_component_id']);
            $table->index(['grade_component_id']);
            $table->index(['graded_by']);
            
            // Unique constraint: 1 siswa hanya bisa punya 1 nilai per komponen
            $table->unique(['student_id', 'grade_component_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
