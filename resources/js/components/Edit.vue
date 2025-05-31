<template>
    <div class="container mx-auto px-4 py-6">
        <h1 class="mb-4 text-center text-lg sm:text-xl lg:text-2xl font-semibold">値の編集</h1>
        <div v-if="message" class="mb-4 p-2 bg-green-100 text-green-800 rounded text-center">{{ message }}</div>
        <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-6 py-6 max-w-md w-full mx-auto space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">最高値</label>
                <input
                    v-model="form.high_value"
                    type="text"
                    step="0.001"
                    placeholder="最高値"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                    @input="(e) => preventString(e, 'high_value')"
                    @compositionupdate="(e) => handleChangingUpdate(e, 'high_value')"
                    @compositionend="(e) => handleChangingEnd(e, 'high_value')"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">最低値</label>
                <input
                    v-model="form.row_value"
                    type="text"
                    step="0.001"
                    placeholder="最低値"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                    @input="(e) => preventString(e, 'row_value')"
                    @compositionupdate="(e) => handleChangingUpdate(e, 'row_value')"
                    @compositionend="(e) => handleChangingEnd(e, 'row_value')"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">日付</label>
                <input
                    v-model="form.date"
                    type="date"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
            </div>
            <div>
                <input
                    type="submit"
                    value="更新する"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition"
                />
            </div>
        </form>
        <div class="text-center mt-6">
            <a href="/" class="text-blue-600 hover:underline">チャートのページに戻る</a>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps<{
    id: Number
}>();
interface FormData {
    high_value: string;
    row_value: string;
    date: string;
}
const form = ref<FormData>({
    high_value: '',
    row_value: '',
    date: ''
});
const errors = ref({});
const message = ref('');
const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).getAttribute('content');
const isChanging = ref(false);

onMounted(async () => {
    try {
        const response = await axios.get<FormData>(`/api/edit-value/${props.id}`);

        form.value = response.data;
    } catch (error) {
        console.error('CSRF Cookie Error:', error);
    }
});

// フォームのIME入力を削除
const handleChangingUpdate = (event : CompositionEvent, field: keyof FormData) => {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/[^0-9.]/g, '');
    form.value[field] = value;
};

const handleChangingEnd = (event: CompositionEvent, field: keyof FormData) => {
  isChanging.value = false;
  preventString(event as unknown as InputEvent, field);
};

const preventString = (event: InputEvent, field: keyof FormData) => {
    if (isChanging.value) return;

    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/[^0-9.]/g, '');

    if (value.length > 7) {
        value = value.slice(0, 7);
    }

    form.value[field] = value;
};

const submitForm = async () => {
    try {
        const response = await axios.put<{ message: string }>(`/api/admin/update/${props.id}`, form.value);
        message.value = response.data.message;

        setTimeout(() => {
            message.value = '';
        }, 3000);
    } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
    }
}
</script>