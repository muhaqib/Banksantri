<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waha_message_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->string('teacher_name')->nullable();
            $table->string('target_id');
            $table->string('session')->nullable();
            $table->text('message_content');
            $table->enum('status', ['success', 'failed']);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'sent_at']);
            $table->index(['schedule_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waha_message_logs');
    }
};
