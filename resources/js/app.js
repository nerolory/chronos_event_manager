import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import ChronosWidget from '@chronos/ChronosWidget.vue';

// Импортируем компоненты
import AuthContainer from './components/AuthContainer.vue';
import RegistrationForm from './components/RegistrationForm.vue';
import LoginForm from './components/LoginForm.vue';

const app = createApp({});
app.use(createPinia());

// Регистрируем их глобально
app.component('auth-container', AuthContainer);
app.component('registration-form', RegistrationForm);
app.component('login-form', LoginForm);
app.component('chronos-widget', ChronosWidget);

app.mount('#app');