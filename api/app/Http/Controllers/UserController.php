<?php

namespace App\Http\Controllers;

use \App\Models\User;
use \App\Models\Link;
use \Firebase\JWT\JWT;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UserController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  public function refresh(Request $request) 
  {
    $user = $request->user();

    return $this->createToken($user->username);
  }

  private function createToken($username) 
  {
    $expired_in = \DateInterval::createfromdatestring('+14 day');
    $refresh_in = \DateInterval::createfromdatestring('+7 day');
    $dt = new \DateTime();

    $expired = $dt->add($expired_in)->format('U');
    $refresh = $dt->add($refresh_in)->format('U');

    $jwt = JWT::encode([
      'iss' => env('APP_NAME'),
      'aud' => 'short-yinq-yiyi',
      'exp' => $expired,
      'nbf' => time(),
      'usr' => $username,
    ], env('JWT_KEY'));

    return [
      'token' => $jwt,
      'expired' => $expired,
      'refresh' => $refresh,
    ];
  }
  
  public function login(Request $req)
  {
    $this->validate($req, [
      'username'=> 'required',
      'password'=> 'required'
    ]);

    $username = $req->input("username");
    $password = $req->input("password");

    try {
      $user = User::where('username', $username)->first();
      if(!empty($user->password) && password_verify($password, $user->password)) {
        return response()->json(["message" => $this->createToken($username)],200);
      }else {
        return response()->json(["error" => "Username Or Password wrong"],200);
      }
    } catch (\Throwable $th) {
      return response()->json(["error" => "Login failed"],400);
    }   

  }

  public function register(Request $req)
  {
    $this->validate($req, [
      'email' => 'required|unique:users|max:255',
      'username' => 'required|unique:users|max:15|min:3',
      'password' => 'required'
    ],[
      'email.unique' => 'Email already exist',
      'email.required' => 'You must enter your email',
      'username.required' => 'You must enter username',
      'username.unique' => 'Username already exist',
      'password.required' => 'your messages'
    ]);

    $email = $req->input("email");
    $username = $req->input("username");
    $password = $req->input("password");

    try {
      $user = new User;
      $user->email = $email;
      $user->username = $username;
      $user->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 14]);
      $user->save();
      
      return response()->json(["message" => "Register success"],201);
    } catch (\Throwable $th) {
      return response()->json(["error" => "Register failed"],400);
    }

  }

}
