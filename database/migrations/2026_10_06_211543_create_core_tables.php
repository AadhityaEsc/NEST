<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Universities Table
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); //university name
            $table->string('email_domain'); // uni email
            $table->timestamps();
        });

        // 2. Modify Users  (assuming default users table exists, we add to it)
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->unique();
            $table->enum('role', ['student', 'landlord'])->default('student');
            $table->foreignId('university_id')->nullable()->constrained('universities');
            // Track when the user last opened the app for the 3-day expiry logic
            $table->timestamp('last_app_opened_at')->useCurrent();
        });

        // 3. Listings 
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['room_available', 'roommate_wanted']);
            $table->string('title');
            $table->text('description');
            $table->decimal('rent_amount', 8, 2);
            // Spatial coordinateslive Map Bounding Box
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            // Auto-withdrawal logic fields
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_interaction_at')->useCurrent();
            $table->boolean('has_wishlist_extension')->default(false);
            $table->timestamps();
        });

        // 4. Wishlists Table (to trigger the 1-time 3-day extension)
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('universities');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'role', 'university_id', 'last_app_opened_at']);
        });
    }
};
