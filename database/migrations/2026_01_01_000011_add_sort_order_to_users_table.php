<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'sort_order')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unsignedInteger('sort_order')->default(0)->after('password');
            });
        }

        $users = DB::table('users')->orderBy('id')->get(['id']);
        foreach ($users as $index => $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'sort_order')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('sort_order');
            });
        }
    }
};
