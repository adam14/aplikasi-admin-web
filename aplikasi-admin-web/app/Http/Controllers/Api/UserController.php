<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Helpers\Api;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth, Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function __construct()
    {
        $this->status = "true";
        $this->data = [];
        $this->errorMsg = null;
    }

    public function index(Request $request) {
        try {
            $query = User::paginate();

            if ($request->has('limit')) {
                $query = User::paginate($request->limit);
            }

            $this->data = $query;
        } catch (JWTException $e) {
            $this->status = 'false';
            $this->errorMsg = $e->getMessage();
        }

        return response()->json(Api::format($this->status, $this->data, $this->errorMsg), 200);
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only(['username', 'password']);
            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                return response()->json(Api::format('false', $this->data, 'Username or Password not match.'), 200);
            }
        } catch (JWTException $e) {
            return response()->json(Api::format('false', $this->data, $e->getMessage()), 200);
        }

        $user = Auth::user();
        $name = $user->fullname;
        $id = $user->id;

        return response()->json(Api::format($this->status, compact('token', 'name', 'id'), $this->errorMsg), 200);
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'fullname' => 'required',
                'password' => 'required',
                'password_confirmation' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            if ($request->password != $request->password_confirmation) {
                return response()->json(Api::format(false, $this->data, 'Confirm password not match'), 200);
            }

            $data_post = [
                'username' => $request->get('username'),
                'fullname' => $request->get('fullname'),
                'password' => Hash::make($request->get('password')),
                'level_id' => 2,
                'is_active' => 1
            ];

            $result = User::create($data_post);

            if ($result) {
                $param = [
                    'name' => $request->fullname,
                    'username' => $request->username
                ];
            }

            $this->data = $result;
        } catch (\Exception $e) {
            $this->status = "false";
            $this->errorMsg = $e->getMessage();
        }

        return response()->json(Api::format($this->status, $this->data, $this->errorMsg), 201);
    }

    public function detail($id)
    {
        try {
            $query = User::find($id);

            $this->data = $query;
        } catch (JWTException $e) {
            $this->status   = "false";
            $this->errorMsg = $e->getMessage();
        }

        return response()->json(Api::format($this->status, $this->data, $this->errorMsg), 200);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('user'));
    }
}