<?php

namespace App\Models;

use App\Models\Concerns\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use CascadeSoftDeletes, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',

        'score',
        'price_score',

        'tags',

        'type_id',
        'owner_id',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    protected $cascadeDeletes = ['dishes'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(RestaurantType::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
