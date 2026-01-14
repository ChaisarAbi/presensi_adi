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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'class' atau 'student'
            $table->string('title');
            $table->json('parameters'); // JSON untuk menyimpan filter
            $table->integer('record_count')->default(0);
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
