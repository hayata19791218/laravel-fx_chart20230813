<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Value;

class ChartController extends Controller
{
    /**
    * チャートを表示するページ
    */
    public function index(Value $value, Request $request)
    {
        $chartData = $value->valueShow($request);
        
        return view('fx.chart', compact('chartData'));
    }

    /**
    * チャートに表示する値を登録するページ
    */
    public function create(){
        return view('fx.create');
    }

    /**
    * チャートに表示する値を登録するメソッド
    */
    public function store(Value $value, Request $request){
        $value->valueStore($request);

        return back()->with('message', '登録が完了しました');
    }
}
