<template>
    <div class="mb-6">
        <div class="mb-2">[単純移動平均線(紫)]</div>
        <div class="my-4 flex items-center gap-4">
            <label for="smaDays">SMA日数:</label>
            <div class="flex gap-x-1 items-center">
                <input
                    id="smaDays"
                    type="number"
                    v-model.number="selectedDays"
                    @change="fetchSma"
                    class="border p-1 rounded w-[60px]"
                    min="1"
                />
                <div>日</div>
            </div>
        </div>
    </div>
    <div class="mb-6">
        <div>[表示期間]</div>
        <div class="my-4 flex items-center gap-2">
            <input type="date" v-model="startDate" class="border p-1 rounded" />
            <span>〜</span>
            <input type="date" v-model="endDate" class="border p-1 rounded" />
            <button
                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600"
                @click="applyDateFilter"
            >適用</button>
        </div>
    </div>
    <div>
        <div class="mb-2">[トレンドライン]</div>
        <div class="flex items-center gap-4 mb-6">
            <div>高値トレンド : オレンジ</div>
            <div>安値トレンド : グリーン</div>
            <div>中心軸トレンド : グレイ</div>
        </div>
    </div>

    <canvas
        ref="chartCanvas"
        :width="chartWidth"
        height="500"
    ></canvas>
    <div 
        v-if="showModal" 
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
    Title,
    CategoryScale,
    TimeScale,
    Tooltip
} from 'chart.js';
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

const chartCanvas = ref<HTMLCanvasElement | null>(null);
const chartWidth = ref<number>(Math.min(props.chartData.dates.length * 80, 7000));
const showModal = ref(false);
const selectedDate = ref('');
const memoText = ref('');
const highMemos = ref<Record<string, string>>({ ...props.chartData.highMemos });
const rowMemos = ref<Record<string, string>>({ ...props.chartData.rowMemos });
const selectedType = ref<'high' | 'row'>('high');
const selectedDays = ref(3);
const startDate = ref<string>('');
const endDate = ref<string>('');

let chartInstance: Chart | null = null;


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


const applyDateFilter = () => {
    if (!chartInstance) return;

    if (!startDate.value || !endDate.value) {
        alert('開始日と終了日を入力してください。');

        startDate.value = '';
        endDate.value = '';

        return;
    }

    const start = new Date(startDate.value);
    const end = new Date(endDate.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    start.setHours(0, 0, 0, 0);
    end.setHours(0, 0, 0, 0);
    const filteredDates: string[] = [];
    const filteredHighs: number[] = [];
    const filteredRows: number[] = [];

    if (start > today || end < start) {
        alert('正しい日付を選択してください。');

        startDate.value = '';
        endDate.value = '';

        return;
    }

    props.chartData.dates.forEach((date, i) => {
        const currentDate = new Date(date);

        if (currentDate >= start && currentDate <= end) {
            filteredDates.push(date);
            filteredHighs.push(Number(props.chartData.highValue[i]));
            filteredRows.push(Number(props.chartData.rowValue[i]));
            // filteredHighs.push(props.chartData.highValue[i]);
            // filteredRows.push(props.chartData.rowValue[i]);
        }
    });

    const filteredMiddles = filteredHighs.map((v, i) => (v + filteredRows[i]) / 2);
    const highTrend = linearTrendLine(filteredDates, filteredHighs);
    const rowTrend = linearTrendLine(filteredDates, filteredRows);
    const middleTrend = linearTrendLine(filteredDates, filteredMiddles);

    chartInstance.data.labels = filteredDates;
    chartInstance.data.datasets[0].data = filteredHighs;
    chartInstance.data.datasets[1].data = filteredRows;



    chartInstance.data.datasets[2].data = highTrend.map((y, i) => ({ x: filteredDates[i], y }));
    chartInstance.data.datasets[3].data = rowTrend.map((y, i) => ({ x: filteredDates[i], y }));
    chartInstance.data.datasets[4].data = middleTrend.map((y, i) => ({ x: filteredDates[i], y }));
    chartInstance.update();
}

const updateSmaLine = (smaData: number[]) => {
    if (!chartInstance) return;

    const smaDatasetIndex = chartInstance.data.datasets.findIndex(ds => ds.label === 'SMA');


    const smaPoints = props.chartData.dates.map((date, i) => ({
        x: date,
        y: smaData[i] ?? null
    }));

    if (smaDatasetIndex !== -1) {
        // すでに存在する場合は更新
        chartInstance.data.datasets[smaDatasetIndex].data = smaData;
    } else {
        // 存在しない場合は追加
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

    chartInstance.update();
};

const fetchSma = async () => {
    try {
        const res = await axios.get('/api/sma', {
            params: { days: selectedDays.value }
        });

        updateSmaLine(res.data.sma);
    } catch (e) {
        console.error('SMA取得失敗', e);
    }
};

const openModal = async (date: string, type: 'high' | 'row') => {
    if (!props.isLoggedIn) {
        alert('ログインしてください');

        return;
    }

    selectedDate.value = date;
    selectedType.value = type;

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

    showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
}

const saveMemo = async () => {
    try {

        // メモを新規登録か更新かのチェック
        const memoUpdateCheck = selectedType.value === 'high' ? !!highMemos.value[selectedDate.value] : !!rowMemos.value[selectedDate.value];

        await axios.post('/api/save-memo', {
            date: selectedDate.value,
            type: selectedType.value,
            memo: memoText.value,
        });

        if (selectedType.value === 'high') {
            highMemos.value[selectedDate.value] = memoText.value;
        } else {
            rowMemos.value[selectedDate.value] = memoText.value;
        }

        showModal.value = false;

        alert(memoUpdateCheck ? 'メモを更新しました' : 'メモを保存しました');
    } catch (error) {
        console.error('メモの保存に失敗しました', error);

        alert('メモの保存に失敗しました');
    }
}

onMounted(async () => {
    Chart.register(
        LineController,
        LineElement,
        PointElement,
        LinearScale,
        Title,
        CategoryScale,
        TimeScale,
        Tooltip
    );

    await nextTick();

    if (!chartCanvas.value) {
        console.error('canvas がまだ描画されていません');

        return;
    }

    const ctx = chartCanvas.value.getContext('2d');
    
    if (!ctx) {
        console.error('2D context が取得できません');

        return;
    }

    fetchSma();

    const dates = props.chartData.dates;
    const highs = props.chartData.highValue.map(Number);
    const rows = props.chartData.rowValue.map(Number);
    const middles = highs.map((val, i) => (val + rows[i]) / 2);
    const highTrend = linearTrendLine(dates, highs);
    const rowTrend = linearTrendLine(dates, rows);
    const middleTrend = linearTrendLine(dates, middles);

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.chartData.dates,
            datasets: [
                {
                    label: '最高値',
                    data: props.chartData.highValue,
                    borderColor: 'red',
                    backgroundColor: 'transparent'
                },
                {
                    label: '最低値',
                    data: props.chartData.rowValue,
                    borderColor: 'blue',
                    backgroundColor: 'transparent'
                },
                {
                    label: 'レジスタンス(高値傾向)',
                    type: 'line',
                    data: highTrend.map((y, i) => ({ x:dates[i], y })),
                    borderColor: 'orange',
                    borderDash: [4, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill:false 
                },
                {
                    label: 'サポート（安値傾向）',
                    data: rowTrend.map((y, i) => ({ x: dates[i], y })),
                    borderColor: 'green',
                    borderDash: [4, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false
                },
                {
                    label: '中心軸トレンド',
                    data: middleTrend.map((y, i) => ({ x: dates[i], y })),
                    borderColor: 'gray',
                    borderDash: [2, 6],
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
                    max: 165,
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

                            return format(new Date(date), "yyyy年M月d日", { locale: ja });
                        },
                        afterBody: function (tooltipItems) {
                            const point = tooltipItems[0];
                            const date = format(new Date(point.parsed.x), "yyyy-MM-dd");
                            const datasetIndex = point.datasetIndex;

                            if (datasetIndex === 0) {
                                // 最高値の場合
                                const highMemo = highMemos.value[date];

                                return [`最高値メモ: ${highMemo || 'なし'}`];
                            } else if (datasetIndex === 1) {
                                // 最低値の場合
                                const rowMemo = rowMemos.value[date];

                                return [`最低値メモ: ${rowMemo || 'なし'}`];
                            }
                        }
                    },
                    external: (context) => {
                        const tooltipModel = context.tooltip;
                        if (tooltipModel.opacity === 0) return;
                        const point = tooltipModel.dataPoints?.[0];
                        if (!point) return;

                        const date = format(new Date(point.parsed.x), "yyyy-MM-dd");
                        const datasetIndex = point.datasetIndex;
                        const type = datasetIndex === 0 ? 'high' : 'row';

                        if (chartCanvas.value) {
                            chartCanvas.value.onclick = () => openModal(date, type);
                        }
                    }
                }
            }
        }
    });
});
</script>