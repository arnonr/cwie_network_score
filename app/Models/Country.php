<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{

    use HasFactory;
    protected $table = 'country';
    // protected $primaryKey = 'ct_code';

    protected $fillable = [
        'ct_code',
        'name',
        'ct_nameENG',
        'ct_nameTHA',
        'code',
    ];
}
