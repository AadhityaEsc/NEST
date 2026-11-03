
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add location_label only if it does not exist.
        if (!Schema::hasColumn('listings', 'location_label')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->string('location_label', 120)->nullable();
            });
        }

        // Create listing_images only if it does not exist.
        if (!Schema::hasTable('listing_images')) {
            Schema::create('listing_images', function (Blueprint $table) {
                $table->id();

                $table->foreignId('listing_id')
                    ->constrained('listings')
                    ->cascadeOnDelete();

                $table->string('disk', 32)->default('public');
                $table->string('path');
                $table->unsignedSmallInteger('sort_order')
                    ->default(0);

                $table->timestamps();

                $table->index(['listing_id', 'sort_order']);
            });
        }

        // Verify that the existing table has all required columns.
        $requiredColumns = [
            'id',
            'listing_id',
            'disk',
            'path',
            'sort_order',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredColumns as $column) {
            if (!Schema::hasColumn('listing_images', $column)) {
                throw new RuntimeException(
                    "Missing required listing_images column: {$column}"
                );
            }
        }
    }

    public function down(): void
    {
        // Intentionally preserve existing database structures.
        // They were already present before this migration completed.
        // Do not delete the existing listing_images table.
    }
};
