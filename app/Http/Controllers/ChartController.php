<?php

namespace App\Http\Controllers;

use App\Models\Value;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class ChartController extends Controller
{
    /**
     * チャートを表示
     * @return View
    */
    public function index(Value $value, Request $request) : View
    {
        $chartData = $value->valueShow($request);
        $title = 'チャート表';
        $user_name = null;

        if (Auth::check()) {
            $user_name = Auth::user()->name;
        }
    
        return view('fx.chart', compact('chartData', 'user_name', 'title'));
    }

    /**
    * チャートに表示する値を登録するページ
    * @return View
    */
    public function create() : View
    {
        return view('fx.create');
    }

    /**
    * 値を登録
    * @return JsonResponse;
    */
    public function store(Value $value, Request $request) : JsonResponse
    {
        $value->valueStore($request);

        return response()->json([
            'message' => '登録が完了しました'
        ]);
    }

    /**
    * 管理画面
    * @return View
    */
    public function admin(Value $value, Request $request) : View
    {
        $editDatas = $value->valueAdmin($request);

        return view('fx.admin',compact('editDatas'));
    }

    public function list(Value $value, Request $request)
    {
        $editDatas = $value->valueAdmin($request);

        return response()->json($editDatas);
    }

    public function saveMemo(Request $request, Value $value)
    {
        $value->memoValueStore($request);

        return response()->json(['message' => 'メモが保存されました']);
    }

    /**
    * 値の編集ページ
    */
    public function edit($id){
        $valueEdit = Value::find($id);

        return view('fx.edit', compact('valueEdit', 'id'));
    }

    /**
    * 値の更新
    */
    public function update(Value $value, Request $request){
        $value->valueStore($request);
        // return back()->with('message', '値の更新が完了しました');

        return response()->json([
            'status' => 'success',
            'message' => '更新しました'
        ]);
    }

     /**
     * Vue用 - 値データの取得
     */
    public function getEditValue($id)
    {
        $valueEdit = Value::find($id);

        if (!$valueEdit) {
            return response()->json(['message' => 'データが見つかりませんでした'], 404);
        }

        return response()->json($valueEdit);
    }

    /**
     * メモのデータを取得
    */
    public function getMemo(Request $request, Value $value)
    {
        $memo = $value->getMemoData($request);

        return response()->json(['memo' => $memo ?? ''], 200);
    }

    /**
    * 値の削除
    */
    public function delete(Value $value, Request $request){
        $value->valueDelete($request);
        
        return response()->json([
            'status' => 'success',
            'message' => '削除しました'
        ]);
    }

    /**
     * 単純移動平均線の値をテンプレートに渡す
    */
    public function getSma(Request $request)
    {
        $days = (int) $request->input('days');
        
        $sma = Value::calculateSmaSeries($days);

        return response()->json([
            'days' => $days,
            'sma' => $sma
        ]);
    }
}