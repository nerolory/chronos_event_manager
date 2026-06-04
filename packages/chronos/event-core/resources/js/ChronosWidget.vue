<template>
  <div class="chronos-widget">
    <header class="chronos-widget__header">
      <div class="chronos-widget__kinetic">
        <KineticEngine />
      </div>
      <div class="chronos-widget__controls">
        <div class="chronos-widget__brand">
          <h2 class="chronos-widget__title">
            {{ formattedDate }}
          </h2>
        </div>
        <div class="chronos-widget__nav">
          <BaseButton
            variant="ghost"
            @click="setMonth(-1)"
          >
            <span class="nav-icon">&larr;</span> Назад
          </BaseButton>
          <BaseButton
            variant="ghost"
            @click="setToday"
          >
            Сегодня
          </BaseButton>
          <BaseButton
            variant="ghost"
            @click="setMonth(1)"
          >
            Вперед <span class="nav-icon">&rarr;</span>
          </BaseButton>
        </div>
      </div>
    </header>

    <div class="chronos-widget__layout">
      <SidePanel
        :selected-date="store.selectedDate"
        :events="store.selectedDate ? store.eventsForSelectedDate : store.upcomingEvents"
        :is-loading="store.isLoading"
        @add-event="openAddForm"
        @edit-event="openEditForm"
        @delete-event="handleDeleteEvent"
      />

      <main class="chronos-widget__body">
        <div
          v-if="store.isLoading"
          class="chronos-widget__skeleton"
        >
          <div
            v-for="i in 35"
            :key="i"
            class="skeleton-cell"
          />
        </div>
        <MonthGrid
          v-else
          :calendar-days="daysInMonth"
          :events="store.events"
          @select-date="handleDaySelection"
          @event-drop="handleEventDrop"
          @show-more-events="handleShowMoreEvents"
        />
      </main>
    </div>

    <EventForm
      v-model="showEventForm"
      :event="editingEvent"
      :initial-date="store.selectedDate"
      @saved="onEventSaved"
    />
  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import { makeCalendarState } from './Composables/CalendarState.js';
import { useCalendarStore } from './Stores/useCalendarStore.js';
import MonthGrid from './Components/Organisms/MonthGrid.vue';
import SidePanel from './Components/Organisms/SidePanel.vue';
import EventForm from './Components/Organisms/EventForm.vue';
import BaseButton from './Components/Atoms/BaseButton.vue';
import KineticEngine from '@chronos/Components/Organisms/KineticEngine.vue';

const { currentViewDate, daysInMonth, setMonth } = makeCalendarState();
const store = useCalendarStore();

const showEventForm = ref(false);
const editingEvent = ref(null);

const formattedDate = computed(() =>
  currentViewDate.value.toLocaleString('ru-RU', { month: 'long', year: 'numeric' })
);

const fetchEvents = () => {
  if (!daysInMonth.value.length) return;
  const startDate = new Date(daysInMonth.value[0].date);
  startDate.setDate(startDate.getDate() - 7); // На неделю раньше
  const endDate = new Date(daysInMonth.value[daysInMonth.value.length - 1].date);
  endDate.setDate(endDate.getDate() + 7); // На неделю позже

  const start = startDate.toISOString().split('T')[0];
  const end = endDate.toISOString().split('T')[0];
  store.fetchEvents(start, end);
};

const setToday = () => {
  currentViewDate.value = new Date();
};

const handleDaySelection = (date) => {
  store.selectDate(date);
};

const openAddForm = () => {
  editingEvent.value = null;
  showEventForm.value = true;
};

const openEditForm = (event) => {
  editingEvent.value = event;
  showEventForm.value = true;
};

const handleDeleteEvent = async (event) => {
  if (!confirm(`Удалить событие "${event.title}"?`)) return;
  await store.deleteEvent(event.event_id || event.id);
};

const onEventSaved = () => {
  fetchEvents();
};

const handleEventDrop = async ({ eventId, event, targetDate }) => {
  try {
    const year = targetDate.getFullYear();
    const month = String(targetDate.getMonth() + 1).padStart(2, '0');
    const day = String(targetDate.getDate()).padStart(2, '0');
    const targetDateStr = `${year}-${month}-${day} 12:00:00`;
    await store.updateEventDate(eventId || event.event_id, targetDateStr);
    fetchEvents();
  } catch (error) {
    alert('Не удалось переместить событие');
  }
};

const handleShowMoreEvents = ({ date }) => {
  store.selectDate(date);
};

watch(currentViewDate, fetchEvents);
onMounted(fetchEvents);
</script>
  
  <style lang="scss" scoped>
  .chronos-widget {
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 10%);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    min-height: 700px;
  
    &__header {
      display: flex;
      flex-direction: column;
      padding: 20px 24px;
      border-bottom: 1px solid #e2e8f0;
      background: #fff;
      gap: 16px;
    }

    &__kinetic {
      display: flex;
      justify-content: center;
      width: 100%;
    }

    &__controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
    }
  
    &__layout {
      display: grid;
      grid-template-columns: 280px 1fr;
      flex: 1;
    }
  
    &__sidebar {
      padding: 24px;
      background: #f8fafc;
      border-right: 1px solid #e2e8f0;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
  
    &__nav {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #f1f5f9;
      padding: 4px;
      border-radius: 8px;
    }
  
    &__title {
      font-size: 1.5rem;
      font-weight: 800;
      color: #0f172a;
      text-transform: capitalize;
    }
  
    &__body {
      background: #fff;
      overflow-y: auto;
    }
  }
  
  .stats-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgb(0 0 0 / 5%);
  
    &__label {
      display: block;
      font-size: 0.75rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.025em;
      margin-bottom: 4px;
    }
  
    &__value {
      font-size: 2rem;
      font-weight: 800;
      color: #1e293b;
    }
  }
  
  .settings-btn {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-weight: 500;
    color: #475569;
    transition: all 0.2s ease;
    
    &:hover {
      background: #f1f5f9;
      color: #1e293b;
      border-color: #cbd5e1;
    }
  }
  
  .nav-icon {
    font-family: monospace;
  }

  .chronos-widget__skeleton {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--chronos-border, #e2e8f0);
  }

  .skeleton-cell {
    min-height: 120px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.4s infinite;
  }

  @keyframes skeleton-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }
  </style>
