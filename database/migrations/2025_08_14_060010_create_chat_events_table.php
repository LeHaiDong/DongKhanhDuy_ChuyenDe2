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
        Schema::create('chat_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')->nullable()->constrained('chat_messages')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->index();
            $table->string('type')->index(); // intent_detected, keyword_reply, nlp_reply, handoff_requested, lead_collected, order_assisted, product_recommendation
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_events');
    }
};



