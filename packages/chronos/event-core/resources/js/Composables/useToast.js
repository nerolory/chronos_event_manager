import { ref } from 'vue';

const toasts = ref([]);

/**
 * useToast - composable для управления уведомлениями-тостами
 * @returns {Object} Объект с методами для работы с тостами
 * @returns {Ref<Array>} toasts - Реактивный массив тостов
 * @returns {Function} addToast - Функция для добавления тоста
 * @returns {Function} removeToast - Функция для удаления тоста
 * @returns {Function} success - Функция для добавления успешного тоста
 * @returns {Function} error - Функция для добавления тоста ошибки
 * @returns {Function} info - Функция для добавления информационного тоста
 * @returns {Function} warning - Функция для добавления тоста предупреждения
 */
export function useToast() {
  const addToast = (message, type = 'success', duration = 3000) => {
    const id = Date.now();
    const toast = { id, message, type, duration };
    toasts.value.push(toast);
    return id;
  };

  const removeToast = (id) => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index !== -1) {
      toasts.value.splice(index, 1);
    }
  };

  const success = (message, duration) => addToast(message, 'success', duration);
  const error = (message, duration) => addToast(message, 'error', duration);
  const info = (message, duration) => addToast(message, 'info', duration);
  const warning = (message, duration) => addToast(message, 'warning', duration);

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    info,
    warning,
  };
}
