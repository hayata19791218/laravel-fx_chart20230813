<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/reset.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/value.css') }}" rel="stylesheet" type="text/css">
    <meta name=”robots” content=”noindex” />
    <title>値の編集</title>
</head>
<body>
    <div class="container">
        <h1>値の編集</h1>
        @if(session('message'))
            <p class="session">{{ session('message') }}</p>
        @endif
        <form action="{{ route('fx.update', ['value' => $valueEdit]) }}" method="post">
            @csrf
            @method('put')
            <div class="high-value">
                <input type="number" name="high_value" step="0.001" placeholder="最高値" value="{{ old('high_value', $valueEdit->high_value) }}">
            </div>
            <div class="row-value">
                <input type="number" name="row_value" step="0.001" placeholder="最低値" value="{{ old('row_value', $valueEdit->row_value) }}">
            </div>
            <div class="date">
                <input type="date" name="date" value="{{ old('date', $valueEdit->date) }}">
            </div>
            <div class="submit">
                <input type="submit" value="更新する">
            </div>
        </form>
        <a href="/" class="back">チャートのページに戻る</a>
    </div>
</body>
</html>