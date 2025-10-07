<?php

declare(strict_types=1);

namespace MiniORM\Models;

use MiniORM\Model;

// Post modeli
class Post extends Model
{
    protected string $table = 'posts';
    
    protected array $fillable = [
        'user_id',
        'category_id',
        'title',
        'content',
        'status',
        'created_at',
        'updated_at'
    ];

    // Post kime ait
    public function user(): mixed
    {
        return $this->belongsTo(User::class);
    }

    // Post hangi kategoride
    public function category(): mixed
    {
        return $this->belongsTo(Category::class);
    }

    // Post'a yapılan yorumlar
    public function comments(): array
    {
        return $this->hasMany(Comment::class);
    }
}

