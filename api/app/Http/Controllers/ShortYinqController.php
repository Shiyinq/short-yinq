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
		$urlId = $req->url;
		
		try {	
			$realUrl = $this->count_hit($urlId);
			return redirect($realUrl);
		} catch (\Throwable $th) {
			return response()->json(["error" => "URL Not Found"],404);	
		}
	}

	public function automaticShortenerURL(Request $req)
	{
		$user = $this->me($req);
		$realUrl = $req->input("url");
		$urlId = $this->generateId();
		$data = [
			"user_id" => $user ? $user->id: NULL,
			"url_id" => $urlId,
			"real_url" => $realUrl,
			"hit" => 0,
			"status" => NULL
		];

		try {
			$this->createURL($data);
			return response()->json(["url" => $this->host().$urlId],201);		
		} catch (\Throwable $th) {
			return response()->json(["error" => "Can't create url"],400);		
		}
	}
	
	public function customShortenerURL(Request $req)
	{
		$this->validate($req, [
			'url_id' => 'required|unique:links'
		],[
			'url_id.unique' => 'The URL has already been taken' 
		]);

		$userId = $this->me($req);
		$existUrlId = $req->input("id");
		$customURL = $req->input("url_id");

		try {
			$this->updateURL($existUrlId, $customURL);
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

	private function count_hit($urlId)
	{
		$l = Link::where('url_id', $urlId)->first();
		$l->count_hit += 1 ;
		$l->save();

		return $l->real_url;
	}
	
	public function listURL(Request $req)
	{
		$userId = $this->me($req)->id;

		try {
			$newDatas = [];
			$urls = Link::where('user_id', $userId)->get();
			
			foreach ($urls as $url) {
				array_push($newDatas,[
					"id"=> $url->id,
					"url_id"=> $url->url_id,
					"shortener" => $this->host().$url->url_id,
					"real_url"=> $url->real_url,
					"count_hit"=> $url->count_hit,
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
	
	private function updateURL($urlId, $customURL)
	{
		$l = Link::where('url_id', $urlId)->first();
		$l->url_id = $customURL;
		$l->save();
	}

	private function createURL($data)
	{
		$link = new Link;
		$link->user_id = $data["user_id"];
		$link->url_id = $data["url_id"];
		$link->real_url = $data["real_url"];
		$link->count_hit = $data["hit"];
		$link->status = $data["status"];
		$link->save();
	}

	public function deleteURL(Request $req)
	{
		$userId = $this->me($req)->id;
		$urlId = $req->id;

		try {
			$l = Link::where(['url_id' => $urlId, 'user_id' => $userId]);
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
