<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class Value extends Model
{
    use HasFactory;

    /**
    * チャートに表示する値の登録
    */
    function valueShow($request){
        // $data = Value::all();

        $data = DB::table('values')->get();
        $chartData = [
            'values' => $data->pluck('high_value'),
            'values2' => $data->pluck('row_value'),
            'dates' => $data->pluck('date')
        ];

        return $chartData;
    }

    /**
    * チャートに表示する値の保存
    */
    function valueStore($request){
        $inputs = $request->validate([
            'high_value' => 'required',
            'row_value' => 'required',
            'date' => 'required'
        ]);
        
        $this->high_value = $inputs['high_value'];
        $this->row_value = $inputs['row_value'];
        $this->date = $inputs['date'];
        $this->save();
    }

    /**
    * チャートの値の表示
    */
    function valueAdmin($request){
        $value = Value::all();
        return $value;
    }

    /**
    * 値の削除
    */
    function valueDelete($request){
        $this->delete();
    }
}
