<template>
    <div class="mb-6 value-wrap">
        <div>
            <div class="mb-2">[SMA日数]</div>
            <div class="my-4 flex items-center gap-4">
                <div class="flex gap-x-1 items-center">
                    <input
                        id="smaDays"
                        type="number"
                        v-model="selectSmaDays"
                        @change="fetchSma"
                        class="border p-1 rounded w-[60px]"
                        min="1"
                    />
                    <div>日</div>
                    <div class="purple"></div>
                </div>
            </div>
        </div>
        <div>
            <div>[表示期間]</div>
            <div class="my-4 flex items-center gap-2">
                <input 
                    type="date" 
                    v-model="startDate" 
                    class="border p-1 rounded" 
                />
                <span>〜</span>
                <input 
                    type="date" 
                    v-model="endDate" 
                    class="border p-1 rounded" 
                />
                <button
                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600"
                    @click="displayDateChange"
                >適用</button>
            </div>
        </div>

        <div class="mb-6">
            <div>[Y軸の範囲]</div>
            <div class="my-4 flex items-center gap-2">
                <label class="text-sm">最大:</label>
                <input 
                    type="number" 
                    v-model.number="yAxisMax" 
                    class="border p-1 rounded w-[90px]" 
                />
                <label class="text-sm">最小:</label>
                <input 
                    type="number" 
                    v-model.number="yAxisMin" 
                    class="border p-1 rounded w-[90px]" 
                />
                <button
                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600"
                    @click="yAxisDisplayValueChange"
                >適用</button>
            </div>
        </div>

    </div>
    <div>
        <div class="mb-2">[トレンドライン]</div>
        <div class="flex items-center gap-4 mb-6">
            <div>高値<span class="orange"></span></div>
            <div>安値<span class="green"></span></div>
            <div>中間<span class="gray"></span></div>
        </div>
    </div>

    <canvas
        ref="chartCanvas"
        :width="chartWidth"
        height="500"
    ></canvas>
    <div 
        v-if="momoModalFlg" 
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        @click.self="closeModal"
    >
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">{{ selectedDate }}の{{ selectedType === 'high' ? '最高値' : '最低値' }}のメモ</h3>
            <textarea 
                v-model="memoText" 
                rows="4" 
                class="w-full p-2 border border-gray-300 rounded resize-none mb-4 focus:outline-none focus:ring focus:border-blue-400"
            />
            <div class="flex justify-end space-x-4">
                <button 
                    @click="saveMemo"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
                >保存</button>
                <button 
                    @click="closeModal"
                    class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition"
                >キャンセル</button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref,nextTick } from 'vue';
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    TimeScale,
    Tooltip
} from 'chart.js'
import 'chartjs-adapter-date-fns';
import { format } from 'date-fns';
import { ja } from 'date-fns/locale';
import axios from 'axios';

interface ChartData {
    dates: string[];
    highValue: number[];
    rowValue: number[];
    highMemos?: Record<string, string>;
    rowMemos?: Record<string, string>;
}

const props = defineProps<{
    chartData: ChartData;
    isLoggedIn: boolean;
}>();

const chartCanvas = ref<HTMLCanvasElement | null>(null)
const chartWidth = ref<number>(Math.min(props.chartData.dates.length * 80, 7000))
const momoModalFlg = ref(false)
const selectedDate = ref('')
const memoText = ref('')
const highMemos = ref<Record<string, string>>({ ...props.chartData.highMemos })
const rowMemos = ref<Record<string, string>>({ ...props.chartData.rowMemos })
const selectedType = ref<'high' | 'row'>('high')
const selectSmaDays = ref(21)
const startDate = ref<string>('')
const endDate = ref<string>('')
const yAxisMax = ref<number>(153)
const yAxisMin = ref<number>(140)

let chartInstance: Chart<"line", { x:number | string; y: number | null }[]> | null = null;

// トレンドライン
const linearTrendLine = (x: string[], y: number[]) => {
    const n = y.length;
    const xNums = x.map(date => new Date(date).getTime());
    // const xNums = x.map((_, i) => i);
    const avgX = xNums.reduce((a, b) => a + b, 0) / n;
    const avgY = y.reduce((a, b) => a + b, 0) / n;
    const numerator = xNums.reduce((sum, xi, i) => sum + (xi - avgX) * (y[i] - avgY), 0);
    const denominator = xNums.reduce((sum, xi) => sum + (xi - avgX) ** 2, 0);
    const slope = numerator / denominator;
    const intercept = avgY - slope * avgX;   // 直線の傾き
    const trendLine = xNums.map(xi => slope * xi + intercept);   // y = ax + b
    // const trendLine = xNums.map(i => slope * i + intercept);   // y = ax + b

    return trendLine;
}

const yAxisDisplayValueChange = () => {
    if (!chartInstance) return

    const max = yAxisMax.value
    const min = yAxisMin.value

    if(min == null || max == null || Number.isNaN(min) || Number.isNaN(max) || min >= max) {
        alert('正しい値を入力してください')

        return
    }

    const y = chartInstance.options.scales?.y

    if (y) {
        y.max = max
        y.min = min
    }

    chartInstance.update('active')
}


const displayDateChange = () => {
    if (!chartInstance) return

    if (!startDate.value || !endDate.value) {
        alert('開始日と終了日を入力してください。')

        startDate.value = ''
        endDate.value = ''

        return;
    }

    const start = new Date(startDate.value)
    const end = new Date(endDate.value)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    start.setHours(0, 0, 0, 0)
    end.setHours(0, 0, 0, 0)
    const filteredDates: string[] = []
    const filteredHighs: number[] = []
    const filteredRows: number[] = []
    type XY = { x: string; y: number | null }
    const toXY = (dates: string[], ys: number[]): XY[] => dates.map((d, i) => ( { x: d, y: ys[i] ?? null }))

    if (start > today || end < start) {
        alert('正しい日付を選択してください。')

        startDate.value = ''
        endDate.value = ''

        return
    }

    props.chartData.dates.forEach((date, i) => {
        const currentDate = new Date(date);

        if (currentDate >= start && currentDate <= end) {
            filteredDates.push(date);
            filteredHighs.push(Number(props.chartData.highValue[i]))
            filteredRows.push(Number(props.chartData.rowValue[i]))
        }
    })

    const filteredMiddles = filteredHighs.map((v, i) => (v + filteredRows[i]) / 2)
    const highTrend = linearTrendLine(filteredDates, filteredHighs)
    const rowTrend = linearTrendLine(filteredDates, filteredRows)
    const middleTrend = linearTrendLine(filteredDates, filteredMiddles)

    chartInstance.data.labels = filteredDates
    chartInstance.data.datasets[0].data = toXY(filteredDates, filteredHighs)
    chartInstance.data.datasets[1].data = toXY(filteredDates, filteredRows)
    chartInstance.data.datasets[2].data = highTrend.map((y, i) => ({ x: filteredDates[i], y }))
    chartInstance.data.datasets[3].data = rowTrend.map((y, i) => ({ x: filteredDates[i], y }))
    chartInstance.data.datasets[4].data = middleTrend.map((y, i) => ({ x: filteredDates[i], y }))
    chartInstance.update()
}

// smaを更新する
const updateSmaLine = (smaData: number[]) => {
    if (!chartInstance) return;

    const smaPoints = props.chartData.dates.map((date, i) => ({
        x: date,
        y: smaData[i] ?? null
    }));

    const idx = chartInstance.data.datasets.findIndex(ds => ds.label === 'SMA');

    if (idx !== -1) {
        chartInstance.data.datasets[idx].data = smaPoints;
    } else {
        // 存在しない場合はSMAを追加、初期表示用
        chartInstance.data.datasets.push({
            label: 'SMA',
            data: smaPoints,
            borderColor: 'purple',
            pointStyle: 'circle',
            showLine: true,
            type: 'line',
            backgroundColor: 'purple',
            pointRadius: 3,
            borderWidth: 4, 
        });
    }

    // チャートの描画
    chartInstance.update();
};

const fetchSma = async () => {
    try {
        const res = await axios.get('/api/sma', {
            params: { days: selectSmaDays.value }
        });

        updateSmaLine(res.data.sma);
    } catch (e) {
        console.error('SMA取得失敗', e);
    }
};

// メモを入力するモーダル
const openMemoModal = async (date: string, type: 'high' | 'row') => {
    if (!props.isLoggedIn) {
        alert('ログインしてください')

        return
    }

    selectedDate.value = date
    selectedType.value = type

    try {
        const response = await axios.get(`/api/get-memo`, {
            params: { date, type }
        });

        memoText.value = response.data.memo || '';
    } catch (error) {
        console.error('メモの取得に失敗しました', error);
        alert('メモの取得に失敗しました');
        return;
    }

    momoModalFlg.value = true;
};

const closeModal = () => {
  momoModalFlg.value = false;
}

const saveMemo = async () => {
    try {

        // メモを新規登録か更新かのチェック
        const memoUpdateCheck = selectedType.value === 'high' ? !!highMemos.value[selectedDate.value] : !!rowMemos.value[selectedDate.value]

        await axios.post('/api/save-memo', {
            date: selectedDate.value,
            type: selectedType.value,
            memo: memoText.value,
        })

        if (selectedType.value === 'high') {
            highMemos.value[selectedDate.value] = memoText.value
        } else {
            rowMemos.value[selectedDate.value] = memoText.value
        }

        momoModalFlg.value = false

        alert(memoUpdateCheck ? 'メモを更新しました' : 'メモを保存しました')
    } catch (error) {
        console.error('メモの保存に失敗しました', error)

        alert('メモの保存に失敗しました')
    }
}

onMounted(async () => {
    Chart.register(
        LineController,  // 折れ線グラフを表示
        LineElement,     // 折れ線グラフを表示
        PointElement,    // 最高値・最低値の点
        LinearScale,     // y軸
        TimeScale,       // x軸
        Tooltip
    )

    await nextTick()

    if (!chartCanvas.value) {
        console.error('canvas がまだ描画されていません');

        return
    }

    const ctx = chartCanvas.value.getContext('2d')
    
    if (!ctx) {
        console.error('2D context が取得できません')

        return
    }

    fetchSma()

    const dates = props.chartData.dates
    const highs = props.chartData.highValue.map(Number)
    const rows = props.chartData.rowValue.map(Number)
    const middles = highs.map((val, i) => (val + rows[i]) / 2)
    const highTrend = linearTrendLine(dates, highs)
    const rowTrend = linearTrendLine(dates, rows)
    const middleTrend = linearTrendLine(dates, middles)
    const highsXY = props.chartData.dates.map((d, i) => ({ x: d, y: Number(props.chartData.highValue[i]) }))
    const rowsXY  = props.chartData.dates.map((d, i) => ({ x: d, y: Number(props.chartData.rowValue[i]) }))

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.chartData.dates,
            datasets: [
                {
                    label: '最高値',
                    data: highsXY,
                    borderColor: 'red',
                    backgroundColor: 'transparent'
                },
                {
                    label: '最低値',
                    data: rowsXY,
                    borderColor: 'blue',
                    backgroundColor: 'transparent'
                },
                {
                    label: 'レジスタンス(高値傾向)',
                    type: 'line',
                    data: highTrend.map((y, i) => ({ x:dates[i], y })),
                    borderColor: 'orange',
                    // borderDash: [4, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill:false 
                },
                {
                    label: 'サポート（安値傾向）',
                    data: rowTrend.map((y, i) => ({ x: dates[i], y })),
                    borderColor: 'green',
                    // borderDash: [4, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false
                },
                {
                    label: '中心軸トレンド',
                    data: middleTrend.map((y, i) => ({ x: dates[i], y })),
                    borderColor: 'gray',
                    // borderDash: [2, 6],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false
                }                
            ]
        },
        options: {
            responsive: false,
            scales: {
                y: {
                    max: 153,
                    min: 140
                },
                x: {
                    type: 'time',
                    time: {
                        unit: 'day',
                        displayFormats: {
                            day: 'yyyy/MM/dd'
                        }
                    },
                    ticks: {
                        source: 'labels',
                        autoSkip: false,
                        maxRotation: 90,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function (tooltipItems) {
                            const date = tooltipItems[0].parsed.x;

                            return format(new Date(date), "yyyy年M月d日", { locale: ja })
                        },
                        afterBody: function (tooltipItems) {
                            const point = tooltipItems[0];
                            const date = format(new Date(point.parsed.x), "yyyy-MM-dd")
                            const datasetIndex = point.datasetIndex

                            if (datasetIndex === 0) {
                                // 最高値の場合
                                const highMemo = highMemos.value[date]

                                return [`最高値メモ: ${highMemo || 'なし'}`]
                            } else if (datasetIndex === 1) {
                                // 最低値の場合
                                const rowMemo = rowMemos.value[date]

                                return [`最低値メモ: ${rowMemo || 'なし'}`]
                            }
                        }
                    },
                    external: (context) => {
                        const tooltipModel = context.tooltip
                        const point = tooltipModel.dataPoints?.[0]      // チャートに表示される最初のデータ

                        if (tooltipModel.opacity === 0) return

                        if (!point) return

                        const date = format(new Date(point.parsed.x), "yyyy-MM-dd")
                        const datasetIndex = point.datasetIndex
                        const type = datasetIndex === 0 ? 'high' : 'row'

                        if (chartCanvas.value) {

                            // メモを入力するモーダルを開く
                            chartCanvas.value.onclick = () => openMemoModal(date, type)
                        }
                    }
                }
            }
        }
    });
});
</script>

<style lang="scss">
.value-wrap {
    display:flex;
    column-gap:40px;
}
.orange {
    display: inline-block;
    width: 30px;
    height: 5px;
    background-color: orange;
    margin-left:5px;
    margin-bottom:4px;
}
.green {
    display: inline-block;
    width: 30px;
    height: 5px;
    background-color: green;
    margin-left:5px;
    margin-bottom:4px;
}
.gray {
    display: inline-block;
    width: 30px;
    height: 5px;
    background-color: gray;
    margin-left:5px;
    margin-bottom:4px;
}
.purple {
    display: inline-block;
    width: 30px;
    height: 5px;
    background-color: purple;
    margin-left:5px;
}
</style>