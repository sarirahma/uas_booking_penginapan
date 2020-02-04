<?php
namespace App\Http\Controllers;

use App\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BillController extends Controller {
    public function index(Request $request) {
        $acceptHeader = $request->header('Accept');
        $id = Auth::user()->user_id;

        if (Gate::allows('admin')) {
            $bill = Bill::OrderBy("bill_id", "DESC")->paginate(10)->toArray();
        } else {
            $bill = Bill::Where(['user_id' => $id])->OrderBy("bill_id", "DESC")->paginate(2)->toArray();
        }

        if (!$bill) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Data not Found!'
            ], 404);
        }

         if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $response = [
                "total_count" => $bill["total"],
                "limit" => $bill["per_page"],
                "pagination" => [
                    "next_page" => $bill["next_page_url"],
                    "current_page" => $bill["current_page"]
                ],
                "data" => $bill["data"],
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<Bill/>');

                $xml->addChild('total_count', $bill['total']);
                $xml->addChild('limit', $bill['per_page']);
                $pagination = $xml->addChild('pagination');
                $pagination->addChild('next_page', $bill['next_page_url']);
                $pagination->addChild('current_page', $bill['current_page']);
                $xml->addChild('total_count', $bill['total']);

                foreach ($bill['data'] as $item) {
                    $xmlItem = $xml->addChild('bill');

                    $xmlItem->addChild('bill_id', $item['bill_id']);
                    $xmlItem->addChild('reservation_id', $item['reservation_id']);
                    $xmlItem->addChild('user_id', $item['user_id']);
                    $xmlItem->addChild('total', $item['total']);
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

        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $input = $request->all();

            $validationRules = [
                'reservation_id' => 'required|exists:reservations',
                'total' => 'required'
            ];

            $validator = Validator::make($input, $validationRules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $bill= new Bill;
            $bill->user_id = Auth::user()->user_id;
            $bill->reservation_id = $request->input('reservation_id');
            $bill->total = $request->input('total');
            $bill->save();
            return response()->json($bill, 200);
        } else {
            return response('Not Acceptable!', 406);
        }
	}

    public function show(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $bill = Bill::find($id);
        
        if (Gate::denies('admin')) {
            if ($bill->user_id != Auth::user()->user_id) {
                return response()->json([
                    'success' => false,
                    'status' => 403,
                    'message' => 'You are Unauthorized'
                ], 403);
            }
        }

        if (!$bill) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

    
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if ($acceptHeader === 'application/json') {
                return response()->json($bill, 200);
            } else {
                $xml = new \SimpleXMLElement('<Bill/>');

                $xml->addChild('bill_id', $bill['bill_id']);
                $xml->addChild('reservation_id', $bill['reservation_id']);
                $xml->addChild('user_id', $bill['user_id']);
                $xml->addChild('total', $bill['total']);
                $xml->addChild('created_at', $bill['created_at']);
                $xml->addChild('updated_at', $bill['updated_at']);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    public function update(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $contentTypeHeader = $request->header('Content-Type');
        
        $bill = Bill::find($id);

        if (!$bill) {
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

        $input = $request->all();

        $validationRules =[
            'reservation_id' => 'required|exists:reservations',
            'total' => 'required'
        ];
        
        $validator = Validator::make($input,$validationRules);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if ($contentTypeHeader === 'application/json' || $contentTypeHeader === 'application/xml') {

                $bill->fill($input);
                $bill->save();
                if ($acceptHeader === 'application/json' && $contentTypeHeader === 'application/json') {
                    return response()->json($bill, 200);
                } else if ($acceptHeader === 'application/xml' && $contentTypeHeader === 'application/xml') {
                    $xml = new \SimpleXMLElement('<Bill/>');

                    $xml->addChild('bill_id', $bill['bill_id']);
                    $xml->addChild('reservation_id', $bill['reservation_id']);
                    $xml->addChild('user_id', $bill['user_id']);
                    $xml->addChild('total', $bill['total']);
                    $xml->addChild('created_at', $bill['created_at']);
                    $xml->addChild('updated_at', $bill['updated_at']);
                    
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
        $bill = Bill::find($id);
        
        if(!$bill) {
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
            $bill->delete();
            $response = [
                'message' => 'Deleted Successfully!',
                'bill_id' => $id
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<Bill/>');

                $xml->addChild('message', 'Deleted Successfully!');
                $xml->addChild('bill_id', $id);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }
}
