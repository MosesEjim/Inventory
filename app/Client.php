<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone'
    ];

    public function sales()
    {
        return $this->hasMany('App\Sale');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}
