<?php

namespace App\Models;

use App\Utilities\TranslationIntoLatin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Brand extends Model
{
    use HasFactory;


    protected $fillable = [
        'slug',
        'name',
        'image',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Brand $brand) {
            $translation = new TranslationIntoLatin();
            $brand->slug = $brand->slug ?? str($translation->translate($brand->name))->slug();
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
