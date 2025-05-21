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
        Schema::create('tool_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->string('nim');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->date('borrow_date');
            $table->date('expiration_date');
            $table->text('purpose');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_requests');
    }
};
