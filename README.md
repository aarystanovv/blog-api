# Blog Management API

Этот проект представляет собой RESTful API для управления блогами с использованием ролей и разрешений. API позволяет пользователям регистрироваться, создавать, редактировать и удалять посты, управлять категориями и тегами, а также поддерживает контроль доступа на основе ролей.

## Стек технологий

- **Framework**: Laravel (версия 8+)
- **Authentication**: Laravel Sanctum
- **Role-Based Access Control**: Spatie Laravel Permission
- **Database**: MySQL/PostgreSQL (через Docker)
- **Web Server**: Nginx (через Docker)
- **API Documentation**: Swagger/OpenAPI

## Основные возможности

1. **Система аутентификации**
    - Регистрация и вход пользователей с использованием Laravel Sanctum.
    - Поддержка сброса пароля.

2. **Управление блогом**
    - CRUD-операции для постов.
    - Поддержка категорий и тегов для постов (отношение многие ко многим).
    - Возможность добавления изображения для постов.
    - Поддержка статусов постов (черновик/опубликован).

3. **Роли и разрешения**
    - Поддержка ролей: Admin, Editor, Author, Reader.
    - Назначение разрешений на основе ролей:
        - Управление постами, категориями, пользователями.
        - Публикация, редактирование и удаление постов.

4. **API документация**
    - Описание всех конечных точек API с примерами запросов и ответов через Swagger.

## Установка и настройка

### Предварительные требования

- Docker и Docker Compose установлены на вашей машине.
- Composer для управления зависимостями PHP.

### Шаги установки

#### Настройка переменных окружения

Скопируйте файл `.env.example` в `.env`:

```bash
cp .env.example .env
```

Отредактируйте .env файл для подключения к базе данных, а также настройте почтовую отправку:

```bash
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=user
DB_PASSWORD=password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ваш_email@gmail.com
MAIL_PASSWORD="ваш_пароль_приложения"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="Blog Management API"
```

#### Запуск Docker контейнеров

Запустите Docker Compose, чтобы развернуть контейнеры для приложения, базы данных и веб-сервера:

```bash
docker-compose up -d --build
```

#### Установка зависимостей

Установите зависимости Laravel внутри контейнера:

```bash
docker-compose exec app composer install
```

#### Запуск миграций и сидов

Выполните миграции и сиды, чтобы создать таблицы и начальные данные в базе данных:

```bash 
docker-compose exec app php artisan migrate --seed
```

#### Генерация ключа приложения

Сгенерируйте ключ для приложения Laravel:

```bash 
docker-compose exec app php artisan key:generate
```

## Использование
### Аутентификация
- Регистрация: Отправьте POST запрос на /api/register с полями name, email, password, password_confirmation, и (опционально) role.
- Вход: Отправьте POST запрос на /api/login с полями email и password.
- Выход: Отправьте POST запрос на /api/logout с заголовком Authorization: Bearer {access_token}.

### Управление категориями
- Создание категории: POST /api/categories
```bash
{
  "name": "Technology"
}
```

```bash
response:
{
  "id": 1,
  "name": "Technology",
  "slug": "technology",
  "created_at": "2024-11-13T09:00:00",
  "updated_at": "2024-11-13T09:00:00"
}
```

### Управление тегами
- Создание тега: POST /api/tags
```bash
{
  "name": "Laravel"
}
```

```bash
response:
{
  "id": 1,
  "name": "Laravel",
  "slug": "laravel",
  "created_at": "2024-11-13T09:00:00",
  "updated_at": "2024-11-13T09:00:00"
}
```

### Управление блогом
- Создание поста: POST /api/posts
- Получение списка постов: GET /api/posts
- Обновление поста: PUT /api/posts/{id}
- Удаление поста: DELETE /api/posts/{id}

### Роли и разрешения
Права доступа для различных ролей:

- Admin: Полные права, включая управление пользователями и категориями.
- Editor: Управление постами и категориями.
- Author: Создание и редактирование своих постов.
- Reader: Доступ только для чтения.

## Документация API
API задокументирован с использованием Swagger. Документация доступна по адресу:

```bash
http://localhost/api/documentation
```

## Тестирование
Для запуска тестов используйте следующую команду:

```bash
docker-compose exec app php artisan test
```

Тесты включают проверку аутентификации, CRUD-операций для постов и проверку ролей и разрешений.


## Структура проекта

- app/Http/Controllers — Контроллеры API.
- app/Http/Requests — Классы запросов для валидации данных.
- app/Http/Resources — Ресурсы API для форматирования данных.
- app/Models — Модели данных.
- app/Traits — Трейты, такие как ImageUploadTrait для обработки изображений.
- database/migrations — Миграции для базы данных.
- database/seeders — Сиды для начальных данных.

## Заметки

- Роль пользователя назначается при регистрации. Если роль не указана, пользователю назначается роль Reader по умолчанию.
- Пользовательские роли и разрешения настраиваются через Spatie Laravel Permission.
