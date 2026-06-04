<template>
    <div class="auth-form">
        <transition name="fade">
            <div v-if="!isReady" class="auth-form-loader">
                <div class="auth-form-loader__spinner"></div>
                <p class="auth-form-loader__text">Загрузка данных...</p>
            </div>
        </transition>

        <form @submit.prevent="submitForm">
            <h2 class="auth-form__title">Создание аккаунта</h2>

            <div class="auth-form__group">
                <label class="auth-form__label" for="name">Имя пользователя</label>
                <input 
                    id="name" 
                    class="auth-form__input"
                    :class="{'auth-form__input--error': errors.name}"
                    v-model="form.name" 
                    type="text" 
                    :disabled="loading || !isReady"
                    placeholder="Иван Иванов" 
                    autocomplete="name"
                >
                <span v-if="errors.name" class="auth-form__error-message">{{ errors.name[0] }}</span>
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="email">Электронная почта</label>
                <input 
                    id="email" 
                    class="auth-form__input"
                    :class="{'auth-form__input--error': errors.email}"
                    v-model="form.email" 
                    type="email" 
                    :disabled="loading || !isReady"
                    placeholder="example@mail.com" 
                    autocomplete="email"
                >
                <span v-if="errors.email" class="auth-form__error-message">{{ errors.email[0] }}</span>
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="password">Пароль</label>
                <input 
                    id="password" 
                    class="auth-form__input"
                    :class="{'auth-form__input--error': errors.password}"
                    v-model="form.password" 
                    type="password" 
                    :disabled="loading || !isReady"
                    placeholder="********"
                    autocomplete="new-password"
                >
                <span v-if="errors.password" class="auth-form__error-message">{{ errors.password[0] }}</span>
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="password_confirmation">Подтвердите пароль</label>
                <input 
                    id="password_confirmation" 
                    class="auth-form__input"
                    v-model="form.password_confirmation" 
                    type="password" 
                    :disabled="loading || !isReady"
                    placeholder="********"
                    autocomplete="new-password"
                >
            </div>

            <div v-if="consentTypes.length > 0" class="auth-form__group">
                <div v-for="consent in consentTypes" :key="consent.id" class="auth-form__checkbox-row">
                    <input 
                        type="checkbox" 
                        :id="'consent_' + consent.id" 
                        v-model="form.consents[consent.slug]"
                        :disabled="loading || !isReady"
                    >
                    <label :for="'consent_' + consent.id" class="auth-form__label">
                        {{ consent.title }}
                        <span v-if="consent.is_required" class="auth-form__required-mark">*</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="auth-form__submit" :disabled="loading || !isReady || !requiredConsentsAccepted">
                {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
            </button>
            
            <div class="auth-form__footer">
                <button type="button" @click="$emit('switch')" class="auth-form__link">
                    Уже есть аккаунт? Войти
                </button>
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
import { computed } from 'vue';
import { useRegistration } from '../composables/useRegistration';
const { form, loading, isReady, errors, consentTypes, notification, submitForm } = useRegistration();
defineEmits(['switch']);

const requiredConsentsAccepted = computed(() => {
    return consentTypes.value
        .filter(c => c.is_required)
        .every(c => form.consents[c.slug] === true);
});
</script>