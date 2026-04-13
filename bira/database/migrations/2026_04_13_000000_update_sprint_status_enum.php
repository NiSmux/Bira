<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First expand the enum to include both old and new values so data updates don't truncate
        DB::statement("ALTER TABLE `releases` MODIFY COLUMN `status` ENUM('new','planned','active','in_progress','completed','to_be_released','delivered') NOT NULL DEFAULT 'planned'");

        // Migrate existing data to new status values
        DB::table('releases')->where('status', 'active')->update(['status' => 'in_progress']);
        DB::table('releases')->where('status', 'completed')->update(['status' => 'delivered']);
        // 'planned' stays 'planned'

        // Now narrow the enum to only new values and set new default
        DB::statement("ALTER TABLE `releases` MODIFY COLUMN `status` ENUM('new','planned','in_progress','to_be_released','delivered') NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        DB::table('releases')->where('status', 'new')->update(['status' => 'planned']);
        DB::table('releases')->where('status', 'in_progress')->update(['status' => 'active']);
        DB::table('releases')->where('status', 'to_be_released')->update(['status' => 'completed']);
        DB::table('releases')->where('status', 'delivered')->update(['status' => 'completed']);

        DB::statement("ALTER TABLE `releases` MODIFY COLUMN `status` ENUM('planned','active','completed') NOT NULL DEFAULT 'planned'");
    }
};
