<?php
    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Room extends Model {
        protected $table = 'rooms';
        protected $primaryKey = 'room_id';

        protected $fillable = array('hotel_id', 'room_type', 'price');

        public $timestamps = true;

        public function user () {
            return $this->belongsTo('App\Models\User');
        }

        public function hotel () {
            return $this->belongsTo('App\Models\Hotels');
        }

        public function reservation () {
            return $this->hasMany('App\Models\Reservation');
        }
    }
