<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        // Validation
        $this->validate($request, [
            'full_name' => 'required|string|min:5',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'phone_number' => 'required',
            'role' => 'required|in:admin,guest'
        ]);

        $input = $request->all();
        $acceptHeader = $request->header('Accept');

        // Validation Starts
        $validationRules = [
            'full_name' => 'required|string|min:5',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'phone_number' => 'required',
            'role' => 'required|in:admin,guest'
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        // Validation Ends

        $user = new User;
        $user->full_name = $request->input('full_name');
        $user->email = $request->input('email');
        $plainPassword = $request->input('password');
        $user->password = app('hash')->make($plainPassword);
        $user->role = $request->input('role');
        $user->phone_number = $request->input('phone_number');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $user->save();

            // Response Accept : 'application/json'
            if ($acceptHeader === 'application/json') {
                return response()->json($user, 200);
            }

            // Response Accept : 'application/xml'
            else {
                $xml = new \SimpleXMLElement('<User/>');

                $xml->addChild('Full Name', $user->full_name);
                $xml->addChild('Email', $user->email);
                $xml->addChild('Password', $user->password);
                $xml->addChild('Phone Number', $user->phone_number);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 200);
        }
    }

    /**
     * Get a JWT via given credentials.
     * 
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        $input = $request->all();
        $acceptHeader = $request->header('Accept');

        $validationRules = [
            'email' => 'required|string',
            'password' => 'required|string',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            # code...
            return response()->json($validator->errors(), 400);
        }

        $credentials = $request->only(['email', 'password']);
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if (!$token = Auth::attempt($credentials)) {
                # code...
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $response = [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60
            ];

            // Response Accept : 'application/json'
            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            }

            // Response Accept : 'application/xml'
            else {
                $xml = new \SimpleXMLElement('<Response/>');

                $xml->addChild('token', $response['token']);
                $xml->addChild('token_type', $response['token_type']);
                $xml->addChild('expires_in', $response['expires_in']);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 403);
        }
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        $acceptHeader = $request->header('Accept');
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $response = [
                'status' => 'success',
                'message' => 'logout'
            ];

            Auth::logout();

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<Response/>');

                $xml->addChild('status', $response['status']);
                $xml->addChild('message', $response['message']);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 403);
        }
    }
}
