<?php

namespace App\Models;

use App\Events\OrderCreated;
use App\Events\OrderDeleted;
use App\Events\OrderUpdated;
use App\States\OrderState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\ModelStates\HasStates;

class Order extends Model
{
    use HasFactory, HasStates;

    protected $fillable = [
        'state',
        'total',

        'restaurant_id',
        'user_id',
    ];

    // Append computed attributes to the model's array / JSON form
    protected $appends = [
        'allowed_transitions',
        'total_items',
        'items',
    ];

    protected $casts = [
        'state' => OrderState::class,
    ];

    protected $dispatchesEvents = [
        'created' => OrderCreated::class,
        'updated' => OrderUpdated::class,
        'deleted' => OrderDeleted::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class)
            ->withPivot('quantity');
    }

    /**
     * Allowed next states from the current state, derived from the state machine config.
     *
     * @return string[]
     */
    public function getAllowedTransitionsAttribute(): array
    {
        return $this->state->transitionableStates();
    }

    /**
     * Total number of items in the order (sum of pivot quantities)
     */
    public function getTotalItemsAttribute(): int
    {
        $sum = 0;
        foreach ($this->dishes as $dish) {
            $sum += (int) ($dish->pivot->quantity ?? 0);
        }

        return $sum;
    }

    /**
     * Return structured items (dish id, name, price, quantity)
     */
    public function getItemsAttribute(): array
    {
        $items = [];
        foreach ($this->dishes as $dish) {
            $items[] = [
                'id' => $dish->id,
                'name' => $dish->name,
                'price' => $dish->price,
                'quantity' => (int) ($dish->pivot->quantity ?? 0),
            ];
        }

        return $items;
    }
}
