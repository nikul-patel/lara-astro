<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'content',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'slug',
        'title',
        'content',
        'meta_title',
        'meta_description',
    ];
}
