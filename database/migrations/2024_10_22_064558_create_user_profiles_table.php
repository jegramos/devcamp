<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            // Primary Information
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('given_name');
            $table->string('family_name');
            $table->string('mobile_number')->nullable();

            $genders = array_column(Gender::cases(), 'value');
            $table->enum('gender', $genders)->nullable();

            $table->date('birthday')->nullable();
            $table->string('profile_picture_path')->nullable();

            // Address
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('address_line_1')->nullable(); // Building number, Building name
            $table->string('address_line_2')->nullable(); // Street, Road name, Barangay
            $table->string('address_line_3')->nullable(); // Additional address info
            $table->string('city_municipality')->nullable();
            $table->string('province_state_county')->nullable();
            $table->string('postal_code')->nullable(); // or Zip Code

            $table->timestamps();
            $table->softdeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
