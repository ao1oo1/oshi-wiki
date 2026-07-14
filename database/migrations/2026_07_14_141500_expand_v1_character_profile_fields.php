<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('characters')) {
            return;
        }

        Schema::table('characters', function (Blueprint $table): void {
            $definitions = [
                'real_name' => fn () => $table->string('real_name')->nullable()->after('name_kana'),
                'aliases' => fn () => $table->text('aliases')->nullable()->after('real_name'),
                'name_english' => fn () => $table->string('name_english')->nullable()->after('aliases'),
                'gender' => fn () => $table->string('gender', 100)->nullable()->after('name_english'),
                'birthday' => fn () => $table->string('birthday')->nullable()->after('age'),
                'height' => fn () => $table->string('height')->nullable()->after('birthday'),
                'weight' => fn () => $table->string('weight')->nullable()->after('height'),
                'blood_type' => fn () => $table->string('blood_type', 100)->nullable()->after('weight'),
                'birthplace' => fn () => $table->string('birthplace')->nullable()->after('blood_type'),
                'species' => fn () => $table->string('species')->nullable()->after('birthplace'),
                'school_grade_class' => fn () => $table->string('school_grade_class')->nullable()->after('affiliation'),
                'occupation_position' => fn () => $table->string('occupation_position')->nullable()->after('school_grade_class'),
                'family_structure' => fn () => $table->text('family_structure')->nullable()->after('occupation_position'),
                'second_person' => fn () => $table->string('second_person')->nullable()->after('first_person'),
                'basic_tone' => fn () => $table->text('basic_tone')->nullable()->after('second_person'),
                'catchphrases' => fn () => $table->text('catchphrases')->nullable()->after('basic_tone'),
                'distinctive_speech' => fn () => $table->longText('distinctive_speech')->nullable()->after('catchphrases'),
                'tone_by_relationship' => fn () => $table->longText('tone_by_relationship')->nullable()->after('distinctive_speech'),
                'short_quote_examples' => fn () => $table->longText('short_quote_examples')->nullable()->after('tone_by_relationship'),
                'abilities' => fn () => $table->longText('abilities')->nullable()->after('appearance'),
                'story_activities' => fn () => $table->longText('story_activities')->nullable()->after('background'),
                'source_title' => fn () => $table->text('source_title')->nullable()->after('story_activities'),
                'source_url' => fn () => $table->text('source_url')->nullable()->after('source_title'),
                'source_type' => fn () => $table->string('source_type', 50)->nullable()->after('source_url'),
                'source_reliability' => fn () => $table->string('source_reliability', 20)->nullable()->after('source_type'),
                'source_checked_at' => fn () => $table->date('source_checked_at')->nullable()->after('source_reliability'),
                'spoiler_level' => fn () => $table->string('spoiler_level', 50)->default('none')->after('source_checked_at'),
            ];

            foreach ($definitions as $column => $definition) {
                if (! Schema::hasColumn('characters', $column)) {
                    $definition();
                }
            }
        });

        DB::table('characters')
            ->whereNull('school_grade_class')
            ->whereNotNull('grade_class')
            ->update(['school_grade_class' => DB::raw('grade_class')]);

        DB::table('characters')
            ->whereNull('basic_tone')
            ->whereNotNull('tone')
            ->update(['basic_tone' => DB::raw('tone')]);

        DB::table('characters')
            ->whereNull('short_quote_examples')
            ->whereNotNull('tone_examples')
            ->update(['short_quote_examples' => DB::raw('tone_examples')]);

        DB::statement('ALTER TABLE characters MODIFY appearance LONGTEXT NULL');
        DB::statement('ALTER TABLE characters MODIFY personality LONGTEXT NULL');
        DB::statement('ALTER TABLE characters MODIFY background LONGTEXT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('characters')) {
            return;
        }

        DB::table('characters')
            ->whereNull('grade_class')
            ->whereNotNull('school_grade_class')
            ->update(['grade_class' => DB::raw('school_grade_class')]);

        DB::table('characters')
            ->whereNull('tone')
            ->whereNotNull('basic_tone')
            ->update(['tone' => DB::raw('basic_tone')]);

        DB::table('characters')
            ->whereNull('tone_examples')
            ->whereNotNull('short_quote_examples')
            ->update(['tone_examples' => DB::raw('short_quote_examples')]);

        DB::statement('ALTER TABLE characters MODIFY appearance TEXT NULL');
        DB::statement('ALTER TABLE characters MODIFY personality TEXT NULL');
        DB::statement('ALTER TABLE characters MODIFY background TEXT NULL');

        Schema::table('characters', function (Blueprint $table): void {
            $columns = [
                'real_name', 'aliases', 'name_english', 'gender', 'birthday',
                'height', 'weight', 'blood_type', 'birthplace', 'species',
                'school_grade_class', 'occupation_position', 'family_structure',
                'second_person', 'basic_tone', 'catchphrases',
                'distinctive_speech', 'tone_by_relationship',
                'short_quote_examples', 'abilities', 'story_activities',
                'source_title', 'source_url', 'source_type',
                'source_reliability', 'source_checked_at', 'spoiler_level',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('characters', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
