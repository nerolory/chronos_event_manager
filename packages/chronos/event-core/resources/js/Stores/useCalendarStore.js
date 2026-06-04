import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { eventsApiService } from '../Services/eventsApiService.js';
import { preserveTime, formatDateLocal } from '../Utils/dateUtils.js';

/**
 * useCalendarStore - Pinia store для управления событиями календаря
 * @returns {Object} Объект с состоянием и методами для работы с событиями
 * @returns {Ref<Array>} events - Массив событий
 * @returns {Ref<Boolean>} isLoading - Состояние загрузки
 * @returns {Ref<Date|null>} selectedDate - Выбранная дата
 * @returns {Ref<Date>} currentViewDate - Текущая дата просмотра
 * @returns {ComputedRef<Array>} eventsForSelectedDate - События для выбранной даты
 * @returns {ComputedRef<Number>} todayEventsCount - Количество событий сегодня
 * @returns {ComputedRef<Array>} upcomingEvents - Ближайшие события
 * @returns {Function} fetchEvents - Функция для загрузки событий
 * @returns {Function} createEvent - Функция для создания события
 * @returns {Function} updateEvent - Функция для обновления события
 * @returns {Function} updateEventDate - Функция для обновления даты события
 * @returns {Function} deleteEvent - Функция для удаления события
 * @returns {Function} selectDate - Функция для выбора даты
 */
export const useCalendarStore = defineStore('calendar', () => {
    const events = ref([]);
    const isLoading = ref(false);
    const selectedDate = ref(null);
    const currentViewDate = ref(new Date());

    const eventsForSelectedDate = computed(() => {
        if (!selectedDate.value) return [];
        const targetStr = selectedDate.value.toDateString();
        return events.value.filter(event => {
            const d = new Date(event.start || event.starts_at);
            return d.toDateString() === targetStr;
        });
    });

    const todayEventsCount = computed(() => {
        const todayStr = new Date().toDateString();
        return events.value.filter(event => {
            const d = new Date(event.start || event.starts_at);
            return d.toDateString() === todayStr;
        }).length;
    });

    const upcomingEvents = computed(() => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return events.value
            .filter(event => {
                const d = new Date(event.start || event.starts_at);
                const eventDate = new Date(d);
                eventDate.setHours(0, 0, 0, 0);
                return eventDate >= today;
            })
            .sort((a, b) => {
                const dateA = new Date(a.start || a.starts_at);
                const dateB = new Date(b.start || b.starts_at);
                return dateA - dateB;
            })
            .slice(0, 10);
    });

    async function fetchEvents(start, end) {
        isLoading.value = true;
        try {
            const result = await eventsApiService.fetchEvents(start, end);
            events.value = result.data || [];
        } catch (error) {
            // Error silently
        } finally {
            isLoading.value = false;
        }
    }

    async function createEvent(payload) {
        const result = await eventsApiService.createEvent(payload);
        events.value.push(result.data);
        return result.data;
    }

    async function updateEvent(id, payload) {
        const result = await eventsApiService.updateEvent(id, payload);
        const idx = events.value.findIndex(e => e.event_id === id || e.id === id);
        if (idx !== -1) events.value[idx] = result.data;
        return result.data;
    }

    async function updateEventDate(id, newDate) {
        const event = events.value.find(e => e.event_id === id || e.id === id);
        if (!event) throw new Error('Event not found');

        const startDate = new Date(event.start || event.starts_at);
        const endDate = new Date(event.end || event.ends_at);
        const duration = endDate - startDate;

        const newStartDate = preserveTime(startDate, newDate);
        const newEndDate = new Date(newStartDate.getTime() + duration);

        const payload = {
            start: formatDateLocal(newStartDate),
            starts_at: formatDateLocal(newStartDate),
            end: formatDateLocal(newEndDate),
            ends_at: formatDateLocal(newEndDate),
        };

        return await updateEvent(id, payload);
    }

    async function deleteEvent(id) {
        await eventsApiService.deleteEvent(id);
        events.value = events.value.filter(e => e.event_id !== id && e.id !== id);
    }

    function selectDate(date) {
        selectedDate.value = date;
    }

    return {
        events,
        isLoading,
        selectedDate,
        currentViewDate,
        eventsForSelectedDate,
        todayEventsCount,
        upcomingEvents,
        fetchEvents,
        createEvent,
        updateEvent,
        updateEventDate,
        deleteEvent,
        selectDate,
    };
});
