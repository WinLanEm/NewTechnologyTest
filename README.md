Что реализовано?
Разработана консольная команда Laravel, которая автоматически собирает и сохраняет данные топовых позиций категорий из внешнего API (эндпоинт: http://localhost/api/appTopCategory). Эта команда интегрирована с планировщиком задач, работающим через Supervisor внутри Docker-контейнера.

1 Принцип работы автоматизации:

Команда запускается каждые 30 минут автоматически (через supervisor + Laravel schedule:work).

Для первой инициализации предусмотрен ручной запуск команды с параметром --first-run — данные за последние 30 дней подгрузятся сразу.


2 API endpoint:

Для доступа к свежей статистике реализован GET-эндпоинт /api/appTopCategory.

Вся маршрутизация защищена двумя middleware:

logger — подробное логирование каждого API-запроса (метод, параметры, IP и время обращения).

throttle — лимит частоты: не более 5 запросов в минуту на клиента для защиты от спама и перегрузок.

3 Как использовать (пошагово):

После поднятия контейнеров для инициализации данных за 30 дней запусти:

php artisan app:parse-top-category-positions --first-run
Далее данные будут автоматически обновляться через каждые полчаса (благодаря Supervisor и Laravel schedule).

Метод API доступен по адресу http://localhost/api/appTopCategory; логируется и ограничивается по частоте обращений.

4 Отладка и мониторинг:

Вся информация о сборе и сохранении данных пишется в логи приложения (storage/logs/laravel.log и storage/logs/schedule.log).

В случае ошибок запроса к внешнему API, они сохраняются с деталями (исключения, параметры, stacktrace).

Спасибо за предоставленную задачу, жду обратной связи :)

5 Развертывание:

docker-compose build
composer install

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

docker-compose up -d
docker exec -it project_app bash

cp .env.example .env
php artisan migrate
php artisan app:parse-top-category-positions --first-run
