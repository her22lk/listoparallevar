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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pack_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('calification_gived_id')->nullable();
            $table->unsignedBigInteger('feedback_received_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('code');
            $table->enum('status', ['reserved', 'delivered', 'cancelled'])->default('reserved');
            $table->string('amount');
            $table->timestamps();

            $table->foreign('pack_id')
            ->references('id')
            ->on('packs')
            ->onDelete('cascade');

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

            $table->foreign('seller_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

            $table->foreign('calification_gived_id')
            ->references('id')
            ->on('califications')
            ->onDelete('cascade');

            $table->foreign('feedback_received_id')
            ->references('id')
            ->on('califications')
            ->onDelete('cascade');

            $table->foreign('payment_id')
            ->references('id')
            ->on('payments')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }

};
