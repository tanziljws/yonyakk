<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content',
        'category',
        'author',
        'status',
        'image',
        'published_at'
    ];

    protected $casts = [
        'status' => 'boolean',
        'published_at' => 'datetime'
    ];
} 