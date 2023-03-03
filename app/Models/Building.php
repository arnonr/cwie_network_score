<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{

    use HasFactory;
    protected $table = 'building';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'name_en',
        'is_publish',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}
