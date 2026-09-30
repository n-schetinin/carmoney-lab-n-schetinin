# Агенты проекта (docs/setup/agents.md)

## planner — `.kilo/agents/planner.md`
Режим: `primary`. Строит план изменений до кода.

Может:
- читать код, `AGENTS.md`, `docs/setup/code_map.md`;
- писать только в `docs/plan/**`;
- в плане — обязательные разделы: Файлы, Шаги, Тесты, Риски;
- если данных не хватает — перечислить, чего не хватает, а не додумывать.

Не может:
- редактировать код (`edit: "*" → deny`);
- запускать команды (`bash: deny`).

## scout — `.kilo/agents/scout.md`
Режим: `subagent`. Разведчик по кодовой базе.

Может:
- искать по коду то, что просят;
- возвращать список мест: файл, строка, одна фраза — что там.

Не может:
- редактировать файлы (`edit: deny`);
- запускать команды (`bash: deny`);
- предлагать исправления.

## Общее у обеих ролей
- Только чтение кода и документации; никаких изменений кода и выполнения команд.
- `.env*` не читается ни одной ролью (глобальное правило проекта).

Места, где пробег читается или используется:
frontend/index.html:31 — поле ввода name="mileage" в форме.
frontend/app.js:8 — mileage включён в набор числовых полей для формирования payload.
backend/src/Domain/ApplicationValidator.php:43 — чтение из входного payload: $payload['mileage'].
backend/src/Domain/ApplicationValidator.php:44–45 — проверка диапазона по rules['vehicle']['max_mileage_km'], формирование ошибки.
backend/src/Domain/ApplicationValidator.php:78 — возврат нормализованного mileage в валидированном input.
backend/config/rules.php:23 — конфигурационный лимит max_mileage_km = 500000.
backend/src/Repository/ApplicationRepository.php:45 — чтение $input['mileage'] при сохранении заявки.
backend/src/Repository/ApplicationRepository.php:38–39 — запись пробега в vehicles.mileage_km.
backend/src/Repository/ApplicationRepository.php:68 — выборка v.mileage_km из БД.
db/schema.sql:22 — столбец БД mileage_km.
db/seed.sql:31–… — заполнение mileage_km синтетическими данными.
tests/Unit/ApplicationValidatorTest.php:34 — пробег во входных данных валидатора.
tests/Unit/AssessmentServiceTest.php:38 — пробег во входных данных сервиса оценки.
Важно: в текущем доменном конвейере пробег читается и валидируется только в ApplicationValidator; в DecisionEngine и расчёте LTV он не участвует.