<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/reset.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/value.css') }}" rel="stylesheet" type="text/css">
    <meta name=”robots” content=”noindex” />
    <title>値の登録</title>
</head>
<body>
    <div class="container">
        <h1>値の登録</h1>
        @if(session('message'))
            <p class="session">{{ session('message') }}</p>
        @endif
        <form action="{{ route('store') }}" method="post">
            @csrf
            <div class="high-value">
                <input type="number" name="high_value" step="0.001" placeholder="最高値">
            </div>
            <div class="row-value">
                <input type="number" name="row_value" step="0.001" placeholder="最低値">
            </div>
            <div class="date">
                <input type="date" name="date">
            </div>
            <div class="submit">
                <input type="submit" value="登録する">
            </div>
        </form>
    </div>
</body>
</html>