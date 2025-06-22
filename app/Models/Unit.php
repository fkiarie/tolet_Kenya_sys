<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'unit_no',
        'state',
        'rent_per_month',
        'deposit',
        'unit_type'
    ];

    protected $casts = [
        'rent_per_month' => 'decimal:2',
        'deposit' => 'decimal:2',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_unit')
            ->withPivot('move_in_date', 'move_out_date')
            ->withTimestamps();
    }

    public function scopeAvailable($query)
    {
        return $query->where('state', 'vacant');
    }
}
// This model represents a unit in a building.