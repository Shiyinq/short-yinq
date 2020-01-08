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

	private function generateId()
	{
		return base_convert(microtime(false), 6, 36);
	}

	public function redirectLink(Request $req)
	{
		$idURL = $req->url;
		
		try {	
			$realURL = $this->countHit($idURL);
			return redirect($realURL);
		} catch (\Throwable $th) {
			return response()->json(["error" => "URL Not Found"],404);	
		}
	}

	public function automaticShortenerURL(Request $req)
	{
		$user = $this->me($req);
		$realURL = $req->input("url");
		$idURL = $this->generateId();
		$data = [
			"userId" => $user ? $user->id: NULL,
			"idURL" => $idURL,
			"realURL" => $realURL,
			"hit" => 0,
			"status" => NULL
		];

		try {
			$this->createURL($data);
			return response()->json(["url" => $this->host().$idURL],201);		
		} catch (\Throwable $th) {
			return response()->json(["error" => "Can't create url"],400);		
		}
	}
	
	public function customShortenerURL(Request $req)
	{
		$this->validate($req, [
			'url' => 'required',
			'idURL' => 'required|unique:links'
		],[
			'idURL.unique' => 'The URL has already been taken' 
		]);

		$userId = $this->me($req);
		$existIdURL = $req->input("id");
		$customURL = $req->input("idURL");
		$realURL = $req->input("url");

		try {
			$this->updateURL($existIdURL, $customURL);
			return response()->json(["url" => $this->host().$customURL],201);		
		} catch (\Throwable $th) {
			return response()->json(["error" => "Can't create url"],400);		
		}
	}


	private function host()
	{
		$hostName = $_SERVER['HTTP_HOST']; 
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
		$domain = $protocol.'://'.$hostName."/";

		return $domain;
	}

	private function me($req)
	{
		return $req->user();
	}

	private function countHit($idURL)
	{
		$l = Link::where('idURL', $idURL)->first();
		$l->countHit += 1 ;
		$l->save();

		return $l->realURL;
	}
	
	public function listURL(Request $req)
	{
		$userId = $this->me($req)->id;

		try {
			$newDatas = [];
			$urls = Link::where('userId', $userId)->get();
			
			foreach ($urls as $url) {
				array_push($newDatas,[
					"id"=> $url->id,
					"idURL"=> $url->idURL,
					"shortener" => $this->host().$url->idURL,
					"realURL"=> $url->realURL,
					"countHit"=> $url->countHit,
					"status"=> $url->status,
					"created_at"=> $url->created_at,
					"updated_at"=> $url->updated_at
				]);
			}
			
			return response()->json($newDatas);
		} catch (\Throwable $th) {
			return response()->json(["error" => "Failed get data"],400);
		}
	}
	
	private function updateURL($idURL, $customURL)
	{
		$l = Link::where('idURL', $idURL)->first();
		$l->idURL = $customURL;
		$l->save();
	}

	private function createURL($data)
	{
		$link = new Link;
		$link->userId = $data["userId"];
		$link->idURL = $data["idURL"];
		$link->realURL = $data["realURL"];
		$link->countHit = $data["hit"];
		$link->status = $data["status"];
		$link->save();
	}

	public function deleteURL(Request $req)
	{
		$userId = $this->me($req)->id;
		$idURL = $req->id;

		try {
			$l = Link::where(['idURL' => $idURL, 'userId' => $userId]);
			if(count($l->get()) != 0){
				$l->delete();
				return response()->json(["message" => "Success deleted"], 200);
			}		
			return response()->json(["message" => "URL Not Found"], 400);
		} catch (\Throwable $th) {
			return response()->json(["error" => "Failed to delete data"],500);
		}
	}
	
}
