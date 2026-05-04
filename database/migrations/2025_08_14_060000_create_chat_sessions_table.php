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
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->index();
            $table->json('state')->nullable();
            $table->string('last_intent')->nullable()->index();
            $table->decimal('last_confidence', 5, 4)->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->unsignedInteger('total_messages')->default(0);
            $table->unsignedInteger('total_user_messages')->default(0);
            $table->unsignedInteger('total_ai_messages')->default(0);
            $table->boolean('handoff_requested')->default(false)->index();
            $table->boolean('contact_collected')->default(false)->index();
            $table->timestamps();

            $table->index(['session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};



