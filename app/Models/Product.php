<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'delivery_id',
        'status_product_id',
        'image',
        'unboxing_image',
        'description',
        'employee_id'
    ];

    protected $appends = ['image_url', 'unboxing_image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }
        return null;
    }

    public function getUnboxingImageUrlAttribute()
    {
        if ($this->unboxing_image) {
            return Storage::disk('public')->url($this->unboxing_image);
        }
        return null;
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusProduct::class, 'status_product_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Remove the createdBy relation
}
