<template>
  <div class="privacy-settings">
    <h2 class="privacy-settings__title">Управление приватностью</h2>
    <p class="privacy-settings__description">
      Управляйте своими согласиями на обработку персональных данных
    </p>

    <div v-if="isLoading" class="privacy-settings__loading">
      Загрузка...
    </div>

    <form v-else @submit.prevent="handleSubmit" class="privacy-settings__form">
      <div
        v-for="consent in consents"
        :key="consent.id"
        class="privacy-settings__item"
      >
        <label class="privacy-settings__label">
          <input
            type="checkbox"
            v-model="formData[consent.id]"
            class="privacy-settings__checkbox"
          />
          <span class="privacy-settings__text">
            <strong>{{ consent.title }}</strong>
            <small>{{ consent.description }}</small>
          </span>
        </label>
      </div>

      <button
        type="submit"
        class="privacy-settings__submit"
        :disabled="isSubmitting"
      >
        {{ isSubmitting ? 'Сохранение...' : 'Сохранить изменения' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const consents = ref([]);
const formData = ref({});
const isLoading = ref(true);
const isSubmitting = ref(false);

const loadConsents = async () => {
  try {
    const response = await axios.get('/api/privacy');
    consents.value = response.data;
    
    formData.value = {};
    response.data.forEach(consent => {
      formData.value[consent.id] = consent.granted;
    });
  } catch (error) {
    console.error('Failed to load consents:', error);
  } finally {
    isLoading.value = false;
  }
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  try {
    const consentsArray = Object.entries(formData.value).map(([id, granted]) => ({
      consent_type_id: parseInt(id),
      granted,
    }));

    await axios.put('/api/privacy', { consents: consentsArray });
    alert('Согласия успешно обновлены');
  } catch (error) {
    console.error('Failed to update consents:', error);
    alert('Не удалось обновить согласия');
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  loadConsents();
});
</script>

<style scoped>
.privacy-settings {
  max-width: 600px;
  margin: 0 auto;
  padding: 24px;
}

.privacy-settings__title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 8px;
}

.privacy-settings__description {
  color: #64748b;
  margin-bottom: 24px;
}

.privacy-settings__loading {
  text-align: center;
  padding: 40px;
  color: #64748b;
}

.privacy-settings__form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.privacy-settings__item {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
}

.privacy-settings__label {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  cursor: pointer;
}

.privacy-settings__checkbox {
  width: 20px;
  height: 20px;
  margin-top: 2px;
  cursor: pointer;
}

.privacy-settings__text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.privacy-settings__text strong {
  color: #1e293b;
  font-size: 0.875rem;
}

.privacy-settings__text small {
  color: #64748b;
  font-size: 0.75rem;
  line-height: 1.4;
}

.privacy-settings__submit {
  padding: 12px 24px;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.privacy-settings__submit:hover:not(:disabled) {
  background: #4338ca;
}

.privacy-settings__submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
