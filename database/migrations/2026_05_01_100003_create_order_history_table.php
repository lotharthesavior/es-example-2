<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_history', function (Blueprint $table): void {
            $table->id();
            $table->string('order_uuid');
            $table->string('event_type');
            $table->json('event_data');
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index('order_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_history');
    }
};
