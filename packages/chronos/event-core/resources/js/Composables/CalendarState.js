import { ref, computed } from 'vue';
import { isToday } from '../Utils/dateUtils.js';

/**
 * makeCalendarState - composable для управления состоянием календаря
 * @returns {Object} Объект с состоянием и методами календаря
 * @returns {Ref<Date>} currentViewDate - Текущая дата просмотра
 * @returns {ComputedRef<Array>} daysInMonth - Массив дней для отображения (42 дня)
 * @returns {Function} setMonth - Функция для смены месяца
 */
export function makeCalendarState() {
    const currentViewDate = ref(new Date());

    const daysInMonth = computed(() => {
        const year = currentViewDate.value.getFullYear();
        const month = currentViewDate.value.getMonth();

        const firstDayOfMonth = new Date(year, month, 1);
        const dayOfWeek = firstDayOfMonth.getDay();
        const startOffset = (dayOfWeek === 0 ? 7 : dayOfWeek) - 1;

        const startDate = new Date(firstDayOfMonth);
        startDate.setDate(startDate.getDate() - startOffset);

        const days = [];
        for (let i = 0; i < 42; i++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);

            days.push({
                date: new Date(date),
                isCurrentMonth: date.getMonth() === month,
                isToday: isToday(date),
            });
        }
        return days;
    });

    const setMonth = (offset) => {
        const newDate = new Date(currentViewDate.value);
        newDate.setMonth(newDate.getMonth() + offset);
        currentViewDate.value = newDate;
    };

    return {
        currentViewDate,
        daysInMonth,
        setMonth
    };
}