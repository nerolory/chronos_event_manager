<template>
  <div class="recurrence-builder">
    <div class="recurrence-builder__row">
      <label class="recurrence-builder__label">Повторение</label>
      <select
        v-model="freq"
        class="recurrence-builder__select"
      >
        <option value="">
          Не повторяется
        </option>
        <option value="DAILY">
          Каждый день
        </option>
        <option value="WEEKLY">
          По дням недели
        </option>
        <option value="MONTHLY">
          Каждый месяц
        </option>
      </select>
    </div>

    <template v-if="freq === 'WEEKLY'">
      <div class="recurrence-builder__row">
        <label class="recurrence-builder__label">Дни</label>
        <div class="recurrence-builder__days">
          <button
            v-for="day in weekDays"
            :key="day.code"
            type="button"
            :class="['recurrence-builder__day-btn', { active: selectedDays.includes(day.code) }]"
            @click="toggleDay(day.code)"
          >
            {{ day.label }}
          </button>
        </div>
      </div>
    </template>

    <template v-if="freq === 'MONTHLY'">
      <div class="recurrence-builder__row">
        <label class="recurrence-builder__label">День месяца</label>
        <input
          v-model.number="byMonthDay"
          type="number"
          min="1"
          max="31"
          class="recurrence-builder__input"
          placeholder="1–31"
        >
      </div>
    </template>

    <template v-if="freq">
      <div class="recurrence-builder__row">
        <label class="recurrence-builder__label">Завершение</label>
        <select
          v-model="endType"
          class="recurrence-builder__select"
        >
          <option value="never">
            Никогда
          </option>
          <option value="count">
            После N раз
          </option>
          <option value="until">
            До даты
          </option>
        </select>
      </div>

      <div
        v-if="endType === 'count'"
        class="recurrence-builder__row"
      >
        <label class="recurrence-builder__label">Количество</label>
        <input
          v-model.number="count"
          type="number"
          min="1"
          max="365"
          class="recurrence-builder__input"
          placeholder="10"
        >
      </div>

      <div
        v-if="endType === 'until'"
        class="recurrence-builder__row"
      >
        <label class="recurrence-builder__label">До</label>
        <input
          v-model="until"
          type="date"
          class="recurrence-builder__input"
        >
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const emit = defineEmits(['update:modelValue']);

defineProps({
  modelValue: { type: String, default: '' },
});

const weekDays = [
  { code: 'MO', label: 'Пн' },
  { code: 'TU', label: 'Вт' },
  { code: 'WE', label: 'Ср' },
  { code: 'TH', label: 'Чт' },
  { code: 'FR', label: 'Пт' },
  { code: 'SA', label: 'Сб' },
  { code: 'SU', label: 'Вс' },
];

const freq = ref('');
const selectedDays = ref([]);
const byMonthDay = ref(null);
const endType = ref('never');
const count = ref(10);
const until = ref('');

function toggleDay(code) {
  const idx = selectedDays.value.indexOf(code);
  if (idx === -1) selectedDays.value.push(code);
  else selectedDays.value.splice(idx, 1);
}

const rrule = computed(() => {
  if (!freq.value) return '';

  let rule = `FREQ=${freq.value}`;

  if (freq.value === 'WEEKLY' && selectedDays.value.length) {
    rule += `;BYDAY=${selectedDays.value.join(',')}`;
  }

  if (freq.value === 'MONTHLY' && byMonthDay.value) {
    rule += `;BYMONTHDAY=${byMonthDay.value}`;
  }

  if (endType.value === 'count' && count.value) {
    rule += `;COUNT=${count.value}`;
  } else if (endType.value === 'until' && until.value) {
    const d = new Date(until.value);
    const iso = d.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
    rule += `;UNTIL=${iso}`;
  }

  return rule;
});

watch(rrule, val => emit('update:modelValue', val));
</script>

<style scoped>
.recurrence-builder {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.recurrence-builder__row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.recurrence-builder__label {
  width: 110px;
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
  flex-shrink: 0;
}

.recurrence-builder__select,
.recurrence-builder__input {
  flex: 1;
  padding: 8px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1e293b;
  background: #fff;
  outline: none;
  transition: border-color 0.15s;
}

.recurrence-builder__select:focus,
.recurrence-builder__input:focus {
  border-color: #4f46e5;
}

.recurrence-builder__days {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.recurrence-builder__day-btn {
  padding: 6px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #fff;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}

.recurrence-builder__day-btn.active {
  background: #4f46e5;
  border-color: #4f46e5;
  color: #fff;
}

.recurrence-builder__day-btn:hover:not(.active) {
  background: #f1f5f9;
  border-color: #94a3b8;
}
</style>
