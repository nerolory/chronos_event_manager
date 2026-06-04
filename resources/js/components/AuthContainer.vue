<template>
    <div class="auth-container">
        <transition name="fade" mode="out-in">
            <component 
                :is="isLogin ? 'login-form' : 'registration-form'" 
                @switch="toggleAuth" 
            />
        </transition>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const isLogin = ref(true);

const toggleAuth = () => {
    isLogin.value = !isLogin.value;
};
</script>

<style>
/* Только логика анимации, так как она специфична для Vue */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.auth-container {
    width: 100%;
    display: flex;
    justify-content: center;
    padding: 2rem 1rem;
}
</style>