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
        Schema::create('user_notification_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('notification_channel_id')->constrained()->onDelete('cascade');
            $table->boolean('is_subscribed')->default(true);
            $table->timestamps();

            // Ensure a user can only have one subscription per channel
            $table->unique(['user_id', 'notification_channel_id'], 'user_notif_sub_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_subscriptions');
    }
};
