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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // polymorphic relation (tasks, projects, etc.)
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('filename'); // original filename
            $table->string('stored_filename'); // unique filename in storage
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->string('disk')->default('public'); // storage disk
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
