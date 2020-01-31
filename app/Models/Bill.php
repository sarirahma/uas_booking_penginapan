<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $table = 'bills';
    protected $primaryKey = 'bill_id';

    protected $fillable = array('reservation_id', 'user_id', 'total');

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function reservation() {
        return $this->belongsTo('App\Models\Reservation');
    }
}
