#!/bin/bash
set -e  # останавливаться при любой ошибке (по желанию)

RUN_TESTS=false
for arg in "$@"; do
    if [[ "$arg" == "--with-tests" ]]; then
        RUN_TESTS=true
    fi
done

echo "Настройка окружения..."
cp .env.dist .env

echo "Запуск контейнеров..."
docker compose up --build -d

echo "Ожидание готовности базы данных..."
sleep 15 # Даем время базе подняться

echo "Выполнение миграций и ссылок..."
docker compose exec php-fpm php artisan migrate --seed
docker compose exec php-fpm php artisan storage:link

if $RUN_TESTS; then
    echo "Запуск тестов..."
    docker compose exec php-fpm php artisan test
    echo "✅ Все тесты пройдены!"
else
    echo "ℹ️  Тесты не запущены (используйте --with-tests для запуска)"
fi

echo "✅ Готово! Проект запущен!"