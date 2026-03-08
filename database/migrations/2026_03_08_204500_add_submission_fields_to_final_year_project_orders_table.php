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
        Schema::table('final_year_project_orders', function (Blueprint $table) {
            $table->string('system_name', 180)->nullable()->after('project_title');
            $table->string('system_repo_url', 2048)->nullable()->after('system_name');
            $table->string('source_zip_path', 255)->nullable()->after('system_repo_url');
            $table->string('source_zip_original_name', 255)->nullable()->after('source_zip_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_year_project_orders', function (Blueprint $table) {
            $table->dropColumn([
                'system_name',
                'system_repo_url',
                'source_zip_path',
                'source_zip_original_name',
            ]);
        });
    }
};
