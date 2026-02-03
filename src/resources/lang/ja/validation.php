<?php

return [
    'required' => ':attributeを入力してください',
    'confirmed' => ':attributeと一致しません',
    'date_format' => ':attributeの形式が正しくありません',

    'after' => ':attributeは:dateより後の時刻にしてください',
    'before' => ':attributeは:dateより前の時刻にしてください',
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください',
    ],

    'custom' => [
        'name' => [
            'required' => 'お名前を入力してください',
        ],
        'email' => [
            'required' => 'メールアドレスを入力してください',
        ],
        'password' => [
            'required' => 'パスワードを入力してください',
            'min' => 'パスワードは8文字以上で入力してください',
            'confirmed' => 'パスワードと一致しません',
        ],
    ],

    'attributes' => [
        'name' => 'お名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'work_start' => '出勤時間',
        'work_end' => '退勤時間',
        'break1_start' => '休憩開始時間',
        'break1_end' => '休憩終了時間',
        'break2_start' => '休憩2開始時間',
        'break2_end' => '休憩2終了時間',
        'note' => '備考',
    ],
];
