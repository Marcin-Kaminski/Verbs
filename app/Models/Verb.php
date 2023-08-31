<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Verb
 *
 * @property int $id
 * @property string $verb_in_polish
 * @property string $verb_in_infinitive
 * @property string $verb_in_past_simple
 * @property string $verb_in_past_participle
 * @property string|null $additional_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Verb newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Verb newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Verb query()
 * @method static \Illuminate\Database\Eloquent\Builder|Verb whereAdditionalDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Verb whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Verb whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Verb whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Verb whereVerbInInfinitive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Verb whereVerbInPastParticiple($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Verb whereVerbInPastSimple($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Verb whereVerbInPolish($value)
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder create(array $attributes = [])
 * @method public Builder update(array $values)
 * @mixin \Eloquent
 */

class Verb extends Model
{
    use HasFactory, AsSource;
}

