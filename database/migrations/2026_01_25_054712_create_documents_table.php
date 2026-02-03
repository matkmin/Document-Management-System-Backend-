<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');

            $table->unsignedBigInteger('document_category_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('uploaded_by');

            $table->foreign('document_category_id')->references('id')->on('document_categories');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('uploaded_by')->references('id')->on('users');

            $table->enum('access_level', ['public', 'department', 'private']);
            $table->unsignedBigInteger('download_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
