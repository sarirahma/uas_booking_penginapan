<?php
namespace App\Http\Controllers;

use App\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class HotelController extends Controller {
    public function index(Request $request) {
        $acceptHeader = $request->header('Accept');
        $hotel = Hotel::OrderBy("hotel_id", "DESC")->paginate(10)->toArray();

        if (!$hotel) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $response = [
                "total_count" => $hotel["total"],
                "limit" => $hotel["per_page"],
                "pagination" => [
                    "next_page" => $hotel["next_page_url"],
                    "current_page" => $hotel["current_page"]
                ],
                "data" => $hotel["data"],
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<Data_Hotel/>');

                $xml->addChild('total_count', $hotel['total']);
                $xml->addChild('limit', $hotel['per_page']);
                $pagination = $xml->addChild('pagination');
                $pagination->addChild('next_page', $hotel['next_page_url']);
                $pagination->addChild('current_page', $hotel['current_page']);
                $xml->addChild('total_count', $hotel['total']);

                foreach ($hotel['data'] as $item) {
                    $xmlItem = $xml->addChild('hotel');

                    $xmlItem->addChild('hotel_id', $item['hotel_id']);
                    $xmlItem->addChild('name', $hotel->name);
                    $xmlItem->addChild('capacity', $hotel->capacity);
                    $xmlItem->addChild('location', $hotel->location);
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
            'name' => 'required',
            'capacity' => 'required',
            'location' => 'required',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $hotel = new Hotel;
        $hotel->name = $request->input('name');
        $hotel->capacity = $request->input('capacity');
        $hotel->location = $request->input('location');
        
        if ($acceptHeader === 'application/json' || $contentTypeHeader === 'application/xml') {
            if ($contentTypeHeader === 'application/json' || $contentTypeHeader === 'application/xml') {
                if ($acceptHeader === 'application/json' && $contentTypeHeader === 'application/json') {
                    $hotel->save();

                    return response()->json($hotel, 200);
                } else if ($acceptHeader === 'appication/xml' && $contentTypeHeader === 'application/xml'){
                    $hotel->save();

                    $xml = new \SimpleXMLElement('<hotel/>');

                    $xml->addChild('hotel_id', $hotel->hotel_id);
                    $xml->addChild('name', $hotel->name);
                    $xml->addChild('capacity', $hotel->capacity);
                    $xml->addChild('location', $hotel->location);
                    $xml->addChild('created_at', $hotel->created_at);
                    $xml->addChild('updated_at', $hotel->updated_at);

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

    public function show(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $hotel = Hotel::find($id);

        if (!$hotel) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if ($acceptHeader === 'application/json') {
                return response()->json($hotel, 200);
            } else {
                $xml = new \SimpleXMLElement('<hotel/>');

                $xml->addChild('hotel_id', $hotel->hotel_id);
                $xml->addChild('name', $hotel->name);
                $xml->addChild('capacity', $hotel->capacity);
                $xml->addChild('location', $hotel->location);
                $xml->addChild('created_at', $hotel->created_at);
                $xml->addChild('updated_at', $hotel->updated_at);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    public function update(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $contentTypeHeader = $request->header('Content-Type');
        $hotel = Hotel::find($id);
        
        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403, 
                'message' => 'You are Unauthorized'
            ], 403);
        }

        if(!$hotel) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'Data not Found'
            ], 404);
        }

        $input = $request->all();
        $validationRules = [
            'name' => 'required',
            'capacity' => 'required',
            'location' => 'required',
        ];

        $validator = Validator::make($input, 
            $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $hotel->fill($input);
            $hotel->save();

            if ($acceptHeader === 'application/json') {
                if ($contentTypeHeader === 'application/json') {
                    return response()->json($hotel, 200);
                } else {
                    return response('Unsupported Media Type', 403);
                }
            } else if ($acceptHeader === 'application/xml') {
                if ($contentTypeHeader === 'application/xml') {
                    $xml = new \SimpleXMLElement('<hotel/>');

                    $xml->addChild('hotel_id', $hotel->hotel_id);
                    $xml->addChild('name', $hotel->name);
                    $xml->addChild('capacity', $hotel->capacity);
                    $xml->addChild('location', $hotel->location);
                    $xml->addChild('created_at', $hotel->created_at);
                    $xml->addChild('updated_at', $hotel->updated_at);

                    return $xml->asXML();
                } else {
                    return response('Unsupported Media Type', 403);
                }
            } else {
                return response('Not Acceptable!', 406);
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

	public function destroy(Request $request, $id){
        $acceptHeader = $request->header('Accept');
        $hotel = Hotel::find($id);

        if (!$hotel) {
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
            $hotel->delete();
                
            $response = [
                'message' => 'Deleted Successfully!',
                'user_id' => $id
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<hotel/>');

                $xml->addChild('message', 'Deleted Successfully!');
                $xml->addChild('hotel_id', $id);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
	}
}

?>