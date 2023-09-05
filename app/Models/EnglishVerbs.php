<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin Builder
 */

class EnglishVerbs extends Model
{
    use HasFactory, AsSource;

    protected $table = 'english_verbs';
}
