# TimeTrim – PHP Task Manager

Уеб приложение за управление на задачи с приоритизиране по времетраене и крайни срокове. Проектът е контейнеризиран с Docker и се стартира изцяло чрез `docker compose`.

---

## Структура на проекта

```
timetrim/
├── Dockerfile          # Конфигурация за уеб контейнера (PHP + Apache)
├── compose.yml         # Docker Compose – дефинира и свързва услугите
├── README.md
└── app/
    ├── index.php       # Начална страница
    ├── login.php       # Вход в акаунт
    ├── logout.php      # Изход
    ├── signup.php      # Регистрация
    ├── dashboard.php   # Управление на задачи
    ├── profile.php     # Профил и статистика
    ├── functions.php   # Споделени функции (база данни, сесии, логика)
    ├── styles.css      # Стилове
    └── timetask_schema.sql  # SQL схема на базата данни
```

---

## Компоненти

### web
- Базиран на официалния `php:8.3-apache` образ
- Сервира PHP приложението на порт **8080**
- Инсталирани разширения: `pdo`, `pdo_mysql`, `mysqli`
- Образът е публикуван в Docker Hub: `petya103/timetrim-web:latest`

### db
- Използва официалния `mysql:8.0` образ
- Базата данни `timetask` се създава автоматично при първо стартиране
- SQL схемата се зарежда автоматично от `timetask_schema.sql`
- Данните се пазят в Docker volume `mysql_data`, така че не се губят при рестарт

---

## Как се стартира

### Изисквания
- Инсталиран [Docker Desktop](https://www.docker.com/products/docker-desktop/)

### Стъпки

```bash
# 1. Клонирай хранилището
git clone https://github.com/petya103/timetrim.git
cd timetrim

# 2. Стартирай контейнерите
docker compose up -d

# 3. Отвори в браузър
http://localhost:8080
```

### Спиране

```bash
docker compose down
```

### Изграждане на образа (ако искаш да билдваш локално)

```bash
docker build -t petya103/timetrim-web:latest .
```

---

## Комуникация между услугите

Двата контейнера (`web` и `db`) са в обща Docker мрежа, създадена автоматично от Compose. Уеб контейнерът се свързва с базата данни по вътрешното hostname `db` — това е името на услугата в `compose.yml`. Credential-ите се подават като environment variables и се четат в `functions.php` чрез `getenv()`.

```
[Браузър] → localhost:8080 → [web контейнер: PHP/Apache] → db:3306 → [db контейнер: MySQL]
```

---

## Docker Hub

- **уеб образ:** https://hub.docker.com/r/petya103/timetrim-web
