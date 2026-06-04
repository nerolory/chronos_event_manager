<template>
  <div class="kinetic-wrapper">
    <div
      ref="tzBar"
      class="tz-bar"
    >
      <!-- 24 кнопки-циферблата генерируются JS -->
    </div>
    <div
      ref="grid"
      class="kinetic-grid"
    >
      <!-- 28×8 = 224 ячейки генерируются JS -->
    </div>
    <div
      ref="debug"
      class="kinetic-debug"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

// ── Состояния циферблата ────────────────────────────────────────────────────
const STATES = {
  'P': { h: 225, m:  45 },
  'A': { h:   0, m:  90 },
  'B': { h:   0, m: 180 },
  'C': { h:   0, m: 270 },
  'D': { h:  90, m: 180 },
  'E': { h: 270, m: 180 },
  'F': { h:  90, m: 270 },
  'G': { h:  90, m:   0 },
  'H': { h: 270, m:   0 },
  'I': { h: 180, m:  90 },
};

// ── Шрифт 4×6: буквы состояний ───────────────────────────────────────────────
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
const ROWS = 8;
const COLS = 28;
const DIGIT_ROW_START = 1;
const BORDER_COLS = new Set([0, 9, 18, 27]);
const BORDER_ROWS = new Set([0, 7]);
const DIGIT_BLOCKS = [
  { startCol: 1,  digitIndex: 0 },
  { startCol: 5,  digitIndex: 1 },
  { startCol: 10, digitIndex: 2 },
  { startCol: 14, digitIndex: 3 },
  { startCol: 19, digitIndex: 4 },
  { startCol: 23, digitIndex: 5 },
];

// ── Refs ───────────────────────────────────────────────────────────────────
const grid = ref(null);
const tzBar = ref(null);
const debug = ref(null);

// ── Состояние ───────────────────────────────────────────────────────────────
const cells = [];
const tzClocks = [];
let intervalId = null;
let activeTz = null;

// ── Функции ─────────────────────────────────────────────────────────────────
function getAnglesForKey(key) {
  const s = STATES[key] || STATES['P'];
  return [s.h, s.m];
}

function advanceCW(current, targetDeg) {
  const cur360 = ((current % 360) + 360) % 360;
  let delta = ((targetDeg - cur360) + 360) % 360;
  if (delta === 0) delta = 0;
  return current + delta;
}

function buildCellMap() {
  const map = [];
  for (let r = 0; r < ROWS; r++) {
    map[r] = [];
    for (let c = 0; c < COLS; c++) {
      const isBorderRow = BORDER_ROWS.has(r);
      const isBorderCol = BORDER_COLS.has(c);
      const isBorder = isBorderRow || isBorderCol;

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

function setHand(handEl, absAngle) {
  handEl.style.transform = `translateX(-50%) rotate(${absAngle}deg)`;
}

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

function tickTzClocks() {
  tzClocks.forEach(tc => {
    const { h, m } = getTimeForOffset(tc.offset);
    const hDeg = (h % 12) * 30 + m * 0.5;
    const mDeg = m * 6;

    tc.curH = advanceCW(tc.curH, hDeg);
    tc.curM = advanceCW(tc.curM, mDeg);
    tc.handH.style.transform = `translateX(-50%) rotate(${tc.curH}deg)`;
    tc.handM.style.transform = `translateX(-50%) rotate(${tc.curM}deg)`;
  });
}

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
  debug.value.textContent = `${hh}:${mm}:${ss}  ${activeTz.full} (UTC${sign}${activeTz.offset})`;
}

// ── Lifecycle ───────────────────────────────────────────────────────────────
onMounted(() => {
  // Определяем локальный офсет
  const localOffset = -(new Date().getTimezoneOffset() / 60);
  activeTz = TIMEZONES.find(tz => tz.offset === localOffset) || TIMEZONES[13];

  // Генерируем ячейки
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
      grid.value.appendChild(cell);

      cells[r][c] = { el: cell, handH, handM, curH: 225, curM: 45 };
    }
  }

  // Генерируем TZ кнопки
  TIMEZONES.forEach(tz => {
    const btn = document.createElement('div');
    btn.className = 'tz-btn' + (tz.offset === activeTz.offset ? ' active' : '');
    btn.title = tz.full;

    const face = document.createElement('div');
    face.className = 'tz-clock';

    const handH = document.createElement('div');
    handH.className = 'kc__hand kc__hand--h';
    const handM = document.createElement('div');
    handM.className = 'kc__hand kc__hand--m';
    face.appendChild(handH);
    face.appendChild(handM);

    const label = document.createElement('div');
    label.className = 'tz-label';
    label.textContent = tz.short;

    btn.appendChild(face);
    btn.appendChild(label);
    tzBar.value.appendChild(btn);

    tzClocks.push({ btn, handH, handM, curH: 225, curM: 45, offset: tz.offset });

    btn.addEventListener('click', () => {
      activeTz = tz;
      document.querySelectorAll('.tz-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

  tick();
  intervalId = setInterval(tick, 1000);
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<style>
.kinetic-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
}

/* ─── Сетка 28×8 ─── */
.kinetic-grid {
  display: grid;
  grid-template-columns: repeat(28, 32px);
  grid-template-rows: repeat(8, 32px);
  gap: 2px;
}

/* ─── Одна ячейка-циферблат ─── */
.kc {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #e8e4df;
  border: 1px solid #d4cfc9;
  position: relative;
  overflow: hidden;
  transition: box-shadow 0.4s ease;
  box-shadow: 0 2px 4px rgb(0 0 0 / 10%), inset 0 1px 0 rgb(255 255 255 / 80%);
}

.kc.active {
  box-shadow: 0 0 0 1.5px #38BDF8, 0 2px 4px rgb(0 0 0 / 10%), inset 0 1px 0 rgb(255 255 255 / 80%);
}

/* ─── Inactive часы с градиентом ─── */
.kc.inactive {
  background: linear-gradient(135deg, #e8e4df 0%, #d4cfc9 100%) !important;
}

/* ─── Бордерные часы (тёмные) ─── */
.kc.border-clock {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
  border-color: #334155;
  box-shadow: 0 2px 4px rgb(0 0 0 / 20%), inset 0 1px 0 rgb(255 255 255 / 10%);
}

.kc.border-clock .kc__hand {
  background: linear-gradient(to bottom, #94a3b8, #64748b);
}

.kc.border-clock::after {
  background: #1e293b;
  border-color: #94a3b8;
}

/* ─── Стрелки ─── */
.kc__hand {
  position: absolute;
  top: 0;
  left: 50%;
  width: 3px;
  height: 50%;
  transform-origin: bottom center;
  transform: translateX(-50%) rotate(0deg);
  border-radius: 3px 3px 0 0;
  background: #3a3530;
  will-change: transform;
  transition: transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1;
}

.kc__hand--h,
.kc__hand--m {
  height: 50%;
}

.kc.inactive .kc__hand {
  background: #c8c3be;
  transition: transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
}

.kc.border-clock.inactive .kc__hand {
  background: #94a3b8;
}

/* Центральная точка */
.kc::after {
  content: '';
  position: absolute;
  width: 4px;
  height: 4px;
  background: #e8e4df;
  border: 1px solid #3a3530;
  border-radius: 50%;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 3;
}

.kc.inactive::after {
  border-color: #c8c3be;
}

/* ─── Панель часовых поясов ─── */
.tz-bar {
  display: grid;
  grid-template-columns: repeat(12, auto);
  gap: 6px;
  justify-content: center;
  max-width: calc(28 * 32px + 27 * 2px);
}

.tz-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 8px;
  border: 1.5px solid transparent;
  background: transparent;
  transition: border-color 0.2s, background 0.2s;
  user-select: none;
}

.tz-btn:hover {
  background: rgb(56 189 248 / 7%);
  border-color: rgb(56 189 248 / 30%);
}

.tz-btn.active {
  border-color: #38BDF8;
  background: rgb(56 189 248 / 12%);
}

.tz-clock {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, #e8e4df 0%, #d4cfc9 100%);
  border: 1px solid #d4cfc9;
  position: relative;
  overflow: hidden;
  pointer-events: none;
  box-shadow: 0 1px 2px rgb(0 0 0 / 10%), inset 0 1px 0 rgb(255 255 255 / 80%);
}

.tz-btn.active .tz-clock {
  background: linear-gradient(135deg, #e8e4df 0%, #d4cfc9 100%);
}

.tz-clock .kc__hand {
  width: 2px;
  height: 50%;
  transition: transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
  background: linear-gradient(to bottom, #3a3530, #5a5550);
}

.tz-clock::after {
  content: '';
  position: absolute;
  width: 2px;
  height: 2px;
  background: #e8e4df;
  border: 1px solid #3a3530;
  border-radius: 50%;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  z-index: 3;
}

.tz-label {
  font-family: monospace;
  font-size: 12px;
  color: #64748b;
  white-space: nowrap;
}

.tz-btn.active .tz-label {
  color: #38BDF8;
}

/* ─── Debug панель ─── */
.kinetic-debug {
  color: #64748b;
  font-size: 14px;
  letter-spacing: 0.05em;
  font-family: monospace;
}
</style>
