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
        // Add new columns to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->text('company_description')->nullable()->after('password');
            $table->json('alerts_setting')->nullable()->after('company_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new columns from the users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('company_description');
            $table->dropColumn('alerts_setting');
        });
    }
};
