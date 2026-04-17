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
        Schema::create('sprint_work_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sprint_id')->constrained('releases')->onDelete('cascade');
            $table->foreignId('work_item_id')->constrained('work_items')->onDelete('cascade');
            $table->foreignId('status_id')->nullable()->constrained('workflow_statuses');
            $table->timestamps();
            
            $table->unique(['sprint_id', 'work_item_id']);
        });

        // Migrate existing data
        $items = DB::table('work_items')->whereNotNull('release_id')->get();
        foreach ($items as $item) {
            DB::table('sprint_work_items')->insert([
                'sprint_id'    => $item->release_id,
                'work_item_id' => $item->id,
                'status_id'    => $item->status_id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprint_work_items');
    }
};
