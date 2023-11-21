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
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('price');
            $table->integer('stock');
            $table->text('description');
            $table->json('tags')->nullable();
            $table->datetime('time_start')->nullable();
            $table->datetime('time_end')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->string('photo_url')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('packs');
    }
};
