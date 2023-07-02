<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Enquete;
use App\Models\EnqueteItem;
use App\Models\EnqueteTemplate;
use App\Models\Client;
use App\Models\EnqueteRequest;
use App\Models\EnqueteResponse;

class EnqueteController extends Controller
{
	// アンケートテンプレート一覧
	public function template_index()
	{
		$templates = EnqueteTemplate::all();
		return response()->json(
			['templates'=>$templates],
			200
		);
	}

	// アンケートテンプレート登録
	public function template_create(Request $request)
	{
		$template = new EnqueteTemplate();
		$template->name = $request->name;
		$template->type = $request->type;
		$template->title = $request->title;
		$template->max_length = $request->max_length;
		$template->items = $request->items;
		$template->created_at = now();
		$template->updated_at = now();
		$template->save();
		return response()->json(
			['message'=>'Create Success', 'status'=>'200'],
			200
		);
	}

	// アンケート一覧
	public function enquete_index()
	{
		$enquetes = Enquete::all();
		return response()->json(
			['enquetes'=>$enquetes],
			200
		);
	}

	// アンケート登録
	public function enquete_create(Request $request)
	{
		DB::beginTransaction();
		try {
			$enquete = new Enquete();
			$enquete->name = $request->name;
			$enquete->description = $request->description;
			$enquete->created_at = now();
			$enquete->updated_at = now();
			$enquete->save();

			$cnt = 0;
			foreach($request->items as $item){
				$enquete_item = new EnqueteItem();
				$enquete_item->enquete_id = $enquete->id;
				$cnt++;
				$enquete_item->no = $cnt;
				$enquete_item->enquete_template_id = $item['enquete_template_id'];
				$enquete_item->type = $item['type'];
				$enquete_item->title = $item['title'];
				$enquete_item->max_length = $item['max_length'];
				$enquete_item->items = $item['items'];
				$enquete_item->created_at = now();
				$enquete_item->updated_at = now();
				$enquete_item->save();
			}

			DB::commit();

			return response()->json(
				['message'=>'Create Success', 'status'=>'200'],
				200
			);
	
		} catch (\Exception $e) {
			DB::rollback();

			return response()->json(
				['errorinfo'=>$e, 'status'=>'500'],
				500
			);
		}
	}

	// 宛先一覧
	public function client_index()
	{
		$clients = Client::all();
		return response()->json(
			['clients'=>$clients],
			200
		);
	}

	// 宛先登録
	public function client_create(Request $request)
	{
		$client = new Client();
		$client->company = $request->company;
		$client->division = $request->division;
		$client->person = $request->person;
		$client->email = $request->email;
		$client->created_at = now();
		$client->updated_at = now();
		$client->save();
		return response()->json(
			['message'=>'Create Success', 'status'=>'200'],
			200
		);
	}

	// アンケート依頼一覧
	public function request_index()
	{
		$requests = DB::table('enquete_requests')
			->select('clients.company', 'clients.division', 'clients.person', 'clients.email', 'enquetes.name', 'enquetes.description', 'enquete_requests.*')
			->leftJoin('clients', 'enquete_requests.client_id', '=', 'clients.id')
			->leftJoin('enquetes', 'enquete_requests.enquete_id', '=', 'enquetes.id')
			->get();
		return response()->json(
			['requests'=>$requests],
			200
		);
	}

	// アンケート依頼登録
	public function request_create(Request $request)
	{
		$enquete = new EnqueteRequest();
		$enquete->client_id = $request->client_id;
		$enquete->enquete_id = $request->enquete_id;
		$enquete->enquete_code = $request->enquete_code;
		$enquete->requested_date = now();
		$enquete->created_at = now();
		$enquete->updated_at = now();
		$enquete->save();
		return response()->json(
			['message'=>'Create Success', 'status'=>'200'],
			200
		);
	}

	// アンケート入力ログイン
	public function enquete_signin(Request $request)
	{
		try {
			$enquete = DB::table('enquete_requests')
			->select('clients.company', 'clients.division', 'clients.person', 'clients.email', 'enquetes.name', 'enquetes.description', 'enquete_requests.*')
			->join('clients', 'enquete_requests.client_id', '=', 'clients.id')
			->join('enquetes', 'enquete_requests.enquete_id', '=', 'enquetes.id')
			->where('clients.email', $request->email)
			->where('enquete_requests.enquete_code', $request->enquete_code)
			->first();
			if(!is_null($enquete)){
				if(empty($enquete->response_date)){
					$items = DB::table('enquete_items')
						->select('enquete_items.*')
						->where('enquete_items.enquete_id', $enquete->enquete_id)
						->get();
					return response()->json(
						['enquete'=>$enquete, 'items'=>$items, 'status'=>'normal'],
						200
					);
				} else {
					return response()->json(
						['status'=>'answered'],
						202
					);		
				}
			} else {
				return response()->json(
					['status'=>'unauthenticated'],
					202
				);	
			}
		} catch (\Exception $e) {
			return response()->json(
				['errorinfo'=>$e, 'status'=>'exception'],
				202
			);
		}
	}

	// アンケート回答
	public function enquete_response(Request $request)
	{
		$enquete = EnqueteRequest::find($request->request_id);
		if(empty($enquete->response_date)){
			DB::beginTransaction();
			try {
				$enquete->response_date = now();
				$enquete->save();
	
				foreach($request->items as $item){
					$response = new EnqueteResponse();
					$response->request_id = $request->request_id;
					$response->enquete_item_id = $item['enquete_item_id'];
					$response->value = $item['value'];
					$response->save();
				}

				DB::commit();
				
				return response()->json(
					['message'=>'Create Success', 'status'=>'200'],
					200
				);
			} catch (\Exception $e) {
				DB::rollback();

				return response()->json(
					['errorinfo'=>$e, 'status'=>'500'],
					500
				);
			}
		} else {
			return response()->json(
				['message'=>'Already Create', 'status'=>'202'],
				202
			);
		}
	}
	
	// アンケート詳細
	public function response_show($id)
	{
//		$response = EnqueteRequest::find($id);
//		$items = DB::table('enquete_responses')
//			->select('enquete_items.*', 'enquete_responses.value')
//			->join('enquete_items', 'enquete_responses.enquete_item_id', '=', 'enquete_items.id')
//			->where('enquete_responses.request_id', $id)
//			->get();
//		return response()->json(
//			['response'=>$response, 'items'=>$items],
//			200
//		);
		$enquete = DB::table('enquete_requests')
			->select('clients.company', 'clients.division', 'clients.person', 'clients.email', 'enquetes.name', 'enquetes.description', 'enquete_requests.*')
			->join('clients', 'enquete_requests.client_id', '=', 'clients.id')
			->join('enquetes', 'enquete_requests.enquete_id', '=', 'enquetes.id')
			->where('enquete_requests.id', $id)
			->first();
		if(!is_null($enquete)){
			$items = DB::table('enquete_items')
				->select('enquete_items.*', 'enquete_responses.value')
				->join('enquete_responses', 'enquete_items.id', '=', 'enquete_responses.enquete_item_id')
				->where('enquete_items.enquete_id', $enquete->enquete_id)
				->get();
			return response()->json(
				['enquete'=>$enquete, 'items'=>$items, 'status'=>'normal'],
				200
			);
		} else {
			return response()->json(
				['status'=>'no data'],
				202
			);	
		}
	}


}
