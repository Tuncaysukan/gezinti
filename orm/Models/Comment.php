<?php

declare(strict_types=1);

namespace MiniORM\Models;

use MiniORM\Model;

// Yorum modeli
class Comment extends Model
{
    protected string $table = 'comments';
    
    protected array $fillable = [
        'user_id',
        'post_id',
        'content',
        'created_at',
        'updated_at'
    ];

    // Yorumu kim yazdı
    public function user(): mixed
    {
        return $this->belongsTo(User::class);
    }

    // Yorum hangi posta ait
    public function post(): mixed
    {
        return $this->belongsTo(Post::class);
    }
}

