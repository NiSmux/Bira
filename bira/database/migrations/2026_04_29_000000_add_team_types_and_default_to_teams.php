<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_types', function (Blueprint $table) {
            $table->unsignedInteger('team_id')->nullable()->after('order_index');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('default_item_type_id')->nullable()->after('description');
            $table->foreign('default_item_type_id')->references('id')->on('item_types')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['default_item_type_id']);
            $table->dropColumn('default_item_type_id');
        });

        Schema::table('item_types', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};
