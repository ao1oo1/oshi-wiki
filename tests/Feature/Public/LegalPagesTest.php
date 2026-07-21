<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_legal_pages_are_public(): void
    {
        $pages = [
            'public.privacy' => 'プライバシーポリシー',
            'public.terms' => '利用規約',
            'public.legal' => '特定商取引法に基づく表記',
            'public.billing-policy' => '解約・返金ポリシー',
            'public.pricing' => '料金プラン',
        ];

        foreach ($pages as $route => $text) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($text);
        }
    }

    public function test_privacy_policy_has_service_specific_sections(): void
    {
        $this->get(route('public.privacy'))
            ->assertOk()
            ->assertSee('公開ページ')
            ->assertSee('執筆ツール')
            ->assertSee('有料プラン')
            ->assertSee('生成AIの学習用データとして利用しません')
            ->assertSee('本人が明示的に公開操作');
    }
}
