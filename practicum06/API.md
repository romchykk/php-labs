# API — Система обліку студентів (практична №6, варіант 11)

Єдина точка входу: `api.php`. Усі відповіді — JSON.
Успіх: `{"success": true, "data": ...}`. Помилка: `{"success": false, "error": "..."}`.

| Метод | URL | Опис | Коди |
|-------|-----|------|------|
| GET  | `api.php?resource=students` | Список студентів | 200 |
| GET  | `api.php?resource=students&id=N` | Один студент | 200, 400 (невалідний id), 404 |
| POST | `api.php?resource=students` | Створити студента | 201, 400 |
| POST | `api.php?resource=students&action=top` | Студент з найвищим балом у групі | 200, 400, 404 |

Тіло POST: `application/x-www-form-urlencoded` або JSON.

- Створення: `name` (2–120 символів), `group_name` (≤ 60), `average_grade` (число 0–100, округлюється до 2 знаків).
- `top`: `group_name`.

Інші методи → 405, невідомий ресурс → 404, внутрішня помилка → 500.
