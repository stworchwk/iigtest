<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['full_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute() //An Accessor - Laravel v.8
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function setImagePathAttribute($value) //A Mutator - Laravel v.8
    {
        $this->attributes['image_path'] = url($value);
    }

    protected $hidden = [
        'first_name',
        'last_name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}
