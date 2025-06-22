<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'county',
        'constituency', 
        'location',
        'unit_type',
        'image',
        'landlord_id'
    ];

    protected $casts = [
        'unit_type' => 'array', // Multiple unit types in one building
    ];

    public function landlord()
    {
        return $this->belongsTo(Landlord::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}