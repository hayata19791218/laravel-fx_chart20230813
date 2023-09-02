<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="{{ asset('css/reset.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('css/index.css') }}" rel="stylesheet" type="text/css">
        <meta name=”robots” content=”noindex” />
        <title>チャート表</title>
    </head>
    <body>
        <div class="chart-container">
            <h1>ドル円のチャート表</h1>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>
            <canvas id="myChart" ></canvas>
            <script>
                let data = @json($chartData);
                var ctx = document.getElementById('myChart').getContext('2d');
                var myChart = new Chart(ctx, {
                    data: {
                        labels: data.dates,
                        datasets: [
                            {
                                type: 'line',
                                label: '最高値',
                                data: data.values,
                                backgroundColor: 'transparent',
                                borderColor: 'red',
                                pointBorderColor: 'red',
                                pointBorderWidth: 2,
                                pointStyle: 'rect',
                                borderWidth: 1,
                                lineTension: 0
                            },
                            {
                                type: 'line',
                                label: '最低値',
                                data: data.values2,
                                backgroundColor: 'transparent',
                                borderColor: 'blue',
                                pointBorderColor: 'blue',
                                pointBorderWidth: 2,
                                pointStyle: 'rect',
                                borderWidth: 1,
                                lineTension: 0
                            },
                        ]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {     
                                    beginAtZero: true,
                                    max: 151,
                                    min: 140
                                }
                            }],
                            xAxes: [{
                                type: 'time',
                                time: {
                                    unit: 'day',
                                    displayFormats: {
                                        day: 'YY/MM/DD'
                                    }
                                },
                                ticks: {
                                    autoSkip: true,
                                    maxTricksLimit: 1
                                }
                            }],
                        },
                        maintainAspectRatio: false
                    }
                });
            </script>
            <a href="{{ route('fx.create') }}" class="create">値を登録する</a>
            <a href="{{ route('fx.admin') }}" class="admin">管理画面</a>
        </div>
    </body>
</html>