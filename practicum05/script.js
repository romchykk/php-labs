// script.js — AJAX-логіка для варіанту №11 «Облік студентів»

'use strict';

/* ── Елементи DOM ──────────────────────────────────────────────── */
const searchInput      = document.getElementById('search');
const resultsContainer = document.getElementById('results-container');
const countBadge       = document.getElementById('count-badge');
const addForm          = document.getElementById('add-form');
const submitBtn        = document.getElementById('submit-btn');
const messageEl        = document.getElementById('message');

/* ── Стан сортування ───────────────────────────────────────────── */
let sortState = { key: 'name', dir: 'asc' };
let currentStudents = [];

/* ── Допоміжні функції ─────────────────────────────────────────── */

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function gradeClass(grade) {
    const g = parseFloat(grade);
    if (g >= 80) return 'grade-high';
    if (g >= 60) return 'grade-mid';
    return 'grade-low';
}

function showMessage(text, type = 'success') {
    messageEl.textContent = text;
    messageEl.className = type;
}

function showError(text) {
    resultsContainer.innerHTML =
        `<div class="state-msg" style="color:#c0392b">⚠ ${escHtml(text)}</div>`;
    countBadge.textContent = '—';
}

function pluralStudents(n) {
    if (n % 10 === 1 && n % 100 !== 11) return 'студент';
    if ([2, 3, 4].includes(n % 10) && ![12, 13, 14].includes(n % 100)) return 'студенти';
    return 'студентів';
}

/* ── Сортування ────────────────────────────────────────────────── */

function sortStudents(students) {
    const { key, dir } = sortState;
    return [...students].sort((a, b) => {
        let va = a[key], vb = b[key];
        if (key === 'average_grade' || key === 'id') {
            return dir === 'asc' ? parseFloat(va) - parseFloat(vb) : parseFloat(vb) - parseFloat(va);
        }
        va = String(va).toLowerCase();
        vb = String(vb).toLowerCase();
        if (va < vb) return dir === 'asc' ? -1 : 1;
        if (va > vb) return dir === 'asc' ?  1 : -1;
        return 0;
    });
}

function sortIcon(col) {
    if (sortState.key !== col) return '<span class="sort-icon">↕</span>';
    return sortState.dir === 'asc'
        ? '<span class="sort-icon active">↑</span>'
        : '<span class="sort-icon active">↓</span>';
}

/* ── Рендер рядка ──────────────────────────────────────────────── */

function buildRow(student, isNew = false) {
    const cls   = gradeClass(student.average_grade);
    const width = Math.min(100, Math.max(0, parseFloat(student.average_grade)));
    const grade = parseFloat(student.average_grade).toFixed(2);

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
          <span class="grade-val ${cls}" data-grade="${escHtml(grade)}">${escHtml(grade)}</span>
          <button class="edit-btn" title="Редагувати бал" data-id="${escHtml(student.id)}" data-grade="${escHtml(grade)}">✏️</button>
        </div>
      </td>
    </tr>`;
}

/* ── Рендер таблиці ────────────────────────────────────────────── */

function renderTable(students) {
    currentStudents = students;

    if (students.length === 0) {
        resultsContainer.innerHTML = '<div class="state-msg">Нічого не знайдено</div>';
        countBadge.textContent = '0 студентів';
        return;
    }

    const sorted = sortStudents(students);
    const rows   = sorted.map(s => buildRow(s)).join('');

    resultsContainer.innerHTML = `
    <div class="table-wrap">
      <table id="results">
        <thead>
          <tr>
            <th class="sortable" data-col="id"># ${sortIcon('id')}</th>
            <th class="sortable" data-col="name">ПІБ студента ${sortIcon('name')}</th>
            <th class="sortable" data-col="group_name">Група ${sortIcon('group_name')}</th>
            <th class="sortable" data-col="average_grade">Середній бал ${sortIcon('average_grade')}</th>
          </tr>
        </thead>
        <tbody id="tbody">
          ${rows}
        </tbody>
      </table>
    </div>`;

    // Сортування по заголовку
    resultsContainer.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            sortState.dir = (sortState.key === col && sortState.dir === 'asc') ? 'desc' : 'asc';
            sortState.key = col;
            renderTable(currentStudents);
        });
    });

    // Кнопки редагування балу
    resultsContainer.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => startEditGrade(btn));
    });

    countBadge.textContent = `${students.length} ${pluralStudents(students.length)}`;
}

/* ── Inline-редагування середнього балу (вимога варіанту 11) ───── */

function startEditGrade(btn) {
    const id        = btn.dataset.id;
    const gradeVal  = btn.dataset.grade;
    const gradeSpan = btn.closest('td').querySelector('.grade-val');
    const gradeCell = btn.closest('.grade-cell');

    // Якщо вже є поле — не дублюємо
    if (gradeCell.querySelector('.grade-input')) return;

    // Ховаємо прогрес-бар і значення, показуємо input
    gradeCell.querySelector('.grade-bar-bg').style.display = 'none';
    gradeSpan.style.display = 'none';
    btn.style.display = 'none';

    const input = document.createElement('input');
    input.type      = 'number';
    input.min       = '0';
    input.max       = '100';
    input.step      = '0.01';
    input.value     = gradeVal;
    input.className = 'grade-input';

    const saveBtn   = document.createElement('button');
    saveBtn.textContent = '✓';
    saveBtn.className   = 'save-grade-btn';
    saveBtn.title       = 'Зберегти';

    const cancelBtn = document.createElement('button');
    cancelBtn.textContent = '✕';
    cancelBtn.className   = 'cancel-grade-btn';
    cancelBtn.title       = 'Скасувати';

    gradeCell.appendChild(input);
    gradeCell.appendChild(saveBtn);
    gradeCell.appendChild(cancelBtn);
    input.focus();
    input.select();

    // Скасування
    function cancelEdit() {
        gradeCell.querySelector('.grade-bar-bg').style.display = '';
        gradeSpan.style.display = '';
        btn.style.display = '';
        input.remove();
        saveBtn.remove();
        cancelBtn.remove();
    }

    cancelBtn.addEventListener('click', cancelEdit);

    // Enter — зберегти, Escape — скасувати
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter')  saveGrade();
        if (e.key === 'Escape') cancelEdit();
    });

    // Збереження
    async function saveGrade() {
        const newGrade = input.value.trim();

        if (!newGrade || isNaN(newGrade) || newGrade < 0 || newGrade > 100) {
            input.style.borderColor = '#ef4444';
            input.title = 'Введіть число від 0 до 100';
            return;
        }

        saveBtn.disabled   = true;
        saveBtn.textContent = '…';

        try {
            const response = await fetch('api_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id, average_grade: newGrade }),
            });

            const data = await response.json();

            if (!response.ok) {
                const errText = data.errors ? data.errors.join('; ') : (data.error || 'Помилка');
                input.style.borderColor = '#ef4444';
                input.title = errText;
                saveBtn.disabled   = false;
                saveBtn.textContent = '✓';
                return;
            }

            // Оновлюємо рядок у локальному масиві
            const idx = currentStudents.findIndex(s => String(s.id) === String(id));
            if (idx !== -1) currentStudents[idx].average_grade = data.average_grade;

            // Перемальовуємо таблицю
            renderTable(currentStudents);

        } catch (networkError) {
            input.style.borderColor = '#ef4444';
            input.title = 'Мережева помилка: ' + networkError.message;
            saveBtn.disabled   = false;
            saveBtn.textContent = '✓';
        }
    }

    saveBtn.addEventListener('click', saveGrade);
}

/* ── Завантажити список ────────────────────────────────────────── */

let loadSeq = 0;

async function loadStudents(query = '') {
    const url = 'api_list.php' + (query ? '?q=' + encodeURIComponent(query) : '');
    const seq = ++loadSeq;

    try {
        const response = await fetch(url);
        // Відповідь на застарілий запит ігноруємо
        if (seq !== loadSeq) return;

        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            showError(err.error || `Помилка сервера: ${response.status}`);
            return;
        }

        const students = await response.json();
        if (seq !== loadSeq) return;
        renderTable(students);

    } catch (networkError) {
        if (seq !== loadSeq) return;
        showError('Мережева помилка: ' + networkError.message);
    }
}

/* ── Живий пошук ───────────────────────────────────────────────── */

let debounceTimer;

searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => loadStudents(searchInput.value.trim()), 280);
});

/* ── Додавання студента ────────────────────────────────────────── */

addForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    showMessage('');

    const name          = document.getElementById('f-name').value.trim();
    const group_name    = document.getElementById('f-group').value.trim();
    const average_grade = document.getElementById('f-grade').value.trim();

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

        const data = await response.json();

        if (!response.ok) {
            const errText = data.errors ? data.errors.join('; ') : (data.error || `Помилка ${response.status}`);
            showMessage(errText, 'error');
            return;
        }

        showMessage(`Студента "${data.name}" успішно додано!`, 'success');
        addForm.reset();
        // Перезавантажуємо з урахуванням активного фільтра за групою
        await loadStudents(searchInput.value.trim());

    } catch (networkError) {
        showMessage('Мережева помилка: ' + networkError.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Додати';
    }
});

/* ── Старт ──────────────────────────────────────────────────────── */
loadStudents();