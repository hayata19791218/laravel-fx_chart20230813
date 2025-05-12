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
        Schema::table('values', function (Blueprint $table) {
            $table->string('high_value_memo')->nullable()->after('high_value');
            $table->string('row_value_memo')->nullable()->after('row_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('values', function (Blueprint $table) {
            $table->dropColumn('high_value_memo');
            $table->dropColumn('row_value_memo');
        });
    }
};
