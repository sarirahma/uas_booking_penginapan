<?php
namespace App\Http\Controllers;

use App\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller {
	public function index(Request $request) {
        $acceptHeader = $request->header('Accept');
        $room = Room::OrderBy("room_id", "DESC")->paginate(10)->toArray();

        if (!$room) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $response = [
                "total_count" => $room["total"],
                "limit" => $room["per_page"],
                "pagination" => [
                    "next_page" => $room["next_page_url"],
                    "current_page" => $room["current_page"]
                ],
                "data" => $room["data"],
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<Data_Room/>');

                $xml->addChild('total_count', $room['total']);
                $xml->addChild('limit', $room['per_page']);
                $pagination = $xml->addChild('pagination');
                $pagination->addChild('next_page', $room['next_page_url']);
                $pagination->addChild('current_page', $room['current_page']);
                $xml->addChild('total_count', $room['total']);

                foreach ($room['data'] as $item) {
                    $xmlItem = $xml->addChild('room');

                    $xmlItem->addChild('room_id', $item['room_id']);
                    $xmlItem->addChild('hotel_id', $item['hotel_id']);
                    $xmlItem->addChild('room_type', $item['room_type']);
                    $xmlItem->addChild('price', $item['price']);
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
        $contentTypeHeader = $request->header('Content-Type');
        $input = $request->all();

        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403, 
                'message' => 'You are Unauthorized'
            ], 403);
        }

        $validationRules = [
            'hotel_id' => 'required|exists:hotels',
            'room_type' => 'required|in:luxury,premium,standard',
            'price' => 'required',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $room = new Room;
        $room->hotel_id = $request->input('hotel_id');
        $room->room_type = $request->input('room_type');
        $room->price = $request->input('price');

        if ($acceptHeader === 'application/json' || $contentTypeHeader === 'application/xml') {
            if ($contentTypeHeader === 'application/json' || $contentTypeHeader === 'application/xml') {
                if ($acceptHeader === 'application/json' && $contentTypeHeader === 'application/json') {
                    $room->save();

                    return response()->json($room, 200);
                }
                else if ($acceptHeader === 'appication/xml' && $contentTypeHeader === 'application/xml'){
                    $room->save();

                    $xml = new \SimpleXMLElement('<room/>');

                    $xml->addChild('room_id', $room->room_id);
                    $xml->addChild('hotel_id', $room->hotel_id);
                    $xml->addChild('room_type', $room->room_type);
                    $xml->addChild('price', $room->price);
                    $xml->addChild('created_at', $room->created_at);
                    $xml->addChild('updated_at', $room->updated_at);

                    return $xml->asXML();
                }
                else {
                    return response('Not Acceptable!', 406);
                }
            } else {
                return response('Unsupported Media Type', 403);
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    public function show(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if ($acceptHeader === 'application/json') {
                return response()->json($room, 200);
            } else {
                $xml = new \SimpleXMLElement('<room/>');

                $xml->addChild('room_id', $room->room_id);
                $xml->addChild('hotel_id', $room->hotel_id);
                $xml->addChild('room_type', $room->room_type);
                $xml->addChild('price', $room->price);
                $xml->addChild('created_at', $room->created_at);
                $xml->addChild('updated_at', $room->updated_at);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    public function update(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $contentTypeHeader = $request->header('Content-Type');
        $room = Room::find($id);

        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403, 
                'message' => 'You are Unauthorized'
            ], 403);
        }

        if(!$room) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'Data not Found'
            ], 404);
        }

        $input = $request->all();

        $validationRules = [
            'room_type' => 'required|in:luxury,premium,standard',
            'price' => 'required',
        ];

        $validator = Validator::make($input, 
            $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $room->fill($input);
            $room->save();

            if ($acceptHeader === 'application/json') {
                if ($contentTypeHeader === 'application/json') {
                    return response()->json($room, 200);
                } else {
                    return response('Unsupported Media Type', 403);
                }
            }
            else if ($acceptHeader === 'application/xml') {
                if ($contentTypeHeader === 'application/xml') {
                    $xml = new \SimpleXMLElement('<room/>');

                    $xml->addChild('room_id', $room->room_id);
                    $xml->addChild('hotel_id', $room->hotel_id);
                    $xml->addChild('room_type', $room->room_type);
                    $xml->addChild('price', $room->price);
                    $xml->addChild('created_at', $room->created_at);
                    $xml->addChild('updated_at', $room->updated_at);

                    return $xml->asXML();
                }
                else {
                    return response('Unsupported Media Type', 403);
                }
            }
            else {
                return response('Not Acceptable!', 406);
            }
        }
        else {
            return response('Not Acceptable!', 406);
        }
    }

    public function destroy(Request $request, $id){
        $acceptHeader = $request->header('Accept');
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }
        
        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403, 
                'message' => 'You are Unauthorized'
            ], 403);
        }
        
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $room->delete();
                
            $response = [
                'message' => 'Deleted Successfully!',
                'room_id' => $id
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<room/>');

                $xml->addChild('message', 'Deleted Successfully!');
                $xml->addChild('room_id', $id);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
	}
}

?>