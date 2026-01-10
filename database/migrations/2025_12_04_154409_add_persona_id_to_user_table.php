<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->foreignId('persona_id')->nullable()->unique()->after('id')->constrained('persona')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn('persona_id');
        });
    }
};
