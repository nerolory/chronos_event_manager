import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

export function useRegistration() {
    const loading = ref(false);
    const isReady = ref(false);
    const errors = ref({});
    const consentTypes = ref([]);
    
    // Состояние для попапа (уведомления)
    const notification = reactive({
        show: false,
        type: 'success', // success или error
        message: ''
    });

    const form = reactive({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        push_token: null,
        consents: {} 
    });

    // Показ уведомления на время
    const triggerNotification = (type, message) => {
        notification.type = type;
        notification.message = message;
        notification.show = true;
        
        // Скрываем через 3 секунды, если не произошло редиректа
        setTimeout(() => {
            notification.show = false;
        }, 3000);
    };

    onMounted(async () => {
        try {
            const response = await axios.get('/api/consents');
            const data = response.data.data || response.data;
            consentTypes.value = data;

            data.forEach(consent => {
                form.consents[consent.slug] = false;
            });
            
            // Форма готова только после успешной загрузки согласий
            isReady.value = true;
        } catch (e) {
            console.error('Ошибка загрузки справочника согласий:', e);
            triggerNotification('error', 'Не удалось загрузить обязательные согласия. Попробуйте обновить страницу.');
        }
    });

    const submitForm = async () => {
        loading.value = true;
        errors.value = {};

        try {
            // Преобразуем consents из объекта {slug: boolean} в массив ID принятых согласий
            const acceptedConsentIds = consentTypes.value
                .filter(c => form.consents[c.slug] === true)
                .map(c => c.id);

            const payload = {
                ...form,
                consents: acceptedConsentIds
            };

            const response = await axios.post('/register-web', payload);

            if (response.data.success) {
                triggerNotification('success', 'Регистрация прошла успешно!');
                // Задержка перед редиректом, чтобы пользователь увидел сообщение
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1500);
            }
        } catch (e) {
            loading.value = false;
            if (e.response?.status === 422) {
                errors.value = e.response.data.errors;
                triggerNotification('error', 'Проверьте правильность заполнения полей.');
            } else {
                errors.value = { message: ['Ошибка сервера.'] };
                triggerNotification('error', 'Произошла ошибка при регистрации.');
            }
        }
    };

    return { 
        form, 
        loading, 
        isReady, 
        errors, 
        consentTypes, 
        notification, 
        submitForm 
    };
}