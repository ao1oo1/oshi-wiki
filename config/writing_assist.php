<?php

return [
    'limits' => [
        'original_characters_per_user' => 30,
        'relationships_per_user' => 100,
        'prompts_per_user' => 50,
        'stories_per_user' => 10,
        'story_analyses_per_user' => 10,
        'story_body_max_length' => 100000,
        'analysis_result_max_length' => 10000,

        'prompt_body_max_length' => 20000,
        'synopsis_max_length' => 5000,

        'note_max_length' => 2000,
        'long_note_max_length' => 5000,
    ],

    'labels' => [
        'original_characters_per_user' => 'オリジナルキャラクター',
        'relationships_per_user' => '関係性',
        'prompts_per_user' => '保存プロンプト',
        'stories_per_user' => 'ストーリー',
        'story_analyses_per_user' => '文体分析',
        'story_body_max_length' => 'ストーリー本文',
        'analysis_result_max_length' => '文体分析結果',
        'prompt_body_max_length' => 'プロンプト本文',
        'synopsis_max_length' => 'あらすじ',
        'note_max_length' => '備考',
        'long_note_max_length' => '長文備考',
    ],
];
