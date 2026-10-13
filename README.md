# JetHim — сайт химчистки «JetHim» (WordPress)

Корпоративный сайт с калькулятором, формами заявок, каталогом услуг и блогом.
Тема **Astra + astra-child** (весь кастомный код в дочерней теме), формы — **Fluent Forms**,
Gutenberg-блоки — собственный плагин **jet-him-blocks**, уведомления — му-плагин **jc-telegram**.

- **Демо для заказчика:** <https://jethim.cxrm.ru> — закрыто от индексации (noindex), обновляется деплоем (см. ниже)
- **Статус проекта и план работ:** [STAGES.md](STAGES.md)
- **Промпты генерации изображений:** [IMAGE-PROMPTS.md](IMAGE-PROMPTS.md)

---

## Стек

| Компонент | Версия / выбор |
|---|---|
| WordPress | 7.1.2 (ядро в репозитории) |
| PHP | 8.4 (FPM) |
| БД | MariaDB 10.11 (локально dev-контейнер MariaDB 11) |
| Веб-сервер | Nginx |
| Тема | Astra 4.14 + `astra-child` (кастом) |
| Формы | Fluent Forms 6.2 (free) + свои дообработки |
| Плагин блоков | `jet-him-blocks` (calc, cta, before-after) |
| Уведомления | `mu-plugins/jc-telegram.php` → Telegram |

---

## Возможности

- **Главная:** hero, карточки услуг, мост в калькулятор, галерея «Примеры работ» (12 фото, лайтбокс), отзывы, гарантии/зоны выезда.
- **Калькулятор стоимости** (`/kalkulyator/`) — сумма переносится скрытым полем в форму заявки.
- **Хаб услуг** (`/uslugi/`) + каталог услуг: карточки, SEO-title из карты, секции контента.
- **Формы заявок (5 рабочих):** заявка из расчёта, заявка по услуге (модалка), запись на выезд (дата/время), обратный звонок (футер), «Фото для расчёта» — с загрузкой фото.
- **Слайдер «до/после»** — блок `before-after` (плагин) + JS `jc-ba` в дочерней теме.
- **Блог** (`/blog/`), статичные страницы (о компании, FAQ, контакты, цены…), русская 404.
- **SEO:** карта title/H1/description по страницам, sitemap (без служебных), JSON-LD **LocalBusiness + FAQPage**, `noindex` служебных страниц.

---

## Структура репозитория

```
├── wp-content/
│   ├── themes/astra-child/       ← весь кастомный фронтенд
│   │   ├── functions.php         ← SEO-карта, REST-загрузка фото (jc/v1/photo),
│   │   │                           модалки, схема JSON-LD, sitemap-фильтр, enqueue
│   │   ├── home.php / uslugi.php / service.php / single.php / archive.php
│   │   ├── template-parts/jc-post-list.php
│   │   └── assets/js/main.js     ← интерактив: калькулятор-мост, модалы, галерея,
│   │                               файл-контрол загрузки фото
│   │       assets/js/gallery.js  ← лайтбокс галереи примеров
│   │       assets/css/main.css
│   ├── plugins/jet-him-blocks/   ← блоки: calc, cta, before-after (+ рендер/стили)
│   ├── plugins/fluentform/       ← Fluent Forms (сток)
│   ├── mu-plugins/jc-telegram.php← заявки → Telegram (мульти-чат, фото, CLI wp jc-tg)
│   └── uploads/                  ← медиа сайта (в git — сайт самодостаточен)
├── wp-admin/ wp-includes/ …      ← ядро WP
├── .gitignore                    ← wp-config.php, внутренние заметки, логи, дампы
├── STAGES.md                     ← этапы, что сдано/в работе
└── IMAGE-PROMPTS.md              ← промпты для генерации картинок
```

Внутренние заметки (**PLAN.md**, **PRICE.md**, **QUESTIONS-TO-CLIENT.md**) и вспомогательные
скрипты (`.scripts/`: деплой, автопроверки, разовые админ-скрипты) **в репозиторий не входят** —
лежат только локально.

---

## Локальная разработка

Сайт: **<http://jc.localhost>** (монтирование проекта в контейнеры: `/mnt/d/dev/projects` → `/srv/projects`).

Docker-контейнеры:

| Контейнер | Назначение |
|---|---|
| `dev_nginx` | Nginx, порт 80 → `jc.localhost` |
| `dev_php` | PHP-FPM + wp-cli |
| `dev_mysql` | MariaDB (БД `jc`, пользователь `dev`) |
| `dev_mail` | Mailpit — SMTP :1025, UI <http://localhost:8025> |

Работать **из WSL**; в PowerShell кавычки и кириллица ломаются — нетривиальные команды
нужно писать в `.sh`-скрипты и запускать `wsl -d Ubuntu -- bash <script.sh>`.

```bash
# wp-cli
docker exec dev_php wp <команда> --allow-root --path=/srv/projects/jc

# после правок PHP темы/плагинов — сброс opcache
bash /mnt/d/dev/projects/.scripts/opcache_reset.sh

# регенерация статичного контента (страницы, FAQ, настройки) из источника
docker exec dev_php wp eval-file /srv/projects/.scripts/e6_pages.php \
  --allow-root --path=/srv/projects/jc
```

Локальная учётка: логин `admin` (пароль — в локальных заметках, в git его нет).

---

## Автопроверки (приёмка)

Скрипты лежат в `.scripts/` (вне репозитория), запуск из WSL:

| Скрипт | Что проверяет |
|---|---|
| `e4_check.sh` / `e4_check_brief.sh` | SEO-метатеги, заголовки, критерии Этапа 4 |
| `e5_check.sh` | Формы: статические проверки + puppeteer-сценарии отправки (A–F), метрики вёрстки форм |
| `e6_check.sh` | Контент по техзаданию/критериям |
| `e8_check.sh` / `e8_lint.sh` | Страницы из SEO-карты: title/H1/description = 1, линт PHP |
| `shot/` (puppeteer) | Скриншоты и метрики вёрстки (`submit.js`, `formshot.js`, `demo_shot.js`) |

---

## Формы и уведомления

**Канал уведомлений — только Telegram.** Email-уведомления Fluent Forms отключены
(источник — `e5_notification` в `.scripts/e5_setup.php`; включать обратно там же).

### Загрузка фото в формах

Fluent Forms (free) не умеют file-upload (это PRO) → свой путь:

1. `POST /wp-json/jc/v1/photo` — валидация (≤10 МБ, jpg/png/webp, rate-limit) → `wp_upload_dir()`;
2. JS-контрол в `assets/js/main.js` (dropzone в модалках) подставляет URL в скрытое поле `photo_url`;
3. при отправке му-плагин `jc-telegram` забирает картинки и шлёт их **вложением** (`sendPhoto`).

### Telegram: `mu-plugins/jc-telegram.php`

- хук `fluentform/submission_inserted` → сообщение со всеми полями формы (метки полей, значения)
  **всем получателям** + до 4 фото из полей/текста формы (прямые ссылки на `.jpg/.png/.webp`);
- свои файлы читаются с диска, внешние — по URL (fallback: Telegram сам скачает по ссылке);
- опции: `jc_tg_token`, `jc_tg_chat_ids` (JSON-массив chat_id);
- CLI:

```bash
wp jc-tg status                       # токен, список получателей
wp jc-tg discover                     # показать chat_id из /start (не сохраняя)
wp jc-tg discover --save              # добавить новых (существующие не затирает)
wp jc-tg add <chat_id>                # добавить вручную
wp jc-tg send "текст" [--file=URL]    # тестовое сообщение / с файлом
```

**Добавить второго получателя (заказчика):** он пишет боту `/start` → на сервере
`wp jc-tg discover --save`.

> `api.telegram.org` недоступен с локальной машины (блокировка сети) — проверка
> отправки выполняется только на сервере демонстрационного сайта.

---

## Демонстрационный сайт (деплой)

Демо: **<https://jethim.cxrm.ru>** — закрыто от индексации (`blog_public=0` + заголовок
`X-Robots-Tag: noindex` в nginx).

Деплой одной командой (скрипт `.scripts/deploy_demo.sh`, вне репозитория):

1. **rsync файлов** (без `.git`, `*.md`, `node_modules`, `wp-content/upgrade`) с `--delete`;
2. **адаптация `wp-config.php`** под сервер БД хостинга (сам файл в git не входит);
3. **дамп локальной БД → импорт**, при этом опции `jc_tg_chat_ids`/`jc_tg_chat_id`
   с целевого сервера сохраняются и восстанавливаются (chat_id не теряется);
4. **замена URL** `jc.localhost` → домен хостинга (включая сериализованные данные);
5. сброс кеша и smoke-проверка (`home=200`).

Перед переносом на сервер заказчика: сменить пароли (админ, БД, FTP/SSH),
включить HTTPS, настроить автобэкапы.

---

## Лицензия и права

Ядро WordPress — GPL, Astra — GPLv2+, Fluent Forms — LGPL, код дочерней темы и
плагинов `jet-him-blocks`/`jc-telegram` — собственность заказчика.
