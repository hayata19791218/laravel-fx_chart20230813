<template>
  
    <canvas
      ref="chartCanvas"
      :width="chartWidth"
      height="500"
    ></canvas>

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

interface ChartData {
    dates: string[];
    values: number[];
    values2: number[];
}

const props = defineProps<{
    chartData: ChartData;
}>();

const chartCanvas = ref<HTMLCanvasElement | null>(null);
const chartWidth = ref<number>(Math.min(props.chartData.dates.length * 80, 7000));

onMounted(async () => {
    // chartWidth.value = Math.min(props.chartData.dates.length * 80, 7000);
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







    // const ctx = chartCanvas.value.getContext('2d');

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
                        }
                    }
                }
            }
        }
    });
});
</script>
