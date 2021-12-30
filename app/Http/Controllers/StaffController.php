<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Passport\Client as OClient;
use GuzzleHttp\Client;
use App\OauthAccessToken;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

use Hash;
use Validator;
use Auth;
use DB;

class StaffController extends Controller
{
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    protected function register(Request $request)
    {
        $input = $request->all();

        $validation = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        if($validation->fails()){
            return response()->json(json_decode($validation->errors(), true));
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $input['password'] = Hash::make($input['password']);
            $input['remember_token'] = Str::random(10);
            $client = User::create($input);
            return response()->json(['status' => 'success', 'client' => $client], 200);
        } else {
            $response = ["message" =>'User already exist'];
            return response($response, 422);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                return response($response, 200);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" =>'User does not exist'];
            return response($response, 422);
        }

    }

    public function index()
    {
        $staffs = User::orderBy('id', 'asc')->get();

        if($staffs){
           return response()->json($staffs);
        }else{
            return response()->json([
                'status'=>'failure',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    public function show($id)
    {
        $staff = User::find($id);
        if ($staff) {
            return response()->json($staff);
        }
    }

	 public function update(Request $request)
    {
        $input = $request->all();
        $validation = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'max:255'],
            'old_password' => 'required|min:6',
            'new_password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);
        if($validation->fails()){
            return response()->json(json_decode($validation->errors(), true));
        }

        $input['user_id'] = Auth::id();
        $user = User::find($input['user_id']);

        if(Hash::check($input['old_password'], $user->password)){
            $input['new_password'] = Hash::make($input['new_password']);
            User::where('id', $user->id)->update([
                'password' => $input['new_password'],
                'email' => $input['email'],
                'name' => $input['name'],
              ]);
            return response()->json([
                'status' => 'success',
                    'message' => 'Password updated successfully'
                ]);
        }
        return response()->json([
            'status' => 'failure',
            'valid' => false,
            'message' => 'password does exist'
        ], 400);

    }
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();

        return response()->json([
           'status' => 'success',
           'user' => 'User Deleted successfully'
        ], 204);

    }
}

