<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'ip_ranges'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /** @return list<string> CIDR ranges, e.g. ["10.1.0.0/16"] */
    public function ipRangeList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->ip_ranges))));
    }
}
