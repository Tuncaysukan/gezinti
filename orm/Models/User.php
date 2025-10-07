<?php

declare(strict_types=1);

namespace MiniORM\Models;

use MiniORM\Model;

// User modeli
class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'name',
        'email',
        'password',
        'status',
        'age',
        'created_at',
        'updated_at'
    ];

    // Kullanıcının postları
    public function posts(): array
    {
        return $this->hasMany(Post::class);
    }

    // Kullanıcının yorumları
    public function comments(): array
    {
        return $this->hasMany(Comment::class);
    }

    // Kullanıcının ilgilendiği kategoriler (many-to-many)
    public function categories(): array
    {
        return $this->belongsToMany(Category::class, 'user_category');
    }
}

