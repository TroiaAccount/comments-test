<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    protected $fillable = ['user_name', 'email', 'text', 'file', 'parent_id', 'home_page'];

    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }

}
