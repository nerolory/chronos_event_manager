/**
 * useDragDrop - composable для drag-and-drop функциональности
 * @param {Function} onDrop - Callback при завершении drag-and-drop
 * @param {Ref<Boolean>} isPast - Ref для проверки, является ли дата прошлой
 * @returns {Object} Объект с методами и состоянием drag-and-drop
 * @returns {Ref<Boolean>} isDragOver - Состояние перетаскивания над элементом
 * @returns {Function} handleDragStart - Обработчик начала перетаскивания
 * @returns {Function} handleDragOver - Обработчик перетаскивания над элементом
 * @returns {Function} handleDragLeave - Обработчик ухода из зоны перетаскивания
 * @returns {Function} handleDrop - Обработчик завершения перетаскивания
 */

import { ref } from 'vue';

export function useDragDrop(onDrop, isPast) {
  const isDragOver = ref(false);

  const handleDragStart = (_event, eventData) => {
    _event.dataTransfer.effectAllowed = 'move';
    _event.dataTransfer.setData('application/json', JSON.stringify({
      eventId: eventData.id || eventData.event_id,
      event: eventData
    }));
  };

  const handleDragOver = (_event) => {
    if (!isPast?.value) {
      isDragOver.value = true;
    }
  };

  const handleDragLeave = () => {
    isDragOver.value = false;
  };

  const handleDrop = (event, targetDate) => {
    event.preventDefault();
    isDragOver.value = false;

    if (isPast?.value) return;

    try {
      const data = JSON.parse(event.dataTransfer.getData('application/json'));
      onDrop({
        eventId: data.eventId,
        event: data.event,
        targetDate
      });
    } catch {
      // Error silently
    }
  };

  return {
    isDragOver,
    handleDragStart,
    handleDragOver,
    handleDragLeave,
    handleDrop,
  };
}
