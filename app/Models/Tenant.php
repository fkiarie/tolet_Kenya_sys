<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'national_id',
        'phone_number',
        'photo',
        'next_of_kin_name',
        'next_of_kin_contact'
    ];

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'tenant_unit')
            ->withPivot('move_in_date', 'move_out_date')
            ->withTimestamps();
    }
}
