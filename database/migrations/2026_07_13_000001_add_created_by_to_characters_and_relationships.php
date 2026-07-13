<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('characters')
            && ! Schema::hasColumn('characters', 'created_by')
        ) {
            Schema::table('characters', function (Blueprint $table): void {
                $column = $table
                    ->unsignedBigInteger('created_by')
                    ->nullable()
                    ->index();

                if (Schema::hasColumn('characters', 'reviewed_by')) {
                    $column->after('reviewed_by');
                }
            });
        }

        if (
            Schema::hasTable('character_relationships')
            && ! Schema::hasColumn(
                'character_relationships',
                'created_by'
            )
        ) {
            Schema::table(
                'character_relationships',
                function (Blueprint $table): void {
                    $column = $table
                        ->unsignedBigInteger('created_by')
                        ->nullable()
                        ->index();

                    if (
                        Schema::hasColumn(
                            'character_relationships',
                            'reviewed_by'
                        )
                    ) {
                        $column->after('reviewed_by');
                    }
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('character_relationships')
            && Schema::hasColumn(
                'character_relationships',
                'created_by'
            )
        ) {
            Schema::table(
                'character_relationships',
                function (Blueprint $table): void {
                    $table->dropColumn('created_by');
                }
            );
        }

        if (
            Schema::hasTable('characters')
            && Schema::hasColumn('characters', 'created_by')
        ) {
            Schema::table(
                'characters',
                function (Blueprint $table): void {
                    $table->dropColumn('created_by');
                }
            );
        }
    }
};
