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
        $conditions = array(
            'email' => $request->input('email'),
            'password' => $request->input('password')
        );

        if (auth()->guard('user')->attempt($conditions)) {

            config(['auth.guards.api.provider' => 'user']);

            $response['success'] = 'Successfully logged in';
            $response["user"] = User::findOrFail(auth()->guard('user')->user()->id);

            $oClient = OClient::where(
                [
                    'password_client'=> 1,
                    'provider' => 'users'
            ])->latest()->first();
            $body = [
                'grant_type' => 'password',
                'client_id' => $oClient->id,
                'client_secret' => $oClient->secret,
                'username' => request('email'),
                'password' => request('password'),
                'scope' => '*'
            ];

            $request = Request::create('/oauth/token', 'POST', $body);

            $result = $this->app->handle($request);
            
            $result = json_decode($result->getContent(), true);

            $response['token'] = $result['access_token'];
            $response['refresh_token'] = $result['refresh_token'];

            $clientName = auth()->guard('user')->user()->first_name.' '.auth()->guard('user')->user()->last_name;

            return response()->json([
                'token' => $response['token'],
                'refresh_token' => $response['refresh_token'],
            	'user' => auth()->guard('user')->user()
            ], 200);
        }else{
            return response()->json(['error' => ['Email and Password are Wrong.']], 200);
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
            'email' => ['required', 'string', 'max:255'],
            'old_password' => 'required|min:6',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);
        if($validation->fails()){
            return response()->json(json_decode($validation->errors(), true));
        }

        $input['user_id'] = Auth::id();
        $user = User::find($input['user_id']);

        if(Hash::check($input['old_password'], $user->password)){
            $input['password'] = Hash::make($input['password']);
            User::where('id', $user->id)->update([
                'password' => $input['password'],
                'email' => $input['email'],
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

