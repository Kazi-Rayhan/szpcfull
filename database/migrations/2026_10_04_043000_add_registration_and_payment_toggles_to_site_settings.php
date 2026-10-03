<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('registration_enabled')->default(true)->after('analytics_measurement_id');
            $table->boolean('payment_enabled')->default(true)->after('registration_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'registration_enabled',
                'payment_enabled',
            ]);
        });
    }
};
