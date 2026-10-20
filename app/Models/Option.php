<?php

namespace App\Models;

use Database\Factories\OptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['option_group_id', 'name', 'price_delta_minor', 'is_active', 'position'])]
class Option extends Model
{
    /** @use HasFactory<OptionFactory> */
    use HasFactory;

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(OptionGroup::class);
    }

    protected function casts(): array
    {
        return [
            'price_delta_minor' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
