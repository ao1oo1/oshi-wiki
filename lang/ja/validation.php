<?php

return [
    'required' => ':attribute は必須です。',
    'email' => ':attribute には有効なメールアドレスを入力してください。',
    'confirmed' => ':attribute が確認用の入力と一致しません。',
    'unique' => ':attribute は使用されています。',
    'min' => [
        'string' => ':attribute は :min 文字以上で入力してください。',
    ],
    'custom' => [
        'email' => [
            'unique' => 'こちらのメールアドレスは使用されています',
        ],
    ],

    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => '確認用パスワード',
    ],
];
