<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $filleable = array('full_name', 'email', 'password', 'phone_number');
    public $timestamps = true;

    protected $hidden = [
        'password'
    ];

    public function hotel() {
        return $this->hasMany('App\Models\Hotel');
    }

    public function bill() {
        return $this->hasMany('App\Models\Bill');
    }

    public function reservation() {
        return $this->hasMany('App\Models\Reservation');
    }
}

?>