/**
 * API Service - централизованный сервис для HTTP запросов
 */

class ApiService {
  constructor() {
    this.baseURL = '/api/chronos';
  }

  /**
   * Получает CSRF токен из мета-тега
   */
  getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  /**
   * Получает базовые заголовки для запросов
   */
  getHeaders(additionalHeaders = {}) {
    return {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': this.getCsrfToken(),
      'Accept': 'application/json',
      ...additionalHeaders,
    };
  }

  /**
   * Обрабатывает ответ от сервера
   */
  async handleResponse(response) {
    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: `HTTP ${response.status}` }));
      throw error;
    }
    return response.json();
  }

  /**
   * Выполняет GET запрос
   */
  async get(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = `${this.baseURL}${endpoint}${queryString ? `?${queryString}` : ''}`;
    const response = await fetch(url);
    return this.handleResponse(response);
  }

  /**
   * Выполняет POST запрос
   */
  async post(endpoint, data) {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(data),
    });
    return this.handleResponse(response);
  }

  /**
   * Выполняет PUT запрос
   */
  async put(endpoint, data) {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      method: 'PUT',
      headers: this.getHeaders(),
      body: JSON.stringify(data),
    });
    return this.handleResponse(response);
  }

  /**
   * Выполняет DELETE запрос
   */
  async delete(endpoint) {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      method: 'DELETE',
      headers: this.getHeaders(),
    });
    return this.handleResponse(response);
  }
}

// Экспорт singleton экземпляра
export const apiService = new ApiService();

// Экспорт класса для возможности создания тестовых экземпляров
export default ApiService;
