<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Auth\UserAuthController;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; 

class UserController extends Controller {
	public function index(Request $request){
        $acceptHeader = $request->header('Accept');
        
        if (Gate::denies('admin')) {
            $user = User::Where(['user_id' => Auth::user()->user_id])->OrderBy("user_id", "DESC")->paginate(1)->toArray();
        } else {
            $user = User::OrderBy("hotel_id", "DESC")->paginate(10)->toArray();
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
			if ($acceptHeader === 'application/json') {
                return response()->json($user, 200);
            } else {
				$xml = new \SimpleXMLElement('<Users/>');
				
                foreach ($user->items('data') as $item) {
                    $xmlItem = $xml->addChild('user');

					$xmlItem->addChild('user_id', $item->user_id);
					$xmlItem->addChild('role', $item->role);
                    $xmlItem->addChild('full_name', $item->full_name);
                    $xmlItem->addChild('email', $item->email);
                    $xmlItem->addChild('password', $item->password);
                    $xmlItem->addChild('phone_number', $item->phone_number);
                    $xmlItem->addChild('created_at', $item->created_at);
                    $xmlItem->addChild('updated_at', $item->updated_at);
				}
				
                return $xml->asXML();
            }
		} else {
			return response('not acceptable!', 406);
		}
	}

	public function show(Request $request, $id){
		$acceptHeader = $request->header('Accept');
        $user = User::find($id);

        if (!$user) {
			return response()->json([
				'success' => false,
				'status' => 404,
				'message' => 'Object not Found'
			], 404);
		}

		if (Gate::allows('admin') || Auth::user()->user_id == $id) {
			$user = User::find($id);
		} else {
			return response('You are Unauthorized', 403);
		}

		if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
			if ($acceptHeader === 'application/json') {
				return response()->json($user, 200);			
			} else {
				$xmlItem = new \SimpleXMLElement('<User/>');
                
                $xmlItem->addChild('user_id', $user->user_id);
				$xmlItem->addChild('role', $user->role);
                $xmlItem->addChild('full_name', $user->full_name);
                $xmlItem->addChild('email', $user->email);
                $xmlItem->addChild('password', $user->password);
                $xmlItem->addChild('phone_number', $user->phone_number);
                $xmlItem->addChild('created_at', $user->created_at);
                $xmlItem->addChild('updated_at', $user->updated_at);
	
				return $xmlItem->asXML();
			} 
		} else {
			return response('Not Acceptable!', 406);
		}
	}

	public function update(Request $request, $id){
		$acceptHeader = $request->header('Accept');
		$contentTypeHeader = $request->header('Content-Type');

        if (Gate::allows('admin') || Auth::user()->user_id == $id) {
			$user = User::find($id);
		} else {
			return response('You are Unauthorized', 403);
        }
        
        if (!$user) {
			return response()->json([
				'success' => false,
				'status' => 404,
				'message' => 'Object not Found'
			], 404);
		}
		
		$input = $request->all();
		
		if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
			if ($contentTypeHeader === 'application/json' || $contentTypeHeader === 'application/xml') {
				if (Auth::user()->user_id == $id) {
					$user->fill($input);

                    if ($acceptHeader === 'application/json' && $contentTypeHeader === 'application/json') {
						$user->save();
						return response()->json($user,200);
					} else if ($acceptHeader === 'application/xml' && $contentTypeHeader === 'application/xml') {
						$user->save();

						$xml = new \SimpleXMLElement('<User/>');

						$xml->addChild('user_id', $user->user_id);
                        $xml->addChild('role', $user->role);
                        $xml->addChild('full_name', $user->full_name);
                        $xml->addChild('email', $user->email);
                        $xml->addChild('password', $user->password);
                        $xml->addChild('phone_number', $user->phone_number);
                        $xml->addChild('created_at', $user->created_at);
                        $xml->addChild('updated_at', $user->updated_at);

						return $xml->asXML();
					} else {
						return response('Unsupported Media Type', 403);
					}
				} else {
					return response('You are Unauthorized!', 403);
				}
			} else {
				return response('Unsupported Media Type', 403);
			}
		} else {
			return response('Not Acceptable!', 406);
		}
	}

	public function destroy(Request $request, $id){
		$acceptHeader = $request->header('Accept');
        $user = User::find($id);
        
        if (!$user) {
			return response()->json([
				'success' => false,
				'status' => 404,
				'message' => 'Object not Found'
			], 404);
		}	

		if (Gate::allows('admin') || Auth::user()->user_id != $id) {
			return response()->json([
				'success' => false,
				'status' => 403,
				'message' => 'You are Unauthorized'
			], 403);
		}

		if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
			if ($id == Auth::user()->user_id) {
				$user->delete();

				$response = [
					'message' => 'Deleted Successfully!',
					'user_id' => $id
				];

				if ($acceptHeader === 'application/json') {
					return response()->json($response, 200);
				} else {
					$xml = new \SimpleXMLElement('<User/>');

					$xml->addChild('message', 'Deleted Successfully!');
					$xml->addChild('user_id', $id);

					return $xml->asXML();
				}
			} else {
				return response('You are unauthorized', 403);
			}
		} else {
			return response('Not Acceptable!', 406);
		}
	}
}

?>