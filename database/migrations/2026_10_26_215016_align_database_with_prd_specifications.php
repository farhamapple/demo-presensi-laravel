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
        // 1. Tambah field unit_kerja, jabatan, avatar_url pada tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('unit_kerja')->nullable()->after('nip');
            $table->string('jabatan')->nullable()->after('unit_kerja');
            $table->string('avatar_url')->nullable()->after('phone');
        });

        // 2. Tambah titik pusat koordinat dan radius pada tabel office_locations
        Schema::table('office_locations', function (Blueprint $table) {
            $table->double('latitude')->nullable()->after('address');
            $table->double('longitude')->nullable()->after('latitude');
            $table->unsignedInteger('radius_meters')->default(100)->after('longitude');
        });

        // 3. Tambah distance_meters dan ip_address pada tabel attendances
        Schema::table('attendances', function (Blueprint $table) {
            $table->double('distance_meters')->nullable()->after('check_out_longitude');
            $table->string('ip_address', 45)->nullable()->after('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['unit_kerja', 'jabatan', 'avatar_url']);
        });

        Schema::table('office_locations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_meters']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['distance_meters', 'ip_address']);
        });
    }
};
