<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['disk', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'uploaded_by'])]
class Media extends Model
{
    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }
}
