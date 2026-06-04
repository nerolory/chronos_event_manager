/**
 * CalendarLayoutProcessor - модуль для расчета позиций событий в календаре
 * Классифицирует события для каждого дня по типам:
 * - Тип 1: Многодневные события, которые проходят через день (начались раньше)
 * - Тип 2: Многодневные события, которые начинаются в этот день
 * - Тип 3: Однодневные события
 */

import { formatDateISO, isPast } from '../Utils/dateUtils.js';

export class CalendarLayoutProcessor {
  constructor(calendarDays, events, options = {}) {
    this.calendarDays = calendarDays;
    this.events = events;
    this.options = {
      maxVisibleEvents: options.maxVisibleEvents || 2,
      weekendDays: options.weekendDays || [5, 6], // Сб, Вс (0-6, где 0 - Пн)
      holidays: options.holidays || [], // Массив строк дат 'YYYY-MM-DD'
    };
  }

  /**
   * Основной метод для расчета раскладки событий
   */
  calculateLayout() {
    const result = {
      dayEvents: {}, // Ключ: индекс дня, значение: массив событий с типом
      hiddenEventsCount: {}, // Ключ: индекс дня, значение: количество скрытых событий
      weekendDays: new Set(),
      holidayDays: new Set(),
    };

    // Маркируем выходные и праздничные дни
    this._markSpecialDays(result);

    // Для каждого дня классифицируем события
    this.calendarDays.forEach((day, dayIndex) => {
      const dayEvents = this._classifyEventsForDay(dayIndex);
      result.dayEvents[dayIndex] = dayEvents;

      // Считаем скрытые события
      const visibleCount = Math.min(dayEvents.length, this.options.maxVisibleEvents);
      const hiddenCount = Math.max(0, dayEvents.length - visibleCount);
      if (hiddenCount > 0) {
        result.hiddenEventsCount[dayIndex] = hiddenCount;
      }
    });

    return result;
  }

  /**
   * Маркирует выходные и праздничные дни
   */
  _markSpecialDays(result) {
    this.calendarDays.forEach((day, index) => {
      const dayOfWeek = day.date.getDay(); // 0-6 (Вс-Сб)
      const adjustedDay = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // 0-6 (Пн-Вс)

      if (this.options.weekendDays.includes(adjustedDay)) {
        result.weekendDays.add(index);
      }

      const dateStr = formatDateISO(day.date);
      if (this.options.holidays.includes(dateStr)) {
        result.holidayDays.add(index);
      }
    });
  }

  /**
   * Классифицирует события для конкретного дня по типам
   */
  _classifyEventsForDay(dayIndex) {
    const dayDate = this.calendarDays[dayIndex].date;
    // Начало и конец дня для сравнения
    const dayStart = new Date(dayDate.getFullYear(), dayDate.getMonth(), dayDate.getDate());
    const dayEnd = new Date(dayDate.getFullYear(), dayDate.getMonth(), dayDate.getDate() + 1);

    const type1Events = []; // Многодневные, проходящие через день
    const type2Events = []; // Многодневные, начинающиеся сегодня
    const type3Events = []; // Однодневные

    this.events.forEach(event => {
      const eventStart = new Date(event.start || event.starts_at);
      const eventEnd = new Date(event.end || event.ends_at);

      // Проверяем, пересекает ли событие этот день
      // Событие пересекает день, если: (начало < конец дня) И (конец > начало дня)
      if (eventStart >= dayEnd || eventEnd <= dayStart) {
        return; // Событие не пересекает этот день
      }

      // Определяем тип события
      const eventStartDate = new Date(eventStart.getFullYear(), eventStart.getMonth(), eventStart.getDate());
      const eventEndDate = new Date(eventEnd.getFullYear(), eventEnd.getMonth(), eventEnd.getDate());

      const isMultiDay = eventStartDate.getTime() !== eventEndDate.getTime();

      if (isMultiDay) {
        if (eventStartDate.getTime() < dayStart.getTime()) {
          // Тип 1: событие началось раньше и проходит через этот день
          type1Events.push({
            ...event,
            type: 1,
            isMultiDay: true,
          });
        } else {
          // Тип 2: событие начинается в этот день
          type2Events.push({
            ...event,
            type: 2,
            isMultiDay: true,
          });
        }
      } else {
        // Тип 3: однодневное событие
        type3Events.push({
          ...event,
          type: 3,
          isMultiDay: false,
        });
      }
    });

    // Сортируем внутри каждого типа по длительности и прошлым событиям
    const sortEvents = (events) => {
      return events.sort((a, b) => {
        const aStart = new Date(a.start || a.starts_at);
        const bStart = new Date(b.start || b.starts_at);
        const aEnd = new Date(a.end || a.ends_at);
        const bEnd = new Date(b.end || b.ends_at);

        const aDuration = aEnd - aStart;
        const bDuration = bEnd - bStart;

        // Прошлые события имеют приоритет
        const aIsPast = isPast(aEnd);
        const bIsPast = isPast(bEnd);

        if (aIsPast && !bIsPast) return -1;
        if (!aIsPast && bIsPast) return 1;

        // По длительности (длинные первыми)
        return bDuration - aDuration;
      });
    };

    // Объединяем в порядке приоритета: тип 1 → тип 2 → тип 3
    return [
      ...sortEvents(type1Events),
      ...sortEvents(type2Events),
      ...sortEvents(type3Events),
    ];
  }
}

/**
 * Composable для использования в компонентах
 */
export function useCalendarLayoutProcessor(calendarDays, events, options) {
  const processor = new CalendarLayoutProcessor(calendarDays, events, options);
  return processor.calculateLayout();
}
