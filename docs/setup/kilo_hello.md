# Kilo Hello

1) Сервис по README и корневым файлам: учебный сервис предварительной оценки заявки н�� заём под ПТС (carmoney-lab, практикум М3), синтетические данные.
2) Команды запуска и проверки в Makefile: `make up`, `make down`, `make test`, `make lint`, `make seed`, `make logs`, `make ps`, `make install`, `make help`; в docker-compose.yml — сервисы `backend` (PHP на 0.0.0.0:8080, порт через APP_PORT, по умолчанию 8080) и `db` (MySQL 8.0, порт через DB_PORT, по умолчанию 3307, БД `carmoney_lab`, пользователь `lab`).
3) Решение `approve` / `review` / `reject` считается в папке `backend/src/Domain` (файлы `DecisionEngine.php`, `LtvCalculator.php`, `AssessmentService.php`).

модель: training-2026-09-minimax-m3