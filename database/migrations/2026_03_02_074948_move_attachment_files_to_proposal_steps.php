<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add attachment_files to proposal_steps
        Schema::table('proposal_steps', function (Blueprint $table) {
            $table->json('attachment_files')->nullable()->after('status');
        });

        // 2. Move data from proposals to the FIRST step of each proposal
        $proposals = DB::table('proposals')->whereNotNull('attachment_files')->get();
        foreach ($proposals as $proposal) {
            // Find first step
            $firstStep = DB::table('proposal_steps')
                ->where('proposal_id', $proposal->id)
                ->orderBy('step_level', 'asc')
                ->first();

            if ($firstStep) {
                // Update the first step with the files
                DB::table('proposal_steps')
                    ->where('id', $firstStep->id)
                    ->update(['attachment_files' => $proposal->attachment_files]);
            }
        }

        // 3. Drop attachment_files from proposals
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('attachment_files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add column back to proposals
        Schema::table('proposals', function (Blueprint $table) {
            $table->json('attachment_files')->nullable()->after('rejection_reason');
        });

        // 2. Recover data from first steps back to proposals
        $steps = DB::table('proposal_steps')
            ->whereNotNull('attachment_files')
            ->where('step_level', 1)
            ->get();
            
        foreach ($steps as $step) {
            DB::table('proposals')
                ->where('id', $step->proposal_id)
                ->update(['attachment_files' => $step->attachment_files]);
        }

        // 3. Drop column from proposal_steps
        Schema::table('proposal_steps', function (Blueprint $table) {
            $table->dropColumn('attachment_files');
        });
    }
};
