# Тестовое задание на позицию php разработчика

### Порядок запуска проекта

```
git clone https://github.com/edgar010797/tasks-rest-api.git
cd tasks-rest-api
cp .env.dist .env
docker compose up --build -d
```

### Запустите эти команды в контейнере для наполнения базы

```
php artisan migrate --seed && php artisan storage:link
```

### Swagger

```
http://localhost:8081/
```

### Postman

```
Postman коллекция лежит в корне проекта
```

### Доступы для авторизации пользователя

```
admin@email.ru
12345
```
