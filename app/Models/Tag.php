<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Utilities\TranslationIntoLatin;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'image',
    ];
//
//    protected $with = [
//        'products',
//    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Tag $tag) {
            $tag->slug = $tag->slug ?? str(TranslationIntoLatin::translate($tag->name))->slug();
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
