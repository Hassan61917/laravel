<?php

use Src\Main\Database\Migrations\Migration;
use Src\Main\Database\Schema\Blueprint\Blueprint;
use Src\Main\Facade\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedBigInteger('attempts');
            $table->unsignedBigInteger('reserved_at')->nullable();
            $table->unsignedBigInteger('available_at');
            $table->unsignedBigInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
