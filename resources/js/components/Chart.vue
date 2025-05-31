<template>
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
    values: number[];
    values2: number[];
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

let chartInstance: Chart | null = null;

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


chartInstance =
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.chartData.dates,
            datasets: [
                {
                    label: '最高値',
                    data: props.chartData.values,
                    borderColor: 'red',
                    backgroundColor: 'transparent'
                },
                {
                    label: '最低値',
                    data: props.chartData.values2,
                    borderColor: 'blue',
                    backgroundColor: 'transparent'
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