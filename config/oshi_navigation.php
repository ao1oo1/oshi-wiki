<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Oshi-Wiki Navigation
    |--------------------------------------------------------------------------
    |
    | 管理画面と一般執筆ユーザー画面のメニューをここで分離管理します。
    | Bladeに直接 admin / writer のメニューを散らばらせないための設定です。
    |
    */

    'home_routes' => [
        'admin' => 'admin.dashboard',
        'writer' => 'writer.dashboard',
    ],

    'menus' => [
        'admin' => [
            [
                'label' => 'ダッシュボード',
                'route' => 'admin.dashboard',
                'active' => 'admin.dashboard',
            ],
            [
                'label' => '作品管理',
                'route' => 'admin.works.index',
                'active' => 'admin.works.*',
            ],
            [
                'label' => 'キャラクター管理',
                'route' => 'admin.characters.index',
                'active' => 'admin.characters.*',
            ],
            [
                'label' => '関係性管理',
                'route' => 'admin.character-relationships.index',
                'active' => 'admin.character-relationships.*',
            ],
            [
                'label' => 'タグ管理',
                'route' => 'admin.tags.index',
                'active' => 'admin.tags.*',
            ],
            [
                'label' => '承認申請',
                'route' => 'admin.review-requests.index',
                'active' => 'admin.review-requests.*',
            ],
            [
                'label' => '登録申請',
                'route' => 'admin.contributor-applications.index',
                'active' => 'admin.contributor-applications.*',
            ],
            [
                'label' => 'お問い合わせ',
                'route' => 'admin.contact-messages.index',
                'active' => 'admin.contact-messages.*',
            ],
            [
                'label' => 'スタッフプロフィール',
                'route' => 'admin.staff-profile.edit',
                'active' => 'admin.staff-profile.*',
            ],
        ],

        'writer' => [
            [
                'label' => 'ダッシュボード',
                'route' => 'writer.dashboard',
                'active' => 'writer.dashboard',
            ],
            [
                'label' => 'オリジナルキャラクター',
                'route' => 'writer.original-characters.index',
                'active' => 'writer.original-characters.*',
            ],
            [
                'label' => '関係性',
                'route' => 'writer.original-character-relationships.index',
                'active' => 'writer.original-character-relationships.*',
            ],
            [
                'label' => '保存プロンプト',
                'route' => 'writer.prompts.index',
                'active' => 'writer.prompts.*',
            ],
            [
                'label' => '使い方ガイド',
                'route' => 'writer.guide',
                'active' => 'writer.guide',
            ],
        ],
    ],
];
