<?php

return [
    'limits' => [
        'original_characters_per_user' => 30,
        'relationships_per_user' => 100,
        'prompts_per_user' => 50,

        'prompt_body_max_length' => 20000,
        'synopsis_max_length' => 5000,

        'note_max_length' => 2000,
        'long_note_max_length' => 5000,
    ],

    'labels' => [
        'original_characters_per_user' => 'オリジナルキャラクター',
        'relationships_per_user' => '関係性',
        'prompts_per_user' => '保存プロンプト',
        'prompt_body_max_length' => 'プロンプト本文',
        'synopsis_max_length' => 'あらすじ',
        'note_max_length' => '備考',
        'long_note_max_length' => '長文備考',
    ],
];
