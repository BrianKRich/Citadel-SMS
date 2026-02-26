<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'is_system'];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(EmployeeRole::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
