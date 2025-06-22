<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Landlord extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'national_id',
        'phone_number',
        'photo'
    ];

    public function buildings()
    {
        return $this->hasMany(Building::class);
    }
}