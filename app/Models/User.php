<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizeableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticableContract, AuthorizeableContract, JWTSubject {
    use Authenticatable, Authorizable;
    
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = array('full_name', 'email', 'password', 'phone_number');
    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     * 
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * JWT Implementation
     * Get the identifier that will be stored in the subject claim of the JWT.
     * 
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     * 
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

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