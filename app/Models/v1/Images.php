<?php

namespace App\Models\v1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
