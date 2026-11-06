<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Suggest a guest" submissions from the landing page.
     */
    public function up(): void
    {
        Schema::create('guest_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('founder_name', 120);
            $table->string('linkedin', 300);
            $table->string('company_name', 160);
            $table->string('founder_role', 120)->nullable();
            $table->string('industry', 80)->nullable();
            $table->string('location', 120)->nullable();
            $table->string('company_website', 300)->nullable();
            $table->text('why');
            $table->string('topics', 400)->nullable();
            $table->text('questions')->nullable();
            $table->string('connection', 80)->nullable();
            $table->string('suggester_name', 120)->nullable();
            $table->string('suggester_email', 200)->nullable();
            $table->string('page_url', 300)->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_suggestions');
    }
};
