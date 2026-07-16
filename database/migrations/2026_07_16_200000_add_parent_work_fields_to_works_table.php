<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table
                ->foreignId('parent_work_id')
                ->nullable()
                ->after('id')
                ->constrained('works')
                ->restrictOnDelete();

            $table
                ->unsignedInteger('child_sort_order')
                ->default(0)
                ->after('parent_work_id');

            $table->index(
                ['parent_work_id', 'child_sort_order'],
                'works_parent_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table->dropIndex('works_parent_sort_index');
            $table->dropForeign(['parent_work_id']);
            $table->dropColumn([
                'parent_work_id',
                'child_sort_order',
            ]);
        });
    }
};
