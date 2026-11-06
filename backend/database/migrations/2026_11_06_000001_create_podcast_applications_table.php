<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Be a guest" applications from the landing page (section form + pop-up).
     */
    public function up(): void
    {
        Schema::create('podcast_applications', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20)->default('section'); // section | popup
            $table->string('full_name', 120);
            $table->string('company', 160);
            $table->string('role', 120)->nullable();
            $table->string('email', 200);
            $table->string('phone', 30);
            $table->string('industry', 80)->nullable();
            $table->string('linkedin', 300)->nullable();
            $table->text('story')->nullable();
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
        Schema::dropIfExists('podcast_applications');
    }
};
