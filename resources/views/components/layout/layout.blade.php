@props(['title', 'user_name'])

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <title>{{ $title }}</title>
    </head>
    <body class="container mx-auto py-4 px-4">
        @unless (request()->routeIs('login'))
        <header>
            <ul class="flex gap-4">
                @guest
                    <li><a href="{{route('login') }}" class="text-blue-600 hover:underline">ログイン</a></li>
                @endguest
                @if ($user_name == 'jonio')
                    <li><a href="{{ route('fx.create') }}" class="text-blue-600 hover:underline">値を登録する</a></li>
                    <li><a href="{{ route('fx.admin') }}" class="text-blue-600 hover:underline">管理画面</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:underline">ログアウト</button>
                        </form>
                    </li>
                @endif
                



            </ul>
        </header>
        @endunless
        <div id="app">
            {{ $slot }}
        </div>
    </body>
</html>