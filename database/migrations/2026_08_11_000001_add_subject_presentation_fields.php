<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('icon', 32)->nullable()->after('title');
            $table->text('icon_url')->nullable()->after('icon');
            $table->text('icon_data')->nullable()->after('icon_url');
            $table->string('color', 191)->nullable()->after('icon_data');
            $table->string('meetings')->nullable()->after('color');
            $table->string('weeks')->nullable()->after('meetings');
            $table->string('level')->nullable()->after('weeks');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['icon', 'icon_url', 'icon_data', 'color', 'meetings', 'weeks', 'level']);
        });
    }
};
