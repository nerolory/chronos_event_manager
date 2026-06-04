<template>
  <aside class="side-panel">
    <div class="side-panel__header">
      <h3 class="side-panel__date">
        {{ formattedDate }}
      </h3>
      <span
        v-if="events.length"
        class="side-panel__count"
      >
        {{ events.length }} {{ eventWord(events.length) }}
      </span>
    </div>

    <div class="side-panel__body">
      <div
        v-if="isLoading"
        class="side-panel__skeleton"
      >
        <div
          v-for="i in 3"
          :key="i"
          class="skeleton-item"
        >
          <div class="skeleton-dot" />
          <div class="skeleton-content">
            <div class="skeleton-title" />
            <div class="skeleton-time" />
          </div>
        </div>
      </div>

      <template v-else>
        <div
          v-if="events.length === 0"
          class="side-panel__empty"
        >
          <span>Нет событий</span>
        </div>

        <ul
          v-else
          class="side-panel__list"
        >
          <li
            v-for="event in events"
            :key="event.event_id || event.id"
            class="side-panel__item"
            :class="{ 'side-panel__item--completed': event.is_completed }"
          >
            <span
              class="side-panel__color-dot"
              :style="{ '--event-color': event.color || '#4f46e5' }"
            />
            <div class="side-panel__item-content">
              <span class="side-panel__item-title">{{ event.title }}</span>
              <span
                v-if="!selectedDate"
                class="side-panel__item-date"
                :class="{ 'side-panel__item-date--today': isToday(event.start) }"
              >{{ formatDate(event.start) }}</span>
              <span class="side-panel__item-time">
                {{ formatTime(event.start) }} – {{ formatTime(event.end) }}
              </span>
            </div>
            <div class="side-panel__item-actions">
              <button
                class="side-panel__action-btn"
                title="Редактировать"
                @click="$emit('edit-event', event)"
              >
                ✎
              </button>
              <button
                class="side-panel__action-btn side-panel__action-btn--danger"
                title="Удалить"
                @click="$emit('delete-event', event)"
              >
                ✕
              </button>
            </div>
          </li>
        </ul>
      </template>
    </div>

    <div class="side-panel__footer">
      <button
        class="side-panel__add-btn"
        @click="$emit('add-event', selectedDate)"
      >
        + Добавить событие
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  selectedDate: { type: Date, default: null },
  events: { type: Array, default: () => [] },
  isLoading: { type: Boolean, default: false },
});

defineEmits(['add-event', 'edit-event', 'delete-event']);

const formattedDate = computed(() => {
  if (!props.selectedDate) return 'Ближайшие события';
  return props.selectedDate.toLocaleString('ru-RU', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  });
});

function formatTime(dateVal) {
  if (!dateVal) return '—';
  return new Date(dateVal).toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatDate(dateVal) {
  if (!dateVal) return '';
  const d = new Date(dateVal);
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);

  if (d.toDateString() === today.toDateString()) {
    return 'Сегодня';
  }
  if (d.toDateString() === tomorrow.toDateString()) {
    return 'Завтра';
  }
  return d.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
  });
}

function isToday(dateVal) {
  if (!dateVal) return false;
  const d = new Date(dateVal);
  const today = new Date();
  return d.toDateString() === today.toDateString();
}

function eventWord(n) {
  if (n % 10 === 1 && n % 100 !== 11) return 'событие';
  if ([2, 3, 4].includes(n % 10) && ![12, 13, 14].includes(n % 100)) return 'события';
  return 'событий';
}
</script>

<style scoped>
.side-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 24px;
  background: #f8fafc;
  border-right: 1px solid #e2e8f0;
  gap: 16px;
}

.side-panel__header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 8px;
}

.side-panel__date {
  font-size: 0.9rem;
  font-weight: 700;
  color: #0f172a;
  text-transform: capitalize;
  line-height: 1.3;
}

.side-panel__count {
  font-size: 0.75rem;
  color: #64748b;
  white-space: nowrap;
}

.side-panel__body {
  flex: 1;
  overflow-y: auto;
}

.side-panel__empty {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 80px;
  color: #94a3b8;
  font-size: 0.875rem;
}

.side-panel__list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.side-panel__item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  transition: border-color 0.15s;
}

.side-panel__item:hover {
  border-color: #94a3b8;
}

.side-panel__item--completed {
  opacity: 0.6;
}

.side-panel__item--completed .side-panel__item-title {
  text-decoration: line-through;
}

.side-panel__color-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
  background-color: var(--event-color, #4f46e5);
}

.side-panel__item-content {
  flex: 1;
  min-width: 0;
}

.side-panel__item-title {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.side-panel__item-time {
  display: block;
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 2px;
}

.side-panel__item-date {
  display: block;
  font-weight: 600;
  color: #4f46e5;
  margin-top: 2px;
}

.side-panel__item-date--today {
  color: #16a34a;
  font-weight: 700;
}

.side-panel__item-actions {
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.15s;
}

.side-panel__item:hover .side-panel__item-actions {
  opacity: 1;
}

.side-panel__action-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 4px;
  font-size: 0.875rem;
  color: #64748b;
  transition: background 0.15s, color 0.15s;
}

.side-panel__action-btn:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.side-panel__action-btn--danger:hover {
  background: #fee2e2;
  color: #dc2626;
}

.side-panel__footer {
  padding-top: 8px;
  border-top: 1px solid #e2e8f0;
}

.side-panel__add-btn {
  width: 100%;
  padding: 10px;
  background: #4f46e5;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.side-panel__add-btn:hover {
  background: #4338ca;
}

.skeleton-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  margin-bottom: 8px;
}

.skeleton-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  flex-shrink: 0;
}

.skeleton-content {
  flex: 1;
  min-width: 0;
}

.skeleton-title {
  height: 16px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 4px;
  margin-bottom: 6px;
  width: 70%;
}

.skeleton-time {
  height: 12px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 4px;
  width: 40%;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
