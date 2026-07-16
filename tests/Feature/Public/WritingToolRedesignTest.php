<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class WritingToolRedesignTest extends TestCase
{
    public function test_writing_tool_page_has_redesigned_content(): void
    {
        $this->get('/writing-tool')
            ->assertOk()
            ->assertSee('writing-tool-page-redesign', false)
            ->assertSee('何度でもコピー')
            ->assertDontSee('AIの回答を保存');
    }

    public function test_page_has_ai_disclosure_and_x_link(): void
    {
        $this->get('/writing-tool')
            ->assertOk()
            ->assertSee('AIを使用していることを明記してください')
            ->assertSee('#oshiwiki')
            ->assertSee('投稿された作品を読みに伺います')
            ->assertSee('https://x.com/Oshi_Wiki', false)
            ->assertSee('@Oshi_Wiki');
    }
}
