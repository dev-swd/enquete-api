<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloController extends Controller
{
	public function apiTest()
	{
		return response()->json(['data'=>'Hello World']);
	}

	public function postTest()
	{
		return response()->json(['data'=>'Hello Japan']);
	}
}
