<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('character_work', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('work_id')->constrained('works')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->string('appearance_type', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['character_id','work_id']);
            $table->index(['character_id','is_primary']);
            $table->index(['work_id','sort_order']);
        });

        $now = now();
        DB::table('characters')->whereNotNull('work_id')->orderBy('id')
            ->select(['id','work_id'])->chunkById(500, function ($characters) use ($now): void {
                $rows = [];
                foreach ($characters as $character) {
                    $rows[] = [
                        'character_id'=>$character->id,
                        'work_id'=>$character->work_id,
                        'is_primary'=>true,
                        'appearance_type'=>null,
                        'sort_order'=>0,
                        'notes'=>null,
                        'created_at'=>$now,
                        'updated_at'=>$now,
                    ];
                }
                if ($rows !== []) DB::table('character_work')->insertOrIgnore($rows);
            });
    }
    public function down(): void {
        Schema::dropIfExists('character_work');
    }
};
