<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DawoodShahid\GridView\GridView;

class HomeController extends Controller
{
    public function getData(Request $request, GridView $grid)
    {
        $response = Http::get("https://dummyjson.com/products");
        $response = $response->json()['products'];

        $gridView = $grid->data($response)
        ->config([
            'pageSize' => 16
        ])
        ->fields(
            [
                "title" => "Test",
                "price" => [
                    'label' => 'Price', // Column label.
                    'type' => 'float'
                ],
                'brand'=>[
                    'label'=>'Brand',
                    'options'=>['Apple'=>'A', 'Samsung'=>'S'],
                    'field'=>"case when gender = 'M' then 'Male' else 'Female' end"
                ],
            ]
        )
        ->searchableColumns(["brand"])
        ->sort('price', 'asc')
        ->page(2)
        //->getData();
        ->generateView();

        return view('welcome', ['gridView' => $gridView]);
    }


    public function serviceIsCalled($data)
    {
        //2: colum filtering
        dd($data);
    }



    public function sendSuccess($data)
    {
        return response()->json([
            "data" => $data,
        ]);
    }

    public function sendError($errorMessage, $data, $code)
    {
        return response()->json([
            "error" => $errorMessage,
            "data" => $data,
            "errorCode" => $code
        ], 402);
    }
}
