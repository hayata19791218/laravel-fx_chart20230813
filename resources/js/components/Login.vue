<template>
    <div class="max-w-md mx-auto mt-10">
        <a class="text-blue-600 hover:underline" href="/">チャートのページに戻る</a>
        <h1 class="text-2xl font-bold mb-4 mt-4">ログイン</h1>
        <div v-if="error" class="text-red-500 mb-2">{{ error }}</div>
        <form @submit.prevent="login">
            <div class="mb-4">
                <label class="block mb-1">メールアドレス</label>
                <input v-model="formData.email" type="email" required class="w-full border px-3 py-2 rounded" />
            </div>
            <div class="mb-4">
                <label class="block mb-1">パスワード</label>
                <input v-model="formData.password" type="password" required class="w-full border px-3 py-2 rounded" />
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">ログイン</button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const formData = ref({
    email: '',
    password: ''
});
const error = ref('');
const login = async () => {
    error.value = '';

    try {
        await axios.get('/sanctum/csrf-cookie');

        await axios.post('/login', formData.value);

        window.location.href = '/';
    } catch (err) {
        error.value = err.response?.data?.message || 'ログインに失敗しました'
    }
}
</script>