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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onUpdate('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onUpdate('cascade');
            $table->text('message');
            $table->boolean('sent')->default(false); 
            $table->boolean('delivered')->default(false); 
            $table->boolean('read')->default(false); 
            $table->timestamp('sent_at')->nullable(); 
            $table->timestamp('delivered_at')->nullable(); 
            $table->timestamp('read_at')->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
