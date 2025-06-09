<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Value extends Model
{
    use HasFactory;

    protected $fillable = [
        'high_value',
        'row_value',
        'high_value_memo',
        'row_value_memo',
        'date',
        'close_value',
    ];

    /**
    * チャートに表示する値の登録
    */
    function valueShow($request){
        $data = DB::table('values')->get();
        $highMemos = $data->pluck('high_value_memo', 'date');
        $rowMemos = $data->pluck('row_value_memo', 'date');

        $chartData = [
            'highValue' => $data->pluck('high_value'),
            'rowValue' => $data->pluck('row_value'),
            'dates' => $data->pluck('date'),
            'highMemos' => $highMemos,
            'rowMemos' => $rowMemos,
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

    /**
     * メモの保存
    */
    public function memoValueStore($request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:high,row',
            'memo' => 'required|string',
        ]);

        $value = Value::firstOrCreate(['date' => $validatedData['date']]);

        if ($validatedData['type'] === 'high') {
            $value->high_value_memo = $validatedData['memo'];
        } else {
            $value->row_value_memo = $validatedData['memo'];
        }

        $value->save();
    }

    /**
     * メモの取得
    */
    public function getMemoData($request) {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:high,row',
        ]);
    
        $value = Value::where('date', $validatedData['date'])->first();
    
        if (!$value) {
            return response()->json(['memo' => ''], 200);
        }
    
        return  $validatedData['type'] === 'high' ? $value->high_value_memo : $value->row_value_memo;
    }

    /**
     * 単純移動平均線の値を取得
    */
    public static function calculateSmaSeries($days)
    {
        $values = Value::orderBy('date')
                       ->pluck('close_value')
                       ->toArray();

        $sma = [];

        for ($i = 0; $i < count($values); $i ++) {
            if ($i < $days - 1) {
                $sma[] = null;
            } else {
                $numericValues = array_map('floatval', $values);
                $sum = array_sum(array_slice($values, $i - $days + 1, $days));
                $sma[] = round($sum / $days, 3);
            }
        }

        return $sma;
    }
}
