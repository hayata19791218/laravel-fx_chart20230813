<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/reset.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet" type="text/css">
    <title>管理画面</title>
</head>
<body>
    <div class="container">
        <h1>管理画面</h1>
        @if(session('message'))
        <p class="message">{{ session('message') }}</p>
        @endif
        <table>
            <tr>
                <th>日付</th>
                <th>最高値</th>
                <th>最安値</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($editDatas as $editData)
            <tr id="row">
                <td>{{ $editData->date }}</td>
                <td>{{ $editData->high_value }}</td>
                <td>{{ $editData->row_value }}</td>
                <td><a href="{{ route('fx.edit', ['id' => $editData->id]) }}">編集</a></td>
                <td>
                    <form method="post" action="{{ route('fx.delete', ['value' => $editData]) }}">
                        @csrf
                        @method('DELETE')
                        <button id="delete">削除</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </table>
        <a href="/" class="back">チャートのページに戻る</a>
        <a href="{{ route('fx.admin') }}" class="admin">管理画面</a>
    </div>
    <script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>