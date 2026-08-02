<?php
namespace Tests\Feature\Public;
use Tests\TestCase;
class HomeMediaLoginAndStoryNotesTest extends TestCase
{
    public function test_requested_ui_changes_are_present(): void
    {
        $index=file_get_contents(resource_path('views/public/works/index.blade.php'));
        $controller=file_get_contents(app_path('Http/Controllers/Public/WorkController.php'));
        $login=file_get_contents(base_path('resources/views/public/writing-tool.blade.php'));
        $show=file_get_contents(base_path('resources/views/public/works/show.blade.php'));
        $this->assertStringContainsString('媒体から探す',$index);
        $this->assertStringContainsString("request('original_media', '')",$controller);
        $this->assertStringContainsString('データベースを見る',$login);
        $this->assertFalse(str_contains($show,'備考：') && str_contains($show,'$event->notes'));
    }
}
