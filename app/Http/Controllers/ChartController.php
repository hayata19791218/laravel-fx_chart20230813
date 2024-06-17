<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Value;
use Illuminate\Support\Facades\Auth;


class ChartController extends Controller
{
    /**
    * チャートを表示するページ
    */
    public function index(Value $value, Request $request)
    {
        $chartData = $value->valueShow($request);

        $user_name = null;

        if (Auth::check()) {
            $user_name = Auth::user()->name;
        }
        
        return view('fx.chart', compact('chartData', 'user_name'));
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

    /**
    * 管理画面
    */
    public function admin(Value $value, Request $request){
        $editDatas = $value->valueAdmin($request);

        return view('fx.admin',compact('editDatas'));
    }

    /**
    * 値の編集ページ
    */
    public function edit($id){
        $valueEdit = Value::find($id);
        return view('fx.edit', compact('valueEdit'));
    }

    /**
    * 値の更新
    */
    public function update(Value $value, Request $request){
        $value->valueStore($request);
        return back()->with('message', '値の更新が完了しました');
    }

    /**
    * 値の削除
    */
    public function delete(Value $value, Request $request){
        $value->valueDelete($request);
        
        return redirect()->route('fx.admin')->with('message', '値を削除しました');
    }
}