<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranting_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ranting_id')->constrained('rantings')->cascadeOnDelete();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranting_members');
    }
};
