<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';

    protected $fillable = array('hotel_id', 'user_id', 'hotel_name', 'room_type', 'night_stay');

    public $timestamps = true;
    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function hotel() {
        return $this->belongsTo('App\Model\Hotel');
    }

}
