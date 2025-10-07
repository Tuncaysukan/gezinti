<?php

declare(strict_types=1);

namespace MiniORM\Models;

use MiniORM\Model;

// Kategori modeli
class Category extends Model
{
    protected string $table = 'categories';
    
    protected array $fillable = [
        'name',
        'slug',
        'created_at',
        'updated_at'
    ];

    // Bu kategorideki postlar
    public function posts(): array
    {
        return $this->hasMany(Post::class);
    }

    // Bu kategoriyle ilgilenen kullanıcılar
    public function users(): array
    {
        return $this->belongsToMany(User::class, 'user_category');
    }
}

