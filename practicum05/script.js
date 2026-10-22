// script.js — AJAX-логіка для варіанту №11 «Облік студентів»
// Кроки 3, 4, 6, 7, 8 з практичної роботи

'use strict';

/* ── Елементи DOM ──────────────────────────────────────────────── */
const searchInput     = document.getElementById('search');
const resultsContainer = document.getElementById('results-container');
const countBadge      = document.getElementById('count-badge');
const addForm         = document.getElementById('add-form');
const submitBtn       = document.getElementById('submit-btn');
const messageEl       = document.getElementById('message');

/* ── Допоміжні функції ─────────────────────────────────────────── */

/**
 * Екранує HTML-символи для безпечної вставки в innerHTML (захист від XSS).
 */
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * Повертає CSS-клас кольору залежно від балу.
 */
function gradeClass(grade) {
    const g = parseFloat(grade);
    if (g >= 80) return 'grade-high';
    if (g >= 60) return 'grade-mid';
    return 'grade-low';
}

/**
 * Показує повідомлення під формою.
 */
function showMessage(text, type = 'success') {
    messageEl.textContent = text;
    messageEl.className = type;
}

/**
 * Будує HTML-рядок таблиці для одного студента.
 */
function buildRow(student, isNew = false) {
    const cls   = gradeClass(student.average_grade);
    const width = Math.min(100, Math.max(0, parseFloat(student.average_grade)));

    return `
    <tr data-id="${escHtml(student.id)}"${isNew ? ' class="new-row"' : ''}>
      <td>${escHtml(student.id)}</td>
      <td>${escHtml(student.name)}</td>
      <td>${escHtml(student.group_name)}</td>
      <td>
        <div class="grade-cell">
          <div class="grade-bar-bg">
            <div class="grade-bar ${cls}" style="width:${width}%"></div>
          </div>
          <span class="grade-val ${cls}">${escHtml(parseFloat(student.average_grade).toFixed(2))}</span>
        </div>
      </td>
    </tr>`;
}

/**
 * Будує та вставляє таблицю зі студентами в контейнер.
 */
function renderTable(students) {
    if (students.length === 0) {
        resultsContainer.innerHTML =
            '<div class="state-msg">Нічого не знайдено</div>';
        countBadge.textContent = '0 студентів';
        return;
    }

    const rows = students.map(s => buildRow(s)).join('');

    resultsContainer.innerHTML = `
    <div class="table-wrap">
      <table id="results">
        <thead>
          <tr>
            <th>#</th>
            <th>ПІБ студента</th>
            <th>Група</th>
            <th>Середній бал</th>
          </tr>
        </thead>
        <tbody id="tbody">
          ${rows}
        </tbody>
      </table>
    </div>`;

    const word = pluralStudents(students.length);
    countBadge.textContent = `${students.length} ${word}`;
}

/** Відмінювання слова "студент" */
function pluralStudents(n) {
    if (n % 10 === 1 && n % 100 !== 11) return 'студент';
    if ([2, 3, 4].includes(n % 10) && ![12, 13, 14].includes(n % 100)) return 'студенти';
    return 'студентів';
}

/* ── Крок 3: Завантажити список при відкритті сторінки ─────────── */
async function loadStudents(query = '') {
    const url = 'api_list.php' + (query ? '?q=' + encodeURIComponent(query) : '');

    try {
        const response = await fetch(url);

        if (!response.ok) {
            // Сервер повернув помилку (4xx / 5xx)
            const err = await response.json().catch(() => ({}));
            showError(err.error || `Помилка сервера: ${response.status}`);
            return;
        }

        const students = await response.json();
        renderTable(students);

    } catch (networkError) {
        // Мережева помилка (сервер недоступний, CORS тощо)
        showError('Мережева помилка: ' + networkError.message);
    }
}

/** Показує помилку прямо в контейнері результатів */
function showError(text) {
    resultsContainer.innerHTML =
        `<div class="state-msg" style="color:#c0392b">⚠ ${escHtml(text)}</div>`;
    countBadge.textContent = '—';
}

/* ── Крок 4: Живий пошук за групою ────────────────────────────── */
let debounceTimer;

searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    // Невелика затримка, щоб не спамити запитами під час набору
    debounceTimer = setTimeout(() => {
        loadStudents(searchInput.value.trim());
    }, 280);
});

/* ── Крок 6: Додавання студента через AJAX ─────────────────────── */
addForm.addEventListener('submit', async (event) => {
    event.preventDefault();           // НЕ перезавантажувати сторінку!

    showMessage('');                  // скидаємо попереднє повідомлення

    const name          = document.getElementById('f-name').value.trim();
    const group_name    = document.getElementById('f-group').value.trim();
    const average_grade = document.getElementById('f-grade').value.trim();

    // Базова клієнтська перевірка
    if (!name || !group_name || average_grade === '') {
        showMessage('Заповніть усі поля форми', 'error');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Зберігаємо…';

    try {
        const response = await fetch('api_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ name, group_name, average_grade }),
        });

        // Крок 7: обробка відповіді сервера
        const data = await response.json();

        if (!response.ok) {
            // 422 — помилки валідації, 500 — серверна помилка
            const errText = data.errors
                ? data.errors.join('; ')
                : (data.error || `Помилка ${response.status}`);
            showMessage(errText, 'error');
            return;
        }

        // Успіх — додаємо рядок у таблицю без перезавантаження
        showMessage(`Студента "${data.name}" успішно додано!`, 'success');
        addForm.reset();
        appendRow(data);

    } catch (networkError) {
        showMessage('Мережева помилка: ' + networkError.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Додати';
    }
});

/**
 * Додає новий рядок у <tbody> таблиці (або перезавантажує список,
 * якщо таблиця ще не відрендерена або активний фільтр пошуку).
 */
function appendRow(student) {
    const tbody = document.getElementById('tbody');

    // Якщо зараз активний пошуковий фільтр — просто перезавантажуємо список
    if (searchInput.value.trim() !== '' || !tbody) {
        loadStudents(searchInput.value.trim());
        return;
    }

    // Вставляємо рядок у кінець таблиці з підсвіткою
    tbody.insertAdjacentHTML('beforeend', buildRow(student, true));

    // Оновлюємо лічильник
    const current = parseInt(countBadge.textContent) || 0;
    const newCount = current + 1;
    countBadge.textContent = `${newCount} ${pluralStudents(newCount)}`;

    // Прокручуємо до нового рядка
    const newRow = tbody.lastElementChild;
    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ── Старт: завантажуємо список одразу при відкритті сторінки ─── */
loadStudents();
