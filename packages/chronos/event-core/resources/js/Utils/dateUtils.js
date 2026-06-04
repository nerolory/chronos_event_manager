/**
 * Date Utils - утилиты для работы с датами
 */

/**
 * Форматирует дату в формат YYYY-MM-DD HH:MM:SS (локальный формат)
 */
export function formatDateLocal(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

/**
 * Форматирует дату в формат YYYY-MM-DD
 */
export function formatDateISO(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

/**
 * Форматирует дату для input type="datetime-local"
 */
export function toLocalDatetimeInput(date) {
  const pad = n => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

/**
 * Создает объект Date из строки YYYY-MM-DD HH:MM:SS
 */
export function parseDateLocal(dateStr) {
  const [datePart, timePart] = dateStr.split(' ');
  const [year, month, day] = datePart.split('-').map(Number);
  const [hours, minutes, seconds] = timePart ? timePart.split(':').map(Number) : [0, 0, 0];
  return new Date(year, month - 1, day, hours, minutes, seconds);
}

/**
 * Сохраняет время из одной даты и применяет к другой
 */
export function preserveTime(sourceDate, targetDate) {
  const result = new Date(targetDate);
  result.setHours(
    sourceDate.getHours(),
    sourceDate.getMinutes(),
    sourceDate.getSeconds(),
    0
  );
  return result;
}

/**
 * Проверяет, является ли дата сегодняшним днем
 */
export function isToday(date) {
  const today = new Date();
  return date.toDateString() === today.toDateString();
}

/**
 * Проверяет, является ли дата прошлым
 */
export function isPast(date) {
  const now = new Date();
  now.setHours(0, 0, 0, 0);
  const compareDate = new Date(date);
  compareDate.setHours(0, 0, 0, 0);
  return compareDate < now;
}
