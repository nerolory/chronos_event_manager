/**
 * Events API Service - сервис для работы с событиями
 */

import { apiService } from './apiService.js';

class EventsApiService {
  /**
   * Получает события за период
   */
  async fetchEvents(start, end) {
    return apiService.get('/events', { start, end });
  }

  /**
   * Создает новое событие
   */
  async createEvent(payload) {
    return apiService.post('/events', payload);
  }

  /**
   * Обновляет событие
   */
  async updateEvent(id, payload) {
    return apiService.put(`/events/${id}`, payload);
  }

  /**
   * Удаляет событие
   */
  async deleteEvent(id) {
    return apiService.delete(`/events/${id}`);
  }
}

export const eventsApiService = new EventsApiService();
export default EventsApiService;
