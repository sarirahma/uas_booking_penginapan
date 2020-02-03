<?php
namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Reservation as AppReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller {
    public function index(Request $request) {
        $acceptHeader = $request->header('Accept');
        $id = Auth::guard('admin')->user()->user_id;

        if (Gate::allows('admin')) {
            $reservation = Reservation::OrderBy("reservation_id", "DESC")->paginate(10)->toArray();
        } else {
            $reservation = Reservation::Where(['user_id' => $id])->OrderBy("reservation_id", "DESC")->paginate(2)->toArray();
        }

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Data not Found!'
            ], 404);
        }

         if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $response = [
                "total_count" => $reservation["total"],
                "limit" => $reservation["per_page"],
                "pagination" => [
                    "next_page" => $reservation["next_page_url"],
                    "current_page" => $reservation["current_page"]
                ],
                "data" => $reservation["data"],
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<Reservationn/>');

                $xml->addChild('total_count', $reservation['total']);
                $xml->addChild('limit', $reservation['per_page']);
                $pagination = $xml->addChild('pagination');
                $pagination->addChild('next_page', $reservation['next_page_url']);
                $pagination->addChild('current_page', $reservation['current_page']);
                $xml->addChild('total_count', $reservation['total']);

                foreach ($reservation['data'] as $item) {
                    $xmlItem = $xml->addChild('reservation');

                    $xmlItem->addChild('reservation_id', $item['reservation_id']);
                    $xmlItem->addChild('user_id', $item['user_id']);
                    $xmlItem->addChild('hotel_name', $item['hotel_name']);
                    $xmlItem->addChild('room_type', $item['room_type']);
                    $xmlItem->addChild('night_stay', $item['night_stay']);
                    $xmlItem->addChild('created_at', $item['created_at']);
                    $xmlItem->addChild('updated_at', $item['updated_at']);
                }

                return $xml->asXML();
            } 
        } else {
            return response('Not Acceptable!', 406);
        }
    }

	public function store(Request $request) {
        $acceptHeader = $request->header('Accept');

        if (Gate::allows('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $input = $request->all();

            $validationRules = [
                'hotel_id' => 'required',
                'hotel_name' => 'required',
                'room_id' => 'required',
                'room_type' => 'required|in:luxury,premium,standard',
                'night_stay' => 'required'
            ];

            $validator = Validator::make($input, $validationRules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $reservation= new Reservation;
            $reservation->hotel_id = $request->input('hotel_id');
            $reservation->hotel_name = $request->input('hotel_name');
            $reservation->room_id = $request->input('room_id');
            $reservation->room_type = $request->input('room_type');
            $reservation->user_id = Auth::guard('admin')->user()->user_id;
            $reservation->night_stay = $request->input('night_stay');
            $reservation->save();
            return response()->json($reservation, 200);
        } else {
            return response('Not Acceptable!', 406);
        }
	}

    public function show(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $reservation = AppReservation::find($id);
        
        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

    
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if ($acceptHeader === 'application/json') {
                return response()->json($reservation, 200);
            } else {
                $xml = new \SimpleXMLElement('<Reservation/>');

                $xml->addChild('reservation_id', $reservation->reservation_id);
                $xml->addChild('hotel_id', $reservation->hotel_id);
                $xml->addChild('hotel_name', $reservation->hotel_name);
                $xml->addChild('room_id', $reservation->room_id);
                $xml->addChild('room_type', $reservation->room_type);
                $xml->addChild('night_stay', $reservation->night_stay);
                $xml->addChild('created_at', $reservation->created_at);
                $xml->addChild('updated_at', $reservation->updated_at);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    public function update(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $contentTypeHeader = $request->header('Content-Type');
        
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

        if (Gate::allows('admin') || $reservation->user_id != Auth::guard('admin')->user()->user_id) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }

        $input = $request->all();

        $validationRules =[
            'hotel_id' => 'required',
            'hotel_name' => 'required',
            'room_id' => 'required',
            'room_type' => 'required|in:luxury,premium,standard',
            'night_stay' => 'required'
        ];
        
        $validator = Validator::make($input,$validationRules);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if ($contentTypeHeader === 'application/json' || $contentTypeHeader === 'application/xml') {

                $reservation->fill($input);
                
                if ($acceptHeader === 'application/json' && $contentTypeHeader === 'application/json') {
                    $reservation->save();
                    return response()->json($reservation, 200);
                } else if ($acceptHeader === 'application/xml' && $contentTypeHeader === 'application/xml') {
                    $reservation->save();

                    $xml = new \SimpleXMLElement('<Reservation/>');

                    $xml->addChild('reservation_id', $reservation->reservation_id);
                    $xml->addChild('hotel_id', $reservation->hotel_id);
                    $xml->addChild('hotel_name', $reservation->hotel_name);
                    $xml->addChild('room_id', $reservation->room_id);
                    $xml->addChild('room_type', $reservation->room_type);
                    $xml->addChild('night_stay', $reservation->night_stay);
                    $xml->addChild('created_at', $reservation->created_at);
                    $xml->addChild('updated_at', $reservation->updated_at);
                    
                    return $xml->asXML();
                } else {
                    return response('Not Acceptable!', 406);
                }
            } else {
                return response('Unsupported Media Type', 403);
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    public function destroy(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $reservation = Reservation::find($id);
        
        if(!$reservation) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

        if (Gate::allows('admin') || $reservation->user_id != Auth::guard('admin')->user()->user_id) {
            return response()->json([
                'success' => false,
                'status' => 403, 
                'message' => 'You are Unauthorized'
            ], 403);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {   
            $reservation->delete();
            $response = [
                'message' => 'Deleted Successfully!',
                'reservation_id' => $id
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<Reservation/>');

                $xml->addChild('message', 'Deleted Successfully!');
                $xml->addChild('reservation_id', $id);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }
}
