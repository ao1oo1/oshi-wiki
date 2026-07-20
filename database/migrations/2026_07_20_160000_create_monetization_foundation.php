<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            // 既存CSV・世界設定カラムの並び順を維持するため、
            // v5収益化カラムは既存作品項目の末尾へ追加する。
            $table->json('media_types')
                ->nullable()
                ->after('required_belongings');
            $table->boolean('monetization_enabled')
                ->default(false)
                ->after('media_types');
            $table->string('monetization_inheritance', 30)
                ->default('self_then_parent')
                ->after('monetization_enabled');
            $table->string('isbn', 32)
                ->nullable()
                ->after('monetization_inheritance');
            $table->string('official_store_url', 2048)
                ->nullable()
                ->after('isbn');
        });

        Schema::create('monetization_services', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('category', 30);
            $table->string('logo_path', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('default_button_label', 100)->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('affiliate_programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->constrained('monetization_services')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('provider_name', 150)->nullable();
            $table->text('url_template');
            $table->string('affiliate_identifier', 255)->nullable();
            $table->json('additional_parameters')->nullable();
            $table->json('allowed_hosts');
            $table->string('code_validation_pattern', 500)->nullable();
            $table->string('code_example', 255)->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_affiliate')->default(true);
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_monetization_links', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_key')->unique();
            $table->foreignId('work_id')->constrained('works')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('monetization_services')->restrictOnDelete();
            $table->foreignId('affiliate_program_id')->constrained('affiliate_programs')->restrictOnDelete();
            $table->string('product_code', 255);
            $table->string('product_type', 50)->default('series');
            $table->string('title', 255)->nullable();
            $table->string('button_label', 100)->nullable();
            $table->string('campaign_code', 255)->nullable();
            $table->string('availability_status', 20)->default('unknown');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('last_verified_at')->nullable();
            $table->string('verification_method', 20)->nullable();
            $table->text('verification_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['work_id', 'service_id', 'affiliate_program_id', 'product_code', 'product_type'],
                'work_monetization_links_unique_product'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_monetization_links');
        Schema::dropIfExists('affiliate_programs');
        Schema::dropIfExists('monetization_services');

        Schema::table('works', function (Blueprint $table): void {
            $table->dropColumn([
                'media_types',
                'monetization_enabled',
                'monetization_inheritance',
                'isbn',
                'official_store_url',
            ]);
        });
    }
};
