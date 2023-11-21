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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->string('password')->bcrypt();
            $table->enum('type', ['person', 'business']);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->integer('score')->nullable();
            $table->integer('total_score')->nullable();
            $table->integer('total_operations')->nullable();
            $table->string('avatar')->nullable();
            $table->string('external_id')->nullable();
            $table->string('external_auth')->nullable();
            $table->boolean('is_verified')->default(false); 
            $table->rememberToken()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
    public function runAfter()
    {
        return ['CreatePermissionTables'];
    }
};
