<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->after('id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('branch_id')->after('role_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('pin_code')->after('password')->nullable();
            $table->boolean('is_active')->after('pin_code')->default(true);
            $table->dateTime('last_login_at')->after('is_active')->nullable();
            $table->string('phone')->after('last_login_at')->nullable();
            $table->string('status')->after('phone')->default('active');
            $table->string('avatar')->after('status')->nullable();

            $table->index('is_active');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['pin_code', 'is_active', 'last_login_at', 'phone', 'status', 'avatar']);
        });
    }
};
