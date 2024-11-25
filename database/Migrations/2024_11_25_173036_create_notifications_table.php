<?php

use Src\Main\Database\Migrations\Migration;
use Src\Main\Database\Schema\Blueprint\Blueprint;
use Src\Main\Facade\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
