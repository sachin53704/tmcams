<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;

class Contractor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ 'name', 'initial' ];


    public static function booted()
    {
        static::created(function (Contractor $contractor)
        {
            self::where('id', $contractor->id)->update([
                'initial'=> preg_filter('/[^A-Z]/', '', ucwords($contractor->name)),
                'created_by'=> Auth::user()->id,
            ]);
        });
        static::updated(function (Contractor $contractor)
        {
            self::where('id', $contractor->id)->update([
                'initial'=> preg_filter('/[^A-Z]/', '', ucwords($contractor->name)),
                'updated_by'=> Auth::user()->id,
            ]);
        });
        static::deleted(function (Contractor $contractor)
        {
            self::where('id', $contractor->id)->update([
                'deleted_by'=> Auth::user()->id,
            ]);
        });
    }
}
