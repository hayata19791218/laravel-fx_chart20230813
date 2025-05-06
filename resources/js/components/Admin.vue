<template>
  <div class="mt-10">
    <div v-if="message" class="mb-4 p-2 bg-green-100 text-green-800 rounded text-center">{{ message }}</div>
    <h1 class="text-2xl font-bold mb-4 text-center">管理画面</h1>
    <table class="w-full border">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2 border border-gray-300">日付</th>
                <th class="p-2 border border-gray-300">最高値</th>
                <th class="p-2 border border-gray-300">最安値</th>
                <th class="p-2 border border-gray-300">編集</th>
                <th class="p-2 border border-gray-300">削除</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in editDatas" :key="item.id" class="text-center border-t">
                <td class="border border-gray-300">{{ item.date }}</td>
                <td class="border border-gray-300">{{ item.high_value }}</td>
                <td class="border border-gray-300">{{ item.row_value }}</td>
                <td class="border border-gray-300"><a :href="`/api/admin/edit/${item.id}`" class="text-blue-500 hover:underline">編集</a></td>
                <td class="border border-gray-300"><button @click="deleteItem(item.id)" class="text-red-500 hover:underline">削除</button></td>
            </tr>
        </tbody>
    </table>
    <div class="mt-4">
        <a href="/" class="text-blue-500 mr-4">チャートのページに戻る</a>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface EditData {
    id: number;
    date: string;
    high_value: string;
    row_value: string;
}

const editDatas = ref<EditData[]>([]);
const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';
const message = ref<string>('');

onMounted(async () => {
    const response = await axios.get<EditData[]>('/api/admin/value-data');  
    editDatas.value = response.data;
});

// 削除
const deleteItem = async (id: number): Promise<void> => {
    const deleteConfirm = confirm('本当に削除してもいいですか?');

    if (!deleteConfirm) return;
    
    try {
        const response = await axios.delete<{ message: string }>(`/api/admin/delete/${id}`, {
            headers: {
                'X-CSRF-TOKEN': csrf
            }
        });

        editDatas.value = editDatas.value.filter(item => item.id !== id);
        message.value = response.data.message;

        setTimeout(() => {
            message.value = '';
        }, 3000);
    } catch(error) {
        alert('削除に失敗しました');
    }
};
</script>