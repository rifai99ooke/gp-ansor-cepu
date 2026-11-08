<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('username')->nullable()->unique()->after('name');
            });
        }

        $users = DB::table('users')->whereNull('username')->orWhere('username', '')->get();
        foreach ($users as $index => $user) {
            $defaultUsername = $index === 0
                ? 'admin'
                : Str::slug((string) ($user->name ?: 'user')).'-'.$user->id;

            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $defaultUsername]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('username');
            });
        }
    }
};
