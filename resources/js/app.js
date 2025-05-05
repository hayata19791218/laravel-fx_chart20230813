import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { createApp } from 'vue/dist/vue.esm-bundler.js';
import Create from './components/Create.vue';
import Chart from './components/Chart.vue';
import Login from './components/Login.vue';
import Admin from './components/Admin.vue';
import Edit from './components/Edit.vue';

const app = createApp({});

app.component('chart-component', Chart);
app.component('create-component', Create);
app.component('login-component', Login);
app.component('admin-component', Admin);
app.component('edit-component', Edit);

app.mount('#app');
