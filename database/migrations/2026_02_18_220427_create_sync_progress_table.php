<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sync_progress', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0);
            $table->integer('successful')->default(0);
            $table->integer('failed')->default(0);
            $table->boolean('is_complete')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('session_id');
            $table->index(['is_complete', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sync_progress');
    }
};