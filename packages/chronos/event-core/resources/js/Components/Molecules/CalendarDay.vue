<template>
  <div
    :class="[
      'chronos-day',
      {
        'chronos-day--today': isToday,
        'chronos-day--other-month': !isCurrentMonth,
        'chronos-day--has-events': events.length > 0,
        'chronos-day--past': isPast,
        'chronos-day--drag-over': isDragOver,
        'chronos-day--weekend': isWeekend,
        'chronos-day--holiday': isHoliday
      }
    ]"
    @click="$emit('select', date)"
    @dragover.prevent="handleDragOver"
    @dragleave="handleDragLeave"
    @drop="handleDrop"
  >
    <div class="chronos-day__header">
      <span class="chronos-day__number">{{ date.getDate() }}</span>
    </div>

    <div
      v-if="events.length > 0"
      class="chronos-day__events"
      :class="{ 'chronos-day__events--expanded': isExpanded }"
    >
      <div
        v-for="event in visibleEvents"
        :key="event.id || event.event_id"
        class="chronos-day__event-bar"
        :class="[
          { 'chronos-day__event-bar--multi-day': event.isMultiDay },
          getEventTextClass(event)
        ]"
        :style="{ '--event-bg-color': event.color || 'var(--chronos-primary, #4f46e5)' }"
        draggable="true"
        @dragstart="handleDragStart($event, event)"
      >
        <span class="chronos-day__event-title">{{ event.title }}</span>
      </div>
      <button
        v-if="showMoreButton"
        class="chronos-day__more-btn"
        :class="{ 'chronos-day__more-btn--expanded': isExpanded }"
        @click.stop="handleShowMore"
      >
        {{ moreButtonText }}
      </button>
    </div>
  </div>
</template>
  
  <script setup>
  import { computed, ref } from 'vue';
  import { isPast } from '../../Utils/dateUtils.js';
  import { useDragDrop } from '../../Composables/useDragDrop.js';

  const props = defineProps({
    date: { type: Date, required: true },
    events: { type: Array, default: () => [] },
    isToday: { type: Boolean, default: false },
    isCurrentMonth: { type: Boolean, default: true },
    isWeekend: { type: Boolean, default: false },
    isHoliday: { type: Boolean, default: false },
    hiddenCount: { type: Number, default: 0 }
  });

  const emit = defineEmits(['select', 'event-drop', 'show-more']);

  const isExpanded = ref(false);
  const isDatePast = computed(() => isPast(props.date));

  const MAX_VISIBLE_COLLAPSED = 3;

  const visibleEvents = computed(() => {
    if (!isExpanded.value) {
      return props.events.slice(0, MAX_VISIBLE_COLLAPSED);
    }
    // В расширенном состоянии показываем все события (скролл добавится через CSS)
    return props.events;
  });

  const showMoreButton = computed(() => {
    return props.events.length > MAX_VISIBLE_COLLAPSED;
  });

  const moreButtonText = computed(() => {
    if (!isExpanded.value) {
      return `+${props.events.length - MAX_VISIBLE_COLLAPSED}`;
    }
    return '';
  });

  // Функция для определения класса цвета текста
  const getEventTextClass = (event) => {
    const eventColor = event.color || '#4f46e5';
    const hex = eventColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5 ? 'chronos-day__event-bar--dark-text' : 'chronos-day__event-bar--light-text';
  };

  const { isDragOver, handleDragStart, handleDragOver, handleDragLeave, handleDrop } = useDragDrop(
    (data) => emit('event-drop', data),
    isDatePast
  );

  const handleShowMore = () => {
    isExpanded.value = !isExpanded.value;
  };
  </script>
  
  <style>
  .chronos-day {
    /* Переменные с фоллбэками */
    --day-bg: var(--chronos-bg, #fff);
    --day-border: var(--chronos-border, #e2e8f0);
    --accent: var(--chronos-primary, #4f46e5);

    min-height: 120px;
    background-color: var(--day-bg);
    box-shadow: inset -1px 0 0 0 var(--day-border);
    border-bottom: 1px solid var(--day-border);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    transition: background-color 0.2s;
    position: relative;
    z-index: 1;
  }

  .chronos-day--today {
    background-color: var(--chronos-bg-today, #f0f7ff);
  }

  .chronos-day--today .chronos-day__number {
    color: var(--accent);
    font-weight: 700;
  }

  .chronos-day--weekend {
    background-color: var(--chronos-bg-weekend, #fafafa);
  }

  .chronos-day--holiday {
    background-color: var(--chronos-bg-holiday, #fff5f5);
  }
  
  .chronos-day--other-month {
    opacity: 0.5;
    background-color: var(--chronos-bg-alt, #fdfdfd);
  }
  
  .chronos-day__header {
    display: flex;
    justify-content: flex-end;
    padding: 12px;
    margin-bottom: 8px;
  }

  .chronos-day__number {
    font-size: 0.875rem;
  }

  .chronos-day__events {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 0 0 12px;
    max-height: 84px; /* 3 события по 20px + gap */
    overflow: hidden;
    transition: max-height 0.2s ease;
  }

  .chronos-day__events--expanded {
    max-height: 180px; /* 6 событий по 20px + gap + кнопка */
    overflow-y: auto;
    padding: 12px;
  }

  .chronos-day__event-bar {
    width: calc(100% - 1px);
    height: 20px;
    border-radius: 2px;
    cursor: grab;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
    background-color: var(--event-bg-color, var(--chronos-primary, #4f46e5));
  }

  .chronos-day__event-bar--multi-day {
    width: calc(100% + 1px);
  }

  .chronos-day__event-bar--light-text .chronos-day__event-title {
    color: white;
  }

  .chronos-day__event-bar--dark-text .chronos-day__event-title {
    color: #1e293b;
  }

  .chronos-day__event-bar:active {
    cursor: grabbing;
  }

  .chronos-day__event-title {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.7rem;
    color: white;
    padding: 2px 4px;
    line-height: 16px;
  }

  .chronos-day__event-bar:hover .chronos-day__event-title {
    white-space: normal;
    overflow: visible;
    background-color: inherit;
    z-index: 10;
    position: absolute;
    inset: 0;
    padding: 2px 4px;
  }

  .chronos-day__more-btn {
    width: 100%;
    height: 20px;
    border: none;
    background: #94a3b8;
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    border-radius: 2px;
    transition: background 0.15s;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
  }

  .chronos-day__more-btn::after {
    content: '▼';
    font-size: 0.5rem;
    line-height: 1;
  }

  .chronos-day__more-btn:hover {
    background: #64748b;
  }

  .chronos-day__more-btn--expanded {
    background: #94a3b8;
  }

  .chronos-day__more-btn--expanded::after {
    content: '▲';
  }

  .chronos-day--drag-over {
    background-color: var(--chronos-bg-today, #f0f7ff);
    border: 2px dashed var(--accent);
  }
  </style>
