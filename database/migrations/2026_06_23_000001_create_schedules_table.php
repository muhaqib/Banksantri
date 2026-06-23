<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('teacher_name');
            $table->enum('recipient_type', ['personal', 'group']);
            $table->string('target_id');
            $table->string('day_of_week');
            $table->time('send_time');
            $table->text('message_content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'day_of_week', 'send_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
