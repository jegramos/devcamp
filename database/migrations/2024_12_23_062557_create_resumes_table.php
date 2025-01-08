<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->json('titles');
            $table->json('experiences');
            $table->json('socials');
            $table->json('tech_expertise')->nullable();
            $table->json('projects')->nullable();
            $table->json('work_timeline')->nullable();
            $table->json('services')->nullable();
            $table->json('contact')->nullable();
            $table->foreignId('theme_id')
                ->constrained('resume_themes')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
