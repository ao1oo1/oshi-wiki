<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table->text('timeline_setting')->nullable()->after('description');

            $table->text('building_layout')->nullable()->after('timeline_setting');
            $table->text('character_room_seat')->nullable()->after('building_layout');
            $table->text('hangout_places')->nullable()->after('character_room_seat');
            $table->text('restricted_secret_places')->nullable()->after('hangout_places');
            $table->text('cafeteria_store_menu')->nullable()->after('restricted_secret_places');

            $table->text('daily_schedule')->nullable()->after('cafeteria_store_menu');
            $table->text('school_dorm_rules')->nullable()->after('daily_schedule');
            $table->text('uniform_details')->nullable()->after('school_dorm_rules');
            $table->text('casual_holiday_rules')->nullable()->after('uniform_details');
            $table->text('duty_system')->nullable()->after('casual_holiday_rules');

            $table->text('class_grade_system')->nullable()->after('duty_system');
            $table->text('organizations_memberships')->nullable()->after('class_grade_system');
            $table->text('ranking_system')->nullable()->after('organizations_memberships');
            $table->text('adult_roles')->nullable()->after('ranking_system');

            $table->text('annual_events')->nullable()->after('adult_roles');
            $table->text('event_flow')->nullable()->after('annual_events');
            $table->text('story_season')->nullable()->after('event_flow');

            $table->text('school_location')->nullable()->after('story_season');
            $table->text('commute_environment')->nullable()->after('school_location');
            $table->text('nearby_shops')->nullable()->after('commute_environment');
            $table->text('climate_nature')->nullable()->after('nearby_shops');

            $table->text('sounds')->nullable()->after('climate_nature');
            $table->text('symbolic_motifs')->nullable()->after('sounds');
            $table->text('required_belongings')->nullable()->after('symbolic_motifs');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table->dropColumn([
                'timeline_setting',
                'building_layout',
                'character_room_seat',
                'hangout_places',
                'restricted_secret_places',
                'cafeteria_store_menu',
                'daily_schedule',
                'school_dorm_rules',
                'uniform_details',
                'casual_holiday_rules',
                'duty_system',
                'class_grade_system',
                'organizations_memberships',
                'ranking_system',
                'adult_roles',
                'annual_events',
                'event_flow',
                'story_season',
                'school_location',
                'commute_environment',
                'nearby_shops',
                'climate_nature',
                'sounds',
                'symbolic_motifs',
                'required_belongings',
            ]);
        });
    }
};
