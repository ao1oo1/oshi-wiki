<?php

use App\Http\Controllers\Admin\WorkController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = request()->user();

    if ($user?->canAccessAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('writer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'writer.user'])->prefix('writer')->name('writer.')->group(function () {
    Route::get('dashboard', \App\Http\Controllers\Writer\DashboardController::class)
        ->name('dashboard');

    Route::get('guide', \App\Http\Controllers\Writer\GuideController::class)
        ->name('guide');

    Route::get(
        'original-characters/{original_character}/image',
        [\App\Http\Controllers\Writer\OriginalCharacterController::class, 'image']
    )->name('original-characters.image');

    Route::resource('original-characters', \App\Http\Controllers\Writer\OriginalCharacterController::class);
    Route::post('original-character-relationships/{originalCharacterRelationship}/duplicate', [\App\Http\Controllers\Writer\OriginalCharacterRelationshipController::class, 'duplicate'])
        ->name('original-character-relationships.duplicate');

    Route::resource('original-character-relationships', \App\Http\Controllers\Writer\OriginalCharacterRelationshipController::class);
    Route::post('prompts/preview', [\App\Http\Controllers\Writer\SavedPromptController::class, 'preview'])
        ->name('prompts.preview');

    Route::post('prompts/{prompt}/duplicate', [\App\Http\Controllers\Writer\SavedPromptController::class, 'duplicate'])
        ->name('prompts.duplicate');

    Route::post('prompts/{prompt}/record-usage', [\App\Http\Controllers\Writer\SavedPromptController::class, 'recordUsage'])
        ->name('prompts.record-usage');

    Route::post(
        'story-analyses/generate-prompt',
        [\App\Http\Controllers\Writer\WriterStoryAnalysisController::class, 'generatePrompt']
    )->name('story-analyses.generate-prompt');

    Route::patch(
        'story-analyses/{story_analysis}/result',
        [\App\Http\Controllers\Writer\WriterStoryAnalysisController::class, 'updateResult']
    )->name('story-analyses.result.update');

    Route::delete(
        'story-analyses/{story_analysis}/result',
        [\App\Http\Controllers\Writer\WriterStoryAnalysisController::class, 'destroyResult']
    )->name('story-analyses.result.destroy');

    Route::resource(
        'story-analyses',
        \App\Http\Controllers\Writer\WriterStoryAnalysisController::class
    );

    Route::resource(
        'stories',
        \App\Http\Controllers\Writer\WriterStoryController::class
    );

    Route::post(
        'prompts/{prompt}/ai-results',
        [\App\Http\Controllers\Writer\SavedPromptAiResultController::class, 'store']
    )->name('prompts.ai-results.store');

    Route::delete(
        'prompts/{prompt}/ai-results/{result}',
        [\App\Http\Controllers\Writer\SavedPromptAiResultController::class, 'destroy']
    )->name('prompts.ai-results.destroy');

    Route::resource('prompts', \App\Http\Controllers\Writer\SavedPromptController::class);
});

Route::middleware(['auth', 'admin.user', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::post('works/bulk-action', \App\Http\Controllers\Admin\WorkBulkActionController::class)
        ->name('works.bulk-action');

    
    Route::get('works/export/csv', \App\Http\Controllers\Admin\WorkCsvExportController::class)
        ->name('works.csv-export');

    Route::get('staff-guide', \App\Http\Controllers\Admin\StaffGuideController::class)
        ->name('staff-guide');

    Route::get('trash', [\App\Http\Controllers\Admin\TrashController::class, 'index'])
        ->name('trash.index');

    Route::post('trash/bulk-destroy', [\App\Http\Controllers\Admin\TrashController::class, 'bulkDestroy'])
        ->name('trash.bulk-destroy');

    Route::post('trash/destroy-all', [\App\Http\Controllers\Admin\TrashController::class, 'destroyAll'])
        ->name('trash.destroy-all');

    Route::post(
        'trash/{type}/{id}/restore',
        [\App\Http\Controllers\Admin\TrashController::class, 'restore']
    )->name('trash.restore');

    Route::delete('trash/{type}/{id}', [\App\Http\Controllers\Admin\TrashController::class, 'destroy'])
        ->name('trash.destroy');

    Route::get('works/import', [\App\Http\Controllers\Admin\WorkTextImportController::class, 'create'])
        ->name('works.import.create');

    Route::post('works/import', [\App\Http\Controllers\Admin\WorkTextImportController::class, 'store'])
        ->name('works.import.store');

    Route::get('works/import/csv', [\App\Http\Controllers\Admin\WorkCsvImportController::class, 'create'])
        ->name('works.csv-import.create');

    Route::post('works/import/csv', [\App\Http\Controllers\Admin\WorkCsvImportController::class, 'store'])
        ->name('works.csv-import.store');

    Route::get('works/import/csv/sample', [\App\Http\Controllers\Admin\WorkCsvImportController::class, 'sample'])
        ->name('works.csv-import.sample');

    Route::get('tags/export/csv', \App\Http\Controllers\Admin\TagCsvExportController::class)
        ->name('tags.csv-export');

    Route::get('tags/import', [\App\Http\Controllers\Admin\TagTextImportController::class, 'create'])
        ->name('tags.import.create');

    Route::post('tags/import', [\App\Http\Controllers\Admin\TagTextImportController::class, 'store'])
        ->name('tags.import.store');

    Route::get('tags/import/csv', [\App\Http\Controllers\Admin\TagCsvImportController::class, 'create'])
        ->name('tags.csv-import.create');

    Route::post('tags/import/csv', [\App\Http\Controllers\Admin\TagCsvImportController::class, 'store'])
        ->name('tags.csv-import.store');

    Route::get('tags/import/csv/sample', [\App\Http\Controllers\Admin\TagCsvImportController::class, 'sample'])
        ->name('tags.csv-import.sample');

    
    
    Route::get('contributor-applications', [\App\Http\Controllers\Admin\ContributorApplicationController::class, 'index'])
        ->name('contributor-applications.index');

    Route::post('contributor-applications/{contributorApplication}/activate', [\App\Http\Controllers\Admin\ContributorApplicationController::class, 'activate'])
        ->name('contributor-applications.activate');

    Route::post('contributor-applications/{contributorApplication}/reject', [\App\Http\Controllers\Admin\ContributorApplicationController::class, 'reject'])
        ->name('contributor-applications.reject');

    Route::delete('contributor-applications/{contributorApplication}', [\App\Http\Controllers\Admin\ContributorApplicationController::class, 'destroy'])
        ->name('contributor-applications.destroy');

    Route::get('review-requests', [\App\Http\Controllers\Admin\ReviewRequestController::class, 'index'])
        ->name('review-requests.index');

    Route::post('review-requests/approve', [\App\Http\Controllers\Admin\ReviewRequestController::class, 'approve'])
        ->name('review-requests.approve');

    Route::post('review-requests/reject', [\App\Http\Controllers\Admin\ReviewRequestController::class, 'reject'])
        ->name('review-requests.reject');

    Route::get(
        'works/{work}/story-sections/csv',
        [\App\Http\Controllers\Admin\WorkStorySectionCsvController::class, 'create']
    )->name('works.story-sections.csv.create');

    Route::post(
        'works/{work}/story-sections/csv',
        [\App\Http\Controllers\Admin\WorkStorySectionCsvController::class, 'import']
    )->name('works.story-sections.csv.import');

    Route::get(
        'works/{work}/story-sections/csv/export/{type}',
        [\App\Http\Controllers\Admin\WorkStorySectionCsvController::class, 'export']
    )->name('works.story-sections.csv.export');

    Route::get(
        'story-sections/csv/sample/{type}',
        [\App\Http\Controllers\Admin\WorkStorySectionCsvController::class, 'sample']
    )->name('works.story-sections.csv.sample');

    Route::get(
        'works/{work}/story-sections/text-import',
        [\App\Http\Controllers\Admin\WorkStorySectionTextImportController::class, 'create']
    )->name('works.story-sections.text-import.create');

    Route::post(
        'works/{work}/story-sections/text-import',
        [\App\Http\Controllers\Admin\WorkStorySectionTextImportController::class, 'store']
    )->name('works.story-sections.text-import.store');

    Route::post(
        'works/{work}/story-sections/bulk-action',
        \App\Http\Controllers\Admin\WorkStorySectionBulkActionController::class
    )->name('works.story-sections.bulk-action');

    Route::get(
        'works/{work}/story-sections/{storySection}/events/csv',
        [
            \App\Http\Controllers\Admin\WorkStorySectionEventCsvController::class,
            'create',
        ]
    )->name('works.story-sections.events.csv.create');

    Route::post(
        'works/{work}/story-sections/{storySection}/events/csv',
        [
            \App\Http\Controllers\Admin\WorkStorySectionEventCsvController::class,
            'store',
        ]
    )->name('works.story-sections.events.csv.store');

    Route::get(
        'works/{work}/story-sections/{storySection}/events/csv/export',
        [
            \App\Http\Controllers\Admin\WorkStorySectionEventCsvController::class,
            'export',
        ]
    )->name('works.story-sections.events.csv.export');

    Route::get(
        'story-section-events/csv/sample',
        [
            \App\Http\Controllers\Admin\WorkStorySectionEventCsvController::class,
            'sample',
        ]
    )->name('story-section-events.csv.sample');

    Route::resource(
        'works.story-sections',
        \App\Http\Controllers\Admin\WorkStorySectionController::class
    )->parameters([
        'story-sections' => 'storySection',
    ]);

    Route::resource('works', WorkController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'admin.user', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('characters/export/csv', \App\Http\Controllers\Admin\CharacterCsvExportController::class)
        ->name('characters.csv-export');
    
    Route::get('characters/import', [\App\Http\Controllers\Admin\CharacterTextImportController::class, 'create'])
        ->name('characters.import.create');

    Route::post('characters/import', [\App\Http\Controllers\Admin\CharacterTextImportController::class, 'store'])
        ->name('characters.import.store');

    
    Route::get('characters/import/csv', [\App\Http\Controllers\Admin\CharacterCsvImportController::class, 'create'])
        ->name('characters.csv-import.create');

    Route::post('characters/import/csv', [\App\Http\Controllers\Admin\CharacterCsvImportController::class, 'store'])
        ->name('characters.csv-import.store');

    Route::get('characters/import/csv/sample', [\App\Http\Controllers\Admin\CharacterCsvImportController::class, 'sample'])
        ->name('characters.csv-import.sample');

    
    Route::post('characters/bulk-action', \App\Http\Controllers\Admin\CharacterBulkActionController::class)
        ->name('characters.bulk-action');

    Route::resource('characters', \App\Http\Controllers\Admin\CharacterController::class);
});

Route::middleware(['auth', 'admin.user', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('character-relationships/export/csv', \App\Http\Controllers\Admin\CharacterRelationshipCsvExportController::class)
        ->name('character-relationships.csv-export');

    Route::get('character-relationships/import/csv', [\App\Http\Controllers\Admin\CharacterRelationshipCsvImportController::class, 'create'])
        ->name('character-relationships.csv-import.create');

    Route::post('character-relationships/import/csv', [\App\Http\Controllers\Admin\CharacterRelationshipCsvImportController::class, 'store'])
        ->name('character-relationships.csv-import.store');

    Route::get('character-relationships/import/csv/sample', [\App\Http\Controllers\Admin\CharacterRelationshipCsvImportController::class, 'sample'])
        ->name('character-relationships.csv-import.sample');

    Route::post('character-relationships/bulk-action', \App\Http\Controllers\Admin\CharacterRelationshipBulkActionController::class)
        ->name('character-relationships.bulk-action');

    Route::resource('character-relationships', \App\Http\Controllers\Admin\CharacterRelationshipController::class)
        ->except(['show']);
});

Route::middleware(['auth', 'admin.user', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource(
        'monetization/services',
        \App\Http\Controllers\Admin\MonetizationServiceController::class
    )->except(['create', 'show'])->names('monetization.services');

    Route::resource(
        'monetization/programs',
        \App\Http\Controllers\Admin\AffiliateProgramController::class
    )->except(['create', 'show'])->names('monetization.programs');
});

Route::middleware(['auth', 'admin.user', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', \App\Http\Controllers\Admin\DashboardController::class)
        ->name('dashboard');
});

Route::middleware(['auth', 'admin.user', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('contact-messages', [\App\Http\Controllers\Admin\ContactMessageController::class, 'index'])
        ->name('contact-messages.index');

    Route::get('contact-messages/{contactMessage}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'show'])
        ->name('contact-messages.show');

    Route::post('contact-messages/{contactMessage}/mark-unread', [\App\Http\Controllers\Admin\ContactMessageController::class, 'markUnread'])
        ->name('contact-messages.mark-unread');

    Route::delete('contact-messages/{contactMessage}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])
        ->name('contact-messages.destroy');

    
    Route::post('tags/bulk-action', \App\Http\Controllers\Admin\TagBulkActionController::class)
        ->name('tags.bulk-action');

    Route::resource('tags', \App\Http\Controllers\Admin\TagController::class)
        ->except(['create', 'show']);
});



Route::get('/works/{work}', [\App\Http\Controllers\Public\WorkController::class, 'show'])
    ->name('public.works.show');

Route::get('/characters/{character}', [\App\Http\Controllers\Public\CharacterController::class, 'show'])
    ->name('public.characters.show');

Route::get('/contact', [\App\Http\Controllers\Public\ContactController::class, 'create'])
    ->name('public.contact.create');

Route::post('/contact', [\App\Http\Controllers\Public\ContactController::class, 'store'])
    ->name('public.contact.store');


Route::get('/', [\App\Http\Controllers\Public\WorkController::class, 'home'])
    ->name('public.home');


Route::get('/works', [\App\Http\Controllers\Public\WorkController::class, 'index'])
    ->name('public.works.index');


Route::get('/tags', [\App\Http\Controllers\Public\TagController::class, 'index'])
    ->name('public.tags.index');

Route::get('/contributor/apply', [\App\Http\Controllers\Public\ContributorApplicationController::class, 'create'])
    ->name('public.contributor.apply');

Route::post('/contributor/apply', [\App\Http\Controllers\Public\ContributorApplicationController::class, 'store'])
    ->name('public.contributor.apply.store');


Route::get(
    '/writing-tool',
    [\App\Http\Controllers\Public\WritingToolController::class, 'show']
)->name('public.writing-tool.show');

Route::get('/about', [\App\Http\Controllers\Public\AboutController::class, 'show'])
    ->name('public.about.show');


// Staff profile
Route::middleware(['auth', 'admin.user', 'password.changed'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/staff-profile', [\App\Http\Controllers\Admin\StaffProfileController::class, 'edit'])->name('staff-profile.edit');
    Route::patch('/staff-profile', [\App\Http\Controllers\Admin\StaffProfileController::class, 'update'])->name('staff-profile.update');
});

Route::get('/staff/{staffPublicId}', [\App\Http\Controllers\Public\StaffProfileController::class, 'show'])->name('public.staff.show');
Route::post('/helpful', [\App\Http\Controllers\Public\HelpfulVoteController::class, 'store'])->name('public.helpful.store');

