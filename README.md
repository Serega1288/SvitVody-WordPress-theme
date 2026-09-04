# Project Starter

## Швидкий старт

```bash
npm install
npm run init
npm run wp:up
npm run dev
```

Початкові дані для нового проєкту задаються в `init.defaults.json`.  
Команда `npm run init` бере значення з цього файла і оновлює `.env`, `package.json` та `project-theme/style.css`.

Налаштування автoвстановлення плагінів у `.env`, наприклад:

```env
WP_AUTO_PLUGINS=woocommerce,advanced-custom-fields
```

## Структура

```text
docker/
  php/
project-theme/
plugins-archives/
.gitignore
.env.example
docker-compose.yml
package.json
README.md
```

## Запуск Docker

```bash
npm run wp:up
```

Плагіни з `WP_AUTO_PLUGINS` встановлюються автоматично після встановлення WordPress у Docker, але не активуються.  
Кастомні плагіни з `plugins-archives/` також встановлюються автоматично з папок або zip-архівів, але не активуються.

WordPress: `http://localhost:8080`  
Adminer: `http://localhost:8081`

## Frontend

Встановити залежності:

```bash
npm install
```

Разова збірка CSS:

```bash
npm run build
```

Запуск watch-режиму з автооновленням:

```bash
npm run dev
```

Для автооновлення відкривай URL BrowserSync, який з’явиться в терміналі після `npm run dev`.  
`http://localhost:8080` — це сам WordPress, а live reload працює через BrowserSync URL.

## Команди

```bash
npm run wp:up
npm run wp:down
npm run wp:purge
npm run wp:logs
npm run db:export
npm run db:import
npm run build
npm run dev
```

`npm run wp:up`  
Піднімає Docker-стек проєкту: MySQL, WordPress, Adminer і `wpcli`. Також перебілджує образ WordPress.

`npm run wp:down`  
Зупиняє Docker-контейнери проєкту.

`npm run wp:purge`  
Повністю очищає Docker-оточення цього проєкту: контейнери, томи, orphan-контейнери та локально зібрані image. Перед запуском просить підтвердження `так`.

`npm run wp:logs`  
Показує логи Docker-контейнерів у реальному часі.

`npm run db:export`  
Експортує базу даних у [database/wordpress.sql](C:\work\project\rasti\database\wordpress.sql).

`npm run db:import`  
Імпортує [database/wordpress.sql](C:\work\project\rasti\database\wordpress.sql) назад у Docker MySQL.

`npm run build`  
Робить разову збірку Sass у CSS для теми `project-theme`.

`npm run dev`  
Запускає watch-режим для Sass і BrowserSync з автооновленням сторінки.

BrowserSync також відслідковує зміни в `plugins-local/**/*.php`, `plugins-local/**/*.js` і `plugins-local/**/*.css`.

## Локальні плагіни

`plugins-local/` використовується для live-підключення локальних плагінів у `wp-content/plugins`.

Плагіни з `plugins-local/` не потрібно видаляти з адмінки WordPress.  
Щоб прибрати локальний плагін, видали його папку з `plugins-local/`, а `plugins-sync` прибере symlink автоматично.

## База даних

Експорт бази:

```bash
npm run db:export
```

Команда зберігає SQL-дамп у кодуванні `utf8mb4`.

Імпорт бази:

```bash
npm run db:import
```

Шлях до дампа:

```text
database/wordpress.sql
```

## Шлях для деплою

```text
project-theme
```
