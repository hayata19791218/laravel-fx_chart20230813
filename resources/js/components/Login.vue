<template>
    <div class="max-w-md mx-auto mt-10">
        <a class="text-blue-600 hover:underline" href="/">チャートのページに戻る</a>
        <h1 class="text-2xl font-bold mb-4 mt-4">ログイン</h1>
        <div v-if="validationErrors.email" class="text-red-500 text-sm mb-2">
            {{ validationErrors.email[0] }}
        </div>
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

<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';

interface FormData {
    email: string;
    password: string;
}

interface ErrorResponse {
    message: string;
}

const formData = ref<FormData>({
    email: '',
    password: ''
});
const validationErrors = ref<{ [key: string]: string[] }>({});
const login = async (): Promise<void> => {
    try {
        await axios.get<void>('/sanctum/csrf-cookie');

        await axios.post<void>('/login', formData.value);

        window.location.href = '/';
    } catch (err: unknown) {
        if (axios.isAxiosError(err)) {
            const axiosError = err as AxiosError<ErrorResponse & { errors?: Record<string, string[]> }>;

            validationErrors.value = axiosError.response?.data?.errors || {};
        } 
    }
}
</script>