<template>
  <Transition name="toast">
    <div
      v-if="visible"
      class="toast"
      :class="`toast--${type}`"
    >
      <div class="toast__content">
        <span class="toast__icon">{{ icon }}</span>
        <span class="toast__message">{{ message }}</span>
      </div>
      <div class="toast__progress">
        <div
          class="toast__progress-bar"
          :style="{ '--progress': progress + '%' }"
        />
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

defineOptions({
  name: 'ToastNotification'
});

const props = defineProps({
  message: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'info', 'warning'].includes(value),
  },
  duration: {
    type: Number,
    default: 3000,
  },
});

const emit = defineEmits(['close']);

const visible = ref(true);
const progress = ref(100);

const icon = computed(() => {
  const icons = {
    success: '✓',
    error: '✕',
    info: 'ℹ',
    warning: '⚠',
  };
  return icons[props.type] || 'ℹ';
});

let interval;
let timeout;

onMounted(() => {
  const step = 100 / (props.duration / 50);
  interval = setInterval(() => {
    progress.value -= step;
    if (progress.value <= 0) {
      clearInterval(interval);
    }
  }, 50);

  timeout = setTimeout(() => {
    visible.value = false;
    setTimeout(() => emit('close'), 300);
  }, props.duration);
});

onUnmounted(() => {
  clearInterval(interval);
  clearTimeout(timeout);
});
</script>

<style scoped>
.toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  min-width: 300px;
  max-width: 400px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgb(0 0 0 / 15%);
  overflow: hidden;
  z-index: 1000;
}

.toast--success {
  border-left: 4px solid #22c55e;
}

.toast--error {
  border-left: 4px solid #ef4444;
}

.toast--info {
  border-left: 4px solid #3b82f6;
}

.toast--warning {
  border-left: 4px solid #f59e0b;
}

.toast__content {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
}

.toast__icon {
  font-size: 1.25rem;
  font-weight: bold;
}

.toast--success .toast__icon {
  color: #22c55e;
}

.toast--error .toast__icon {
  color: #ef4444;
}

.toast--info .toast__icon {
  color: #3b82f6;
}

.toast--warning .toast__icon {
  color: #f59e0b;
}

.toast__message {
  flex: 1;
  font-size: 0.875rem;
  color: #1e293b;
}

.toast__progress {
  height: 3px;
  background: #f1f5f9;
}

.toast__progress-bar {
  height: 100%;
  background: currentcolor;
  width: var(--progress, 100%);
  transition: width 0.05s linear;
}

.toast--success .toast__progress-bar {
  background: #22c55e;
}

.toast--error .toast__progress-bar {
  background: #ef4444;
}

.toast--info .toast__progress-bar {
  background: #3b82f6;
}

.toast--warning .toast__progress-bar {
  background: #f59e0b;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
