<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category',
        'name',
        'email',
        'subject',
        'body',
        'target_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'correction' => '間違いの指摘',
            'copyright' => '著作者による削除希望',
            'contributor' => 'コントリビューター希望',
            'discord' => '開発者コミュニティ参加希望',
            default => 'その他',
        };
    }
}
