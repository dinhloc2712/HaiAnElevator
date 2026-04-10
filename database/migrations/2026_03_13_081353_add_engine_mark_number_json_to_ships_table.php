<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - Converts engine_mark and engine_number from varchar to json
     * - Adds sub_engine_mark and sub_engine_number as json columns
     */
    public function up(): void
    {
        // Step 1: Add new json columns alongside old ones
        Schema::table('ships', function (Blueprint $table) {
            $table->json('engine_mark_json')->nullable()->after('engine_number');
            $table->json('engine_number_json')->nullable()->after('engine_mark_json');
            $table->json('sub_engine_mark')->nullable()->after('engine_number_json');
            $table->json('sub_engine_number')->nullable()->after('sub_engine_mark');
        });

        // Step 2: Migrate existing data - wrap old varchar values into a single-element array
        DB::statement('UPDATE ships SET engine_mark_json = JSON_ARRAY(engine_mark) WHERE engine_mark IS NOT NULL AND engine_mark != ""');
        DB::statement('UPDATE ships SET engine_number_json = JSON_ARRAY(engine_number) WHERE engine_number IS NOT NULL AND engine_number != ""');

        // Step 3: Drop old varchar columns and rename json columns
        Schema::table('ships', function (Blueprint $table) {
            $table->dropColumn(['engine_mark', 'engine_number']);
        });

        Schema::table('ships', function (Blueprint $table) {
            $table->renameColumn('engine_mark_json', 'engine_mark');
            $table->renameColumn('engine_number_json', 'engine_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->dropColumn(['sub_engine_mark', 'sub_engine_number']);
        });

        // Reverse: add back varchar columns
        Schema::table('ships', function (Blueprint $table) {
            $table->string('engine_mark_old')->nullable()->after('engine_number');
            $table->string('engine_number_old')->nullable()->after('engine_mark_old');
        });

        // Migrate data back (take first element of json array)
        DB::statement('UPDATE ships SET engine_mark_old = JSON_UNQUOTE(JSON_EXTRACT(engine_mark, "$[0]")) WHERE engine_mark IS NOT NULL');
        DB::statement('UPDATE ships SET engine_number_old = JSON_UNQUOTE(JSON_EXTRACT(engine_number, "$[0]")) WHERE engine_number IS NOT NULL');

        Schema::table('ships', function (Blueprint $table) {
            $table->dropColumn(['engine_mark', 'engine_number']);
        });

        Schema::table('ships', function (Blueprint $table) {
            $table->renameColumn('engine_mark_old', 'engine_mark');
            $table->renameColumn('engine_number_old', 'engine_number');
        });
    }
};
