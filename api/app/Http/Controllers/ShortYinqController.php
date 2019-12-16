<?php

namespace App\Http\Controllers;

use \App\Models\Link;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ShortYinqController extends Controller
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

	public function redirectLink(Request $req)
	{
		$idURL = $req->url;
		
		try {	
			$realURL = $this->countHit($idURL);
			return redirect($realURL);
		} catch (\Throwable $th) {
			return response()->json($this->response(false, $th));	
		}
	}

	public function automaticShortLink(Request $req)
	{
		$realURL = $req->input("url");
		$idURL = base_convert(microtime(false), 6, 36);
		$data = [
			"userId" => NULL,
			"idURL" => $idURL,
			"realURL" => $realURL,
			"hit" => 0,
			"status" => NULL
		];

		try {
			$this->saveURL($data);
			return response()->json($this->response(true, $this->domain().$idURL));		
		} catch (\Throwable $th) {
			return response()->json($this->response(false, $th->errorInfo));		
		}
	}
	
	private function domain()
	{
		$hostName = $_SERVER['HTTP_HOST']; 
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
		$directURL = $protocol.'://'.$hostName."/";

		return $directURL;
	}

	private function countHit($idURL)
	{
		$l = Link::where('idURL', $idURL)->first();
		$l->countHit += 1 ;
		$l->save();

		return $l->realURL;
	}

	private function saveURL($data)
	{
		$link = new Link;
		$link->userId = $data["userId"];
		$link->idURL = $data["idURL"];
		$link->realURL = $data["realURL"];
		$link->countHit = $data["hit"];
		$link->status = $data["status"];
		$link->save();
	}

	private function response($success, $message)
  {
    return [
      "success" => $success,
      "message" => $message
    ];
	}

}
