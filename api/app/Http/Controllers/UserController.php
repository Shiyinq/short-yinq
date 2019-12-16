<?php

namespace App\Http\Controllers;

use \App\Models\User;
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

  public function login(Requesst $req)
  {
    $this->validate($req, [
      'username'=> 'required',
      'password'=> 'required'
    ]);

    $username = $req->input("username");
    $password = $req->input("password");
    
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
      return response()->json($this->response(true,$th->errorInfo));
    }

  }

}
