<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

/**
 * @mixin Builder
 */
class NorwegianWords extends Model
{
    use HasFactory, AsSource;

    protected $table = 'norwegian_words';
}
