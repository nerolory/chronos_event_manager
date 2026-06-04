/**
 * Kinetic Engine — Clock Clock механика
 * Автор: А.Борисов / A.Borisov
 * За основу взята идея kinetic watch, но реализация выполнена автором самостоятельно
 *
 * Раскладка 28 × 8:
 *
 *   col:  0    1-4   5-8   9    10-13  14-17  18   19-22  23-26  27
 *         │    HH          │    MM           │    SS           │
 *        border  H1   H2  border  M1   M2  border  S1   S2  border
 *
 * Строки: 0=бордер, 1-6=цифры, 7=бордер
 *
 * Бордерные колонки (0, 9, 18, 27) и строки (0, 7) — тёмные часы.
 * Цифры: шрифт 4×6.
 *
 * CSS-модель стрелки: top:0, height:50%, transform-origin:bottom center
 *   rotate(0°)=вверх, 90°=вправо, 180°=вниз, 270°=влево
 */

'use strict';

// ── Состояния циферблата ────────────────────────────────────────────────────
// 0°=вверх, 90°=вправо, 180°=вниз, 270°=влево
// P = парковка (неактивный)
const STATES = {
  'P': { h: 225, m:  45 },
  'A': { h:   0, m:  90 },  // ↑→ угол верх-право
  'B': { h:   0, m: 180 },  // │  вертикаль
  'C': { h:   0, m: 270 },  // ↑← угол верх-лево
  'D': { h:  90, m: 180 },  // →↓ угол право-низ
  'E': { h: 270, m: 180 },  // ←↓ угол лево-низ
  'F': { h:  90, m: 270 },  // ─  горизонталь
  'G': { h:  90, m:   0 },  // →↑ угол право-верх
  'H': { h: 270, m:   0 },  // ←↑ угол лево-верх
  'I': { h: 180, m:  90 },  // ↓→ угол низ-право
};

// ── Шрифт 4×6: буквы состояний ───────────────────────────────────────────────
// Составлен пользователем в визуальном редакторе
const FONT = {
  '0': ['D','F','F','E', 'B','D','E','B', 'B','B','B','B', 'B','B','B','B', 'B','A','C','B', 'A','F','F','C'],
  '1': ['P','I','E','P', 'D','H','B','P', 'A','E','B','P', 'P','B','B','P', 'D','C','G','E', 'A','F','F','H'],
  '2': ['D','F','F','E', 'A','F','E','B', 'D','F','C','B', 'B','D','F','C', 'B','A','F','E', 'A','F','F','C'],
  '3': ['D','F','F','E', 'A','F','E','B', 'D','F','C','B', 'A','F','E','B', 'D','F','C','B', 'A','F','F','C'],
  '4': ['D','E','D','E', 'B','B','B','B', 'B','A','C','B', 'A','F','E','B', 'P','P','B','B', 'P','P','A','C'],
  '5': ['D','F','F','E', 'B','D','F','C', 'B','A','F','E', 'A','F','E','B', 'D','F','C','B', 'A','F','F','C'],
  '6': ['D','F','F','E', 'B','D','F','C', 'B','A','F','E', 'B','D','E','B', 'B','A','C','B', 'A','F','F','C'],
  '7': ['D','F','F','E', 'A','F','E','B', 'P','P','B','B', 'P','P','B','B', 'P','P','B','B', 'P','P','A','C'],
  '8': ['D','F','F','E', 'B','D','E','B', 'B','A','C','B', 'B','D','E','B', 'B','A','C','B', 'A','F','F','C'],
  '9': ['D','F','F','E', 'B','D','E','B', 'B','A','C','B', 'A','F','E','B', 'D','F','C','B', 'A','F','F','C'],
};

// ── Константы ────────────────────────────────────────────────────────────────
const PARK_H   = STATES['P'].h;
const PARK_M   = STATES['P'].m;
const FONT_COLS = 4;
const FONT_ROWS = 6;

// Получить углы [h, m] по ключу состояния
function getAnglesForKey(key) {
  const s = STATES[key] || STATES['P'];
  return [s.h, s.m];
}

// ── Раскладка матрицы 28×8 ───────────────────────────────────────────────────
// Бордерные колонки: 0, 9, 18, 27
// Бордерные строки:  0, 7
// Цифры (startCol, digitIndex):
//   H1: col 1-4,  H2: col 5-8
//   M1: col 10-13, M2: col 14-17
//   S1: col 19-22, S2: col 23-26
// Цифры в строках 1-6

// Раскладка 32×8:
// col: [0=B][1-4=H1][5=gap][6-9=H2][10=B][11-14=M1][15=gap][16-19=M2][20=B][21-24=S1][25=gap][26-29=S2][30=B]
// Нет — слишком широко. Используем 28 колонок без внутреннего зазора,
// но добавляем визуальный разделитель через CSS-класс «gap-col».
//
// Финальная раскладка 28×8:
// [0=B][1-4=H1][5-8=H2][9=B][10-13=M1][14-17=M2][18=B][19-22=S1][23-26=S2][27=B]
// Между H1 и H2 — нет физического зазора, но неактивные ячейки ВНУТРИ
// каждого блока получают класс 'kc digit-bg' (слегка серее светлого фона)
// чтобы блоки визуально читались отдельно.

// Раскладка 28×8:
// [0=B][1-4=H1][5-8=H2][9=B][10-13=M1][14-17=M2][18=B][19-22=S1][23-26=S2][27=B]

const ROWS = 8;
const COLS = 28;
const DIGIT_ROW_START = 1;

const BORDER_COLS = new Set([0, 9, 18, 27]);
const BORDER_ROWS = new Set([0, 7]);

const DIGIT_BLOCKS = [
  { startCol: 1,  digitIndex: 0 },  // H1
  { startCol: 5,  digitIndex: 1 },  // H2
  { startCol: 10, digitIndex: 2 },  // M1
  { startCol: 14, digitIndex: 3 },  // M2
  { startCol: 19, digitIndex: 4 },  // S1
  { startCol: 23, digitIndex: 5 },  // S2
];

// ── Карта ячеек: [row][col] = { isBorder, digitBlock, dr, dc } ───────────────
function buildCellMap() {
  const map = [];
  for (let r = 0; r < ROWS; r++) {
    map[r] = [];
    for (let c = 0; c < COLS; c++) {
      const isBorderRow = BORDER_ROWS.has(r);
      const isBorderCol = BORDER_COLS.has(c);
      const isBorder    = isBorderRow || isBorderCol;

      let digitBlock = null;
      if (!isBorder) {
        for (const block of DIGIT_BLOCKS) {
          const dc = c - block.startCol;
          const dr = r - DIGIT_ROW_START;
          if (dc >= 0 && dc < FONT_COLS && dr >= 0 && dr < FONT_ROWS) {
            digitBlock = { ...block, dr, dc };
            break;
          }
        }
      }

      map[r][c] = { isBorder, digitBlock };
    }
  }
  return map;
}

const cellMap = buildCellMap();

// ── Генерация DOM ─────────────────────────────────────────────────────────────
const grid = document.getElementById('kinetic-grid');
const debugEl = document.getElementById('kinetic-debug');

const cells = [];
for (let r = 0; r < ROWS; r++) {
  cells[r] = [];
  for (let c = 0; c < COLS; c++) {
    const cell = document.createElement('div');
    const handH = document.createElement('div');
    const handM = document.createElement('div');

    handH.className = 'kc__hand kc__hand--h';
    handM.className = 'kc__hand kc__hand--m';
    cell.appendChild(handH);
    cell.appendChild(handM);
    grid.appendChild(cell);

    // curH / curM — накопленный абсолютный угол (всегда растёт по часовой)
    cells[r][c] = { el: cell, handH, handM, curH: 225, curM: 45 };
  }
}

// ── Применение угла — всегда по часовой стрелке ─────────────────────────────
// targetDeg: целевой угол в диапазоне 0-359
// current:   накопленный абсолютный угол
// Возвращает новый накопленный угол.
function advanceCW(current, targetDeg) {
  const cur360 = ((current % 360) + 360) % 360;
  let delta = ((targetDeg - cur360) + 360) % 360;
  if (delta === 0) delta = 0; // стоим на месте
  return current + delta;
}

function setHand(handEl, absAngle) {
  handEl.style.transform = `translateX(-50%) rotate(${absAngle}deg)`;
}

// ── Главный рендер ────────────────────────────────────────────────────────────
function render(d0, d1, d2, d3, d4, d5) {
  const fonts = [d0, d1, d2, d3, d4, d5].map(d => FONT[String(d)] || FONT['0']);

  for (let r = 0; r < ROWS; r++) {
    for (let c = 0; c < COLS; c++) {
      const { isBorder, digitBlock } = cellMap[r][c];
      const cell = cells[r][c];

      if (isBorder) {
        cell.el.className = 'kc border-clock inactive';
        const wave = ((r + c) * 30) % 360;
        cell.curH = advanceCW(cell.curH, wave);
        cell.curM = advanceCW(cell.curM, (wave + 180) % 360);
        setHand(cell.handH, cell.curH);
        setHand(cell.handM, cell.curM);
        continue;
      }

      if (!digitBlock) {
        cell.el.className = 'kc inactive';
        cell.curH = advanceCW(cell.curH, PARK_H);
        cell.curM = advanceCW(cell.curM, PARK_M);
        setHand(cell.handH, cell.curH);
        setHand(cell.handM, cell.curM);
        continue;
      }

      const { digitIndex, dr, dc } = digitBlock;
      const font = fonts[digitIndex];
      const key = font[dr * FONT_COLS + dc] || 'P';
      const isActive = (key !== 'P');
      const [aH, aM] = getAnglesForKey(key);

      cell.el.className = isActive ? 'kc active' : 'kc inactive';
      cell.curH = advanceCW(cell.curH, aH);
      cell.curM = advanceCW(cell.curM, aM);
      setHand(cell.handH, cell.curH);
      setHand(cell.handM, cell.curM);
    }
  }
}

// ── Часовые пояса ─────────────────────────────────────────────────────────────
const TIMEZONES = [
  { offset: -12, label: 'UTC−12', short: 'AoE', full: 'Baker Island' },
  { offset: -11, label: 'UTC−11', short: 'NUT', full: 'Niue' },
  { offset: -10, label: 'UTC−10', short: 'HST', full: 'Hawaii' },
  { offset:  -9, label: 'UTC−9',  short: 'AKST', full: 'Alaska' },
  { offset:  -8, label: 'UTC−8',  short: 'PST',  full: 'Pacific Time' },
  { offset:  -7, label: 'UTC−7',  short: 'MST',  full: 'Mountain Time' },
  { offset:  -6, label: 'UTC−6',  short: 'CST',  full: 'Central Time' },
  { offset:  -5, label: 'UTC−5',  short: 'EST',  full: 'Eastern Time' },
  { offset:  -4, label: 'UTC−4',  short: 'AST',  full: 'Atlantic Time' },
  { offset:  -3, label: 'UTC−3',  short: 'ART',  full: 'Argentina' },
  { offset:  -2, label: 'UTC−2',  short: 'FNT',  full: 'Fernando de Noronha' },
  { offset:  -1, label: 'UTC−1',  short: 'CVT',  full: 'Cape Verde' },
  { offset:   0, label: 'UTC±0',  short: 'GMT',  full: 'London' },
  { offset:  +1, label: 'UTC+1',  short: 'CET',  full: 'Central European' },
  { offset:  +2, label: 'UTC+2',  short: 'EET',  full: 'Eastern European' },
  { offset:  +3, label: 'UTC+3',  short: 'MSK',  full: 'Moscow' },
  { offset:  +4, label: 'UTC+4',  short: 'GET',  full: 'Georgia' },
  { offset:  +5, label: 'UTC+5',  short: 'PKT',  full: 'Pakistan' },
  { offset:  +6, label: 'UTC+6',  short: 'IST',  full: 'India' },
  { offset:  +7, label: 'UTC+7',  short: 'ICT',  full: 'Indochina' },
  { offset:  +8, label: 'UTC+8',  short: 'CST',  full: 'China' },
  { offset:  +9, label: 'UTC+9',  short: 'JST',  full: 'Japan' },
  { offset: +10, label: 'UTC+10', short: 'AEST', full: 'Australian Eastern' },
  { offset: +11, label: 'UTC+11', short: 'AEDT', full: 'Sydney' },
];

// Определяем локальный офсет пользователя (в часах)
const localOffset = -(new Date().getTimezoneOffset() / 60);
let activeTz = TIMEZONES.find(tz => tz.offset === localOffset) || TIMEZONES[13]; // fallback to GMT

// Получить время для заданного UTC-офсета
function getTimeForOffset(offsetHours) {
  const now = new Date();
  const utcMs = now.getTime() + now.getTimezoneOffset() * 60000;
  const tzDate = new Date(utcMs + offsetHours * 3600000);
  return {
    h: tzDate.getHours(),
    m: tzDate.getMinutes(),
    s: tzDate.getSeconds(),
  };
}

// ── Генерация панели TZ ───────────────────────────────────────────────────────
const tzBar = document.getElementById('tz-bar');

// tzClocks[i] = { handH, handM, curH, curM } — живые стрелки на кнопках
const tzClocks = [];

TIMEZONES.forEach(tz => {
  const btn = document.createElement('div');
  btn.className = 'tz-btn' + (tz.offset === activeTz.offset ? ' active' : '');
  btn.title = tz.full;

  // Мини-циферблат
  const face = document.createElement('div');
  face.className = 'tz-clock';

  const handH = document.createElement('div');
  handH.className = 'kc__hand kc__hand--h';
  const handM = document.createElement('div');
  handM.className = 'kc__hand kc__hand--m';
  face.appendChild(handH);
  face.appendChild(handM);

  // Подпись — сокращённое название
  const label = document.createElement('div');
  label.className = 'tz-label';
  label.textContent = tz.short;

  btn.appendChild(face);
  btn.appendChild(label);
  tzBar.appendChild(btn);

  tzClocks.push({ btn, handH, handM, curH: 225, curM: 45, offset: tz.offset });

  btn.addEventListener('click', () => {
    activeTz = tz;
    document.querySelectorAll('.tz-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  });
});

// Обновить стрелки на всех мини-циферблатах TZ
function tickTzClocks() {
  tzClocks.forEach(tc => {
    const { h, m } = getTimeForOffset(tc.offset);
    // Часовая: 360/12 = 30° на час, +0.5° на минуту
    const hDeg = (h % 12) * 30 + m * 0.5;
    // Минутная: 360/60 = 6° на минуту
    const mDeg = m * 6;

    tc.curH = advanceCW(tc.curH, hDeg);
    tc.curM = advanceCW(tc.curM, mDeg);
    tc.handH.style.transform = `translateX(-50%) rotate(${tc.curH}deg)`;
    tc.handM.style.transform = `translateX(-50%) rotate(${tc.curM}deg)`;
  });
}

// ── Тик ───────────────────────────────────────────────────────────────────────
function tick() {
  const { h, m, s } = getTimeForOffset(activeTz.offset);

  render(
    Math.floor(h / 10), h % 10,
    Math.floor(m / 10), m % 10,
    Math.floor(s / 10), s % 10,
  );

  tickTzClocks();

  const hh = String(h).padStart(2,'0');
  const mm = String(m).padStart(2,'0');
  const ss = String(s).padStart(2,'0');
  const sign = activeTz.offset >= 0 ? '+' : '';
  debugEl.textContent = `${hh}:${mm}:${ss}  ${activeTz.full} (UTC${sign}${activeTz.offset})`;
}

tick();
setInterval(tick, 1000);
