<template>
  <div class="user-settings">
    <h2 class="user-settings__title">Настройки профиля</h2>
    <p class="user-settings__description">
      Управляйте настройками вашего аккаунта
    </p>

    <div v-if="isLoading" class="user-settings__loading">
      Загрузка...
    </div>

    <form v-else @submit.prevent="handleSubmit" class="user-settings__form">
      <div class="user-settings__section">
        <h3 class="user-settings__section-title">Основные настройки</h3>
        
        <div class="user-settings__field">
          <label class="user-settings__label">Часовой пояс</label>
          <select
            v-model="formData.timezone"
            class="user-settings__select"
          >
            <option value="UTC">UTC</option>
            <option value="Europe/Moscow">Москва (UTC+3)</option>
            <option value="Europe/London">Лондон (UTC+0)</option>
            <option value="America/New_York">Нью-Йорк (UTC-5)</option>
            <option value="America/Los_Angeles">Лос-Анджелес (UTC-8)</option>
            <option value="Asia/Tokyo">Токио (UTC+9)</option>
          </select>
        </div>

        <div class="user-settings__field">
          <label class="user-settings__label">Язык интерфейса</label>
          <select
            v-model="formData.language"
            class="user-settings__select"
          >
            <option value="ru">Русский</option>
            <option value="en">English</option>
          </select>
        </div>
      </div>

      <div class="user-settings__section">
        <h3 class="user-settings__section-title">Уведомления</h3>
        
        <div class="user-settings__field">
          <label class="user-settings__checkbox-label">
            <input
              type="checkbox"
              v-model="formData.email_notifications"
              class="user-settings__checkbox"
            />
            <span>Email-уведомления о событиях</span>
          </label>
        </div>

        <div class="user-settings__field">
          <label class="user-settings__checkbox-label">
            <input
              type="checkbox"
              v-model="formData.push_notifications"
              class="user-settings__checkbox"
            />
            <span>Push-уведомления</span>
          </label>
        </div>
      </div>

      <button
        type="submit"
        class="user-settings__submit"
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

const formData = ref({
  timezone: 'Europe/Moscow',
  language: 'ru',
  email_notifications: true,
  push_notifications: false,
});

const isLoading = ref(true);
const isSubmitting = ref(false);

const loadSettings = async () => {
  try {
    const response = await axios.get('/api/settings');
    response.data.forEach(setting => {
      if (setting.key in formData.value) {
        formData.value[setting.key] = setting.value === 'true' || setting.value === true;
      }
    });
  } catch (error) {
    console.error('Failed to load settings:', error);
  } finally {
    isLoading.value = false;
  }
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  try {
    const settingsArray = Object.entries(formData.value).map(([key, value]) => ({
      key,
      value: String(value),
    }));

    await axios.put('/api/settings', { settings: settingsArray });
    alert('Настройки успешно обновлены');
  } catch (error) {
    console.error('Failed to update settings:', error);
    alert('Не удалось обновить настройки');
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  loadSettings();
});
</script>

<style scoped>
.user-settings {
  max-width: 600px;
  margin: 0 auto;
  padding: 24px;
}

.user-settings__title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 8px;
}

.user-settings__description {
  color: #64748b;
  margin-bottom: 24px;
}

.user-settings__loading {
  text-align: center;
  padding: 40px;
  color: #64748b;
}

.user-settings__form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.user-settings__section {
  background: #f8fafc;
  padding: 20px;
  border-radius: 8px;
}

.user-settings__section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 16px;
}

.user-settings__field {
  margin-bottom: 16px;
}

.user-settings__field:last-child {
  margin-bottom: 0;
}

.user-settings__label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #1e293b;
  margin-bottom: 8px;
}

.user-settings__select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1e293b;
  background: white;
  cursor: pointer;
}

.user-settings__select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.user-settings__checkbox-label {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  font-size: 0.875rem;
  color: #1e293b;
}

.user-settings__checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.user-settings__submit {
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

.user-settings__submit:hover:not(:disabled) {
  background: #4338ca;
}

.user-settings__submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
