<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fix extends Model
{

    use HasFactory;
    protected $table = 'fix';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'title',
        'detail',
        'building_id',
        'place',
        'fix_img_file',
        'fix_img2_file',
        'fix_img3_file',
        'name',
        'email',
        'phone',
        'user_id',
        'fix_date',
        'place',
        'is_publish',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];

    public function building()
    {
        return  $this->belongsTo(Building::Class, 'building_id', 'id');
    }

    // public function country()
    // {
    //     return  $this->belongsTo(Country::Class, 'country_code', 'ct_code');
    // }
}
