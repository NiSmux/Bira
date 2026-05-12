<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_types', function (Blueprint $table) {
            $table->string('icon', 10)->nullable()->after('name');
            $table->string('color', 20)->nullable()->after('icon');
            $table->text('description')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('item_types', function (Blueprint $table) {
            $table->dropColumn(['icon', 'color', 'description']);
        });
    }
};
