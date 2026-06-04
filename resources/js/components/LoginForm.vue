<template>
    <div class="auth-form">
        <transition name="fade">
            <div v-if="!isReady" class="auth-form-loader">
                <div class="auth-form-loader__spinner"></div>
                <p class="auth-form-loader__text">Загрузка...</p>
            </div>
        </transition>

        <form @submit.prevent="submitForm">
            <h2 class="auth-form__title">Вход в систему</h2>

            <div class="auth-form__group">
                <label class="auth-form__label" for="login_email">Электронная почта</label>
                <input 
                    id="login_email" 
                    class="auth-form__input"
                    :class="{'auth-form__input--error': errors.email}"
                    v-model="form.email" 
                    type="email" 
                    :disabled="loading" 
                    placeholder="example@mail.com"
                    autocomplete="username"
                >
                <span v-if="errors.email" class="auth-form__error-message">{{ errors.email[0] }}</span>
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="login_password">Пароль</label>
                <input 
                    id="login_password" 
                    class="auth-form__input"
                    :class="{'auth-form__input--error': errors.password}"
                    v-model="form.password" 
                    type="password" 
                    :disabled="loading" 
                    placeholder="********"
                    autocomplete="current-password"
                >
                <span v-if="errors.password" class="auth-form__error-message">{{ errors.password[0] }}</span>
            </div>

            <button type="submit" class="auth-form__submit" :disabled="loading">
                {{ loading ? 'Вход...' : 'Войти' }}
            </button>

            <div class="auth-form__footer">
                <button type="button" @click="$emit('switch')" class="auth-form__link">
                    Нет аккаунта? Зарегистрироваться
                </button>
                <a href="#" class="auth-form__link auth-form__link--secondary">Забыли пароль?</a>
            </div>
        </form>

        <transition name="slide-up">
            <div v-if="notification.show" 
                class="auth-notification" 
                :class="'auth-notification--' + notification.type">
                <div class="auth-notification__icon">
                    {{ notification.type === 'success' ? '✔' : '✖' }}
                </div>
                <p class="auth-notification__text">{{ notification.message }}</p>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['switch']);
const loading = ref(false);
const isReady = ref(false);
const errors = ref({});

const notification = reactive({
    show: false,
    type: 'success',
    message: ''
});

const form = reactive({
    email: '',
    password: '',
    remember: true
});

onMounted(() => {
    isReady.value = true;
});

const triggerNotification = (type, message) => {
    notification.type = type;
    notification.message = message;
    notification.show = true;
    setTimeout(() => {
        notification.show = false;
    }, 3000);
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};
    try {
        const response = await axios.post('/login-web', form);
        if (response.data.success) {
            triggerNotification('success', 'Вход выполнен успешно!');
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1000);
        }
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errors.value = e.response.data.errors;
            triggerNotification('error', 'Ошибка валидации.');
        } else {
            errors.value = { email: ['Неверная почта или пароль.'] };
            triggerNotification('error', 'Неверная почта или пароль.');
        }
    } finally {
        loading.value = false;
    }
};
</script>