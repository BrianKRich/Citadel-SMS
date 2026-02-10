<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name'];

    public function roles(): HasMany
    {
        return $this->hasMany(EmployeeRole::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
