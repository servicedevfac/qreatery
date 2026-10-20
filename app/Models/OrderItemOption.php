<?php

namespace App\Models;

use Database\Factories\OrderItemOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_item_id', 'option_id', 'option_name', 'price_delta_minor'])]
class OrderItemOption extends Model
{
    /** @use HasFactory<OrderItemOptionFactory> */
    use HasFactory;

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    protected function casts(): array
    {
        return ['price_delta_minor' => 'integer'];
    }
}
