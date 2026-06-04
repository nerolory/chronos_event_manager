<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="modelValue"
        class="event-form-overlay"
        @click.self="close"
      >
        <div class="event-form">
          <header class="event-form__header">
            <h2 class="event-form__title">
              {{ isEditing ? 'Редактировать событие' : 'Новое событие' }}
            </h2>
            <button
              class="event-form__close"
              @click="close"
            >
              ✕
            </button>
          </header>

          <form
            class="event-form__body"
            @submit.prevent="submit"
          >
            <div class="event-form__field">
              <label class="event-form__label">Название *</label>
              <input
                v-model="form.title"
                type="text"
                class="event-form__input"
                :class="{ 'event-form__input--error': errors.title }"
                placeholder="Название события"
                required
              >
              <span
                v-if="errors.title"
                class="event-form__error"
              >{{ errors.title[0] }}</span>
            </div>

            <div class="event-form__row">
              <div class="event-form__field">
                <label class="event-form__label">Начало *</label>
                <input
                  v-model="form.starts_at"
                  type="datetime-local"
                  class="event-form__input"
                  :class="{ 'event-form__input--error': errors.starts_at }"
                  required
                >
              </div>
              <div class="event-form__field">
                <label class="event-form__label">Конец *</label>
                <input
                  v-model="form.ends_at"
                  type="datetime-local"
                  class="event-form__input"
                  :class="{ 'event-form__input--error': errors.ends_at }"
                  required
                >
              </div>
            </div>

            <div class="event-form__row">
              <div class="event-form__field">
                <label class="event-form__label">Цвет</label>
                <input
                  v-model="form.color_accent"
                  type="color"
                  class="event-form__color"
                >
              </div>
            </div>

            <div class="event-form__field">
              <label class="event-form__label">Описание</label>
              <textarea
                v-model="form.description"
                class="event-form__textarea"
                rows="3"
                placeholder="Необязательно"
              />
            </div>

            <div class="event-form__field">
              <label class="event-form__label">Повторение</label>
              <RecurrenceBuilder v-model="form.recurrence_rule" />
            </div>

            <div
              v-if="serverError"
              class="event-form__server-error"
            >
              {{ serverError }}
            </div>

            <footer class="event-form__footer">
              <button
                type="button"
                class="event-form__btn event-form__btn--ghost"
                @click="close"
              >
                Отмена
              </button>
              <button
                type="submit"
                class="event-form__btn event-form__btn--primary"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'Сохранение...' : (isEditing ? 'Сохранить' : 'Создать') }}
              </button>
            </footer>
          </form>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useCalendarStore } from '../../Stores/useCalendarStore.js';
import { toLocalDatetimeInput } from '../../Utils/dateUtils.js';
import RecurrenceBuilder from '../Molecules/RecurrenceBuilder.vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  event: { type: Object, default: null },
  initialDate: { type: Date, default: null },
});

const emit = defineEmits(['update:modelValue', 'saved']);

const store = useCalendarStore();
const isSubmitting = ref(false);
const errors = ref({});
const serverError = ref('');

const isEditing = computed(() => !!props.event?.event_id);

const defaultForm = () => ({
  title: '',
  description: '',
  starts_at: props.initialDate
    ? toLocalDatetimeInput(props.initialDate)
    : toLocalDatetimeInput(new Date()),
  ends_at: props.initialDate
    ? toLocalDatetimeInput(new Date(props.initialDate.getTime() + 60 * 60 * 1000))
    : toLocalDatetimeInput(new Date(Date.now() + 60 * 60 * 1000)),
  color_accent: '#4f46e5',
  recurrence_rule: '',
});

const form = ref(defaultForm());

watch(() => props.event, (evt) => {
  if (evt) {
    form.value = {
      title: evt.title || '',
      description: evt.description || '',
      starts_at: evt.start ? toLocalDatetimeInput(new Date(evt.start)) : '',
      ends_at: evt.end ? toLocalDatetimeInput(new Date(evt.end)) : '',
      color_accent: evt.color || '#4f46e5',
      recurrence_rule: evt.recurrence_rule || '',
    };
  } else {
    form.value = defaultForm();
  }
}, { immediate: true });

async function submit() {
  isSubmitting.value = true;
  errors.value = {};
  serverError.value = '';

  try {
    const payload = { ...form.value };
    if (!payload.recurrence_rule) delete payload.recurrence_rule;

    if (isEditing.value) {
      await store.updateEvent(props.event.event_id, payload);
    } else {
      await store.createEvent(payload);
    }

    emit('saved');
    close();
  } catch (err) {
    if (err?.errors) {
      errors.value = err.errors;
    } else {
      serverError.value = 'Произошла ошибка. Попробуйте ещё раз.';
    }
  } finally {
    isSubmitting.value = false;
  }
}

function close() {
  errors.value = {};
  serverError.value = '';
  emit('update:modelValue', false);
}
</script>

<style scoped>
.event-form-overlay {
  position: fixed;
  inset: 0;
  background: rgb(15 23 42 / 50%);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
  backdrop-filter: blur(2px);
}

.event-form {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  max-width: 560px;
  box-shadow: 0 25px 50px -12px rgb(0 0 0 / 25%);
  overflow: hidden;
}

.event-form__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.event-form__title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #0f172a;
}

.event-form__close {
  background: none;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  color: #64748b;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background 0.15s;
}

.event-form__close:hover {
  background: #f1f5f9;
}

.event-form__body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-height: 70vh;
  overflow-y: auto;
}

.event-form__row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.event-form__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.event-form__label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
}

.event-form__input,
.event-form__textarea {
  padding: 9px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
  background: #fff;
}

.event-form__input:focus,
.event-form__textarea:focus {
  border-color: #4f46e5;
}

.event-form__input--error {
  border-color: #ef4444;
}

.event-form__error {
  font-size: 0.75rem;
  color: #ef4444;
}

.event-form__color {
  width: 48px;
  height: 38px;
  padding: 2px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
}

.event-form__textarea {
  resize: vertical;
  min-height: 72px;
}

.event-form__server-error {
  padding: 10px 12px;
  background: #fee2e2;
  border-radius: 8px;
  color: #dc2626;
  font-size: 0.875rem;
}

.event-form__footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid #e2e8f0;
}

.event-form__btn {
  padding: 9px 20px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  border: none;
}

.event-form__btn--ghost {
  background: #f1f5f9;
  color: #475569;
}

.event-form__btn--ghost:hover {
  background: #e2e8f0;
}

.event-form__btn--primary {
  background: #4f46e5;
  color: #fff;
}

.event-form__btn--primary:hover:not(:disabled) {
  background: #4338ca;
}

.event-form__btn--primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
