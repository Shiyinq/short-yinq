<?php

namespace App\Http\Controllers;

use \App\Models\User;
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

  private function validation($req)
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
  }

  private function response($success, $message)
  {
    return [
      "success" => $success,
      "message" => $message
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
        return response()->json($this->response(true,$this->createToken($username)));
      }else {
        return response()->json($this->response(false,"Username Or Password Failed"));
      }
    } catch (\Throwable $th) {
      return response()->json($this->response(false,$th->errorInfo));
    }   

  }

  public function register(Request $req)
  {
    $this->validation($req);

    $email = $req->input("email");
    $username = $req->input("username");
    $password = $req->input("password");

    try {
      $user = new User;
      $user->email = $email;
      $user->username = $username;
      $user->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 14]);
      $user->save();
      
      return response()->json($this->response(true,"Register success"));
    } catch (\Throwable $th) {
      return response()->json($this->response(false,$th->errorInfo));
    }

  }

    /**
   * Who.
   * 
   * @param \Illuminate\Http\Request $request
   */
  public function me(Request $request)
  {
    return $request->user();
  }

  /**
   * Refresh token.
   * 
   * @param \Illuminate\Http\Request $request
   */
  public function refresh(Request $request) 
  {
    $user = $request->user();

    return $this->createToken($user->username);
  }

  /**
   * Generate token.
   * 
   * @param string $username
   * @return string JWT token
   */
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
      'exp' => $expired,          // expired after
      'nbf' => time(),            // not used before
      'usr' => $username,         // username
    ], env('JWT_KEY'));

    return [
      'token' => $jwt,
      'expired' => $expired,
      'refresh' => $refresh,
    ];
  }

}
