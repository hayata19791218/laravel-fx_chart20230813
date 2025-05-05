<template>
    <div class="chart-wrapper h-[500px]">
        <canvas ref="chartCanvas" :width="chartWidth"></canvas>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
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

const props = defineProps({
    chartData: {
        type: Object,
        required: true
    }
});

const chartCanvas = ref(null);
const chartWidth = ref(800);

onMounted(() => {
    chartWidth.value = props.chartData.dates.length * 80;
    
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

    const ctx = chartCanvas.value.getContext('2d');

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
            responsive: true,
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
                    }
                },
                ticks: {
                    autoSkip: false,
                    maxRotation: 90,
                    minRotation: 45
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
