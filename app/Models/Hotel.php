<?php
    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Hotel extends Model {
        protected $table = 'hotels';
        protected $primaryKey = 'hotel_id';

        protected $fillable = array('name', 'capacity', 'location');

        public $timestamps = true;

        public function user () {
            return $this->belongsTo('App\Models\User');
        }

        public function room () {
            return $this->hasMany('App\Models\Room');
        }

        public function reservation() {
            return $this->hasMany('App\Models\Reservation');
        }
    }
?>