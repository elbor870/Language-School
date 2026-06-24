// === Courses Management ===
class CoursesManager {
    constructor() {
        this.coursesGrid = document.getElementById('coursesGrid');
        this.courseSelect = document.getElementById('course');
        this.form = document.getElementById('courseForm');
        
        this.init();
    }
    
    async init() {
        await this.loadCourses();
        this.setupFormValidation();
    }
    
    async loadCourses() {
        try {
            const response = await fetch('php/get_courses.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            this.renderCourses(data);
            this.populateCourseSelect(data);
        } catch (error) {
            console.log('Загрузка с сервера не удалась, использую демо-данные:', error);
            this.loadDemoCourses();
        }
    }
    
    loadDemoCourses() {
        const demoCourses = [
            { id: 1, name: 'Excel для чайников', price: 5000, description: 'Обучение работе с таблицами с нуля до продвинутого уровня', amount_students: 12, teacher_fio: 'Иванова Мария Сергеевна' },
            { id: 2, name: 'Python-разработчик', price: 45000, description: 'Полный цикл обучения от основ до создания веб-приложений', amount_students: 10, teacher_fio: 'Петров Александр Владимирович' },
            { id: 3, name: 'Веб-дизайн в Figma', price: 18000, description: 'Создание макетов сайтов и мобильных приложений', amount_students: 8, teacher_fio: 'Сидорова Анна Игоревна' },
            { id: 4, name: 'Немецкий язык с нуля', price: 20000, description: 'Грамматика, лексика и разговорная речь', amount_students: 15, teacher_fio: 'Шмидт Елена Викторовна' },
            { id: 5, name: 'Фронтенд разработка (HTML/CSS/JS)', price: 35000, description: 'Создание сайтов с нуля до адаптивной верстки', amount_students: 10, teacher_fio: 'Козлов Дмитрий Андреевич' },
            { id: 6, name: 'Управление проектами', price: 27000, description: 'Методологии Agile и Scrum для IT-проектов', amount_students: 8, teacher_fio: 'Морозова Ольга Павловна' },
            { id: 7, name: 'Python для Data Science', price: 50000, description: 'Анализ данных, визуализация и машинное обучение', amount_students: 6, teacher_fio: 'Волков Сергей Николаевич' }
        ];
        this.renderCourses(demoCourses);
        this.populateCourseSelect(demoCourses);
    }
    
    renderCourses(courses) {
        if (!this.coursesGrid) return;
        
        this.coursesGrid.innerHTML = courses.map(course => `
            <div class="course-card" data-course-id="${course.id}">
                <h3 class="course-card__title">${course.name}</h3>
                <div class="course-card__price">${this.formatPrice(course.price)} ₽</div>
                <p class="course-card__description">${course.description}</p>
                <div class="course-card__meta">
                    <span class="course-card__meta-item">
                        👥 Группа: ${course.amount_students} чел.
                    </span>
                    <span class="course-card__meta-item">
                        👨‍🏫 ${course.teacher_fio}
                    </span>
                </div>
                <button class="btn btn--primary course-card__btn" onclick="scrollToForm(${course.id})">
                    Записаться
                </button>
            </div>
        `).join('');
    }
    
    populateCourseSelect(courses) {
        if (!this.courseSelect) return;
        
        // Очищаем select, оставляя первый option
        this.courseSelect.innerHTML = '<option value="">-- Выберите курс --</option>';
        
        courses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.id;
            option.textContent = `${course.name} - ${this.formatPrice(course.price)} ₽`;
            this.courseSelect.appendChild(option);
        });
    }
    
    formatPrice(price) {
        return new Intl.NumberFormat('ru-RU').format(price);
    }
    
    setupFormValidation() {
        if (!this.form) return;
        
        const phoneInput = document.getElementById('phone');
        
        // Маска для телефона
        if (phoneInput) {
            phoneInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);
                
                let formatted = '+7(';
                if (value.length > 1) formatted += value.slice(1, 4);
                if (value.length > 4) formatted += ')-' + value.slice(4, 7);
                if (value.length > 7) formatted += '-' + value.slice(7, 9);
                if (value.length > 9) formatted += '-' + value.slice(9, 11);
                
                e.target.value = formatted;
            });
        }
        
        // Валидация и отправка формы
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            console.log('Форма отправлена');
            
            if (!this.validateForm()) {
                console.log('Валидация не пройдена');
                return;
            }
            
            console.log('Валидация пройдена, отправка данных...');
            await this.submitForm();
        });
    }
    
    validateForm() {
        let isValid = true;
        
        const fullName = document.getElementById('fullName');
        const phone = document.getElementById('phone');
        const email = document.getElementById('email');
        const course = document.getElementById('course');
        const consent = document.getElementById('consent');
        
        // Очистка ошибок
        document.querySelectorAll('.form__error').forEach(el => el.textContent = '');
        document.querySelectorAll('.form__input, .form__select').forEach(el => el.classList.remove('error'));
        
        // Валидация ФИО
        if (!fullName.value.trim()) {
            this.showError('fullName', 'Введите ФИО');
            isValid = false;
        } else if (fullName.value.trim().length < 5) {
            this.showError('fullName', 'ФИО должно содержать минимум 5 символов');
            isValid = false;
        }
        
        // Валидация телефона
        const phoneRegex = /^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/;
        if (!phone.value.trim()) {
            this.showError('phone', 'Введите телефон');
            isValid = false;
        } else if (!phoneRegex.test(phone.value)) {
            this.showError('phone', 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX');
            isValid = false;
        }
        
        // Валидация email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim()) {
            this.showError('email', 'Введите email');
            isValid = false;
        } else if (!emailRegex.test(email.value)) {
            this.showError('email', 'Введите корректный email');
            isValid = false;
        }
        
        // Валидация выбора курса
        if (!course.value) {
            this.showError('course', 'Выберите курс');
            isValid = false;
        }
        
        // Валидация согласия
        if (!consent.checked) {
            this.showError('consent', 'Необходимо дать согласие на обработку персональных данных');
            isValid = false;
        }
        
        return isValid;
    }
    
    showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId + 'Error');
        const inputElement = document.getElementById(fieldId);
        
        if (errorElement) errorElement.textContent = message;
        if (inputElement) inputElement.classList.add('error');
    }
    
    async submitForm() {
        const formData = new FormData(this.form);
        const submitBtn = this.form.querySelector('.form__submit');
        
        // Вывод данных для отладки
        console.log('Отправляемые данные:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправка...';
        
        try {
            const response = await fetch('php/submit_form.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('Статус ответа:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Ответ сервера:', result);
            
            if (result.success) {
                console.log('Заявка успешно отправлена, ID:', result.id);
                this.form.style.display = 'none';
                document.getElementById('successMessage').style.display = 'block';
            } else if (result.errors) {
                console.error('Ошибки сервера:', result.errors);
                alert('Ошибки при отправке:\n' + result.errors.join('\n'));
            } else {
                console.error('Неизвестный ответ сервера:', result);
                alert('Неизвестная ошибка сервера');
            }
        } catch (error) {
            console.error('Ошибка отправки:', error);
            
            // Показываем детальную информацию об ошибке
            let errorMessage = 'Ошибка при отправке формы:\n\n';
            errorMessage += 'Тип ошибки: ' + error.name + '\n';
            errorMessage += 'Сообщение: ' + error.message + '\n\n';
            errorMessage += 'Проверьте:\n';
            errorMessage += '1. Запущен ли веб-сервер (Apache)\n';
            errorMessage += '2. Доступен ли файл php/submit_form.php\n';
            errorMessage += '3. Права доступа к файлам\n';
            errorMessage += '4. Консоль браузера (F12) для деталей';
            
            alert(errorMessage);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Записаться';
        }
    }
}