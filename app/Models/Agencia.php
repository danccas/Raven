<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agencia extends Model
{
    protected $table = 'comercial.agencia';
    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $fillable = [
        'nombre', 'estado'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getDireccion() {
        return $this->direccion;
    }

}