<template>
  <div class="chronos-grid">
    <div class="chronos-grid__weekdays">
      <div
        v-for="(day, index) in weekDays"
        :key="day"
        class="chronos-grid__weekday"
        :class="{ 'chronos-grid__weekday--weekend': index >= 5 }"
      >
        {{ day }}
      </div>
    </div>

    <div class="chronos-grid__days">
      <CalendarDay
        v-for="(item, index) in calendarDays"
        :key="item.date.toISOString()"
        :date="item.date"
        :is-today="item.isToday"
        :is-current-month="item.isCurrentMonth"
        :is-weekend="layout.weekendDays.has(index)"
        :is-holiday="layout.holidayDays.has(index)"
        :events="getEventsForDate(item.date, index)"
        :hidden-count="layout.hiddenEventsCount[index] || 0"
        @select="$emit('select-date', $event)"
        @event-drop="$emit('event-drop', $event)"
        @show-more="handleShowMore(index)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import CalendarDay from '../Molecules/CalendarDay.vue';
import { useCalendarLayoutProcessor } from '../../Composables/CalendarLayoutProcessor.js';

const props = defineProps({
  calendarDays: { type: Array, required: true },
  events: { type: Array, default: () => [] }
});

const emit = defineEmits(['select-date', 'event-drop', 'show-more-events']);

const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

// Используем процессор для расчета раскладки
const layout = computed(() => {
  return useCalendarLayoutProcessor(props.calendarDays, props.events, {
    maxVisibleEvents: 3,
    weekendDays: [5, 6], // Сб, Вс
    holidays: [], // TODO: Загружать из конфига
  });
});

const getEventsForDate = (date, dayIndex) => {
  return layout.value.dayEvents[dayIndex] || [];
};

const handleShowMore = (dayIndex) => {
  emit('show-more-events', {
    dayIndex,
    date: props.calendarDays[dayIndex].date,
  });
};
</script>

<style scoped>
.chronos-grid {
  --grid-border: var(--chronos-border, #e2e8f0);
  --weekday-bg: var(--chronos-bg-alt, #f8fafc);
  --weekday-text: var(--chronos-text-light, #64748b);

  display: flex;
  flex-direction: column;
  border: 1px solid var(--grid-border);
  border-radius: var(--chronos-radius, 8px);
  overflow: hidden;
  background: #fff;
}

.chronos-grid__weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background-color: var(--weekday-bg);
  border-bottom: 1px solid var(--grid-border);
}

.chronos-grid__weekday {
  padding: 12px;
  text-align: center;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--weekday-text);
  letter-spacing: 0.05em;
}

.chronos-grid__weekday--weekend {
  color: var(--chronos-text-weekend, #ef4444);
  background-color: var(--chronos-bg-weekend-alt, #fef2f2);
}

.chronos-grid__days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  position: relative;
}
</style>