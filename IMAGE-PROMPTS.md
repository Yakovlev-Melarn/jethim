# IMAGE-PROMPTS.md — Изображения JetHim: план размещения и промпты генерации

**Статус:** план + промпты готовы (ждём генерации и загрузки).
**Задача:** промпты для генерации всех изображений сайта + схема вставки, которая не ломает дизайн.

Текущее состояние (снято 07.10.2026):
- на сайта **нет ни одной фотограﬁи** — только логотип `jethim-logo.svg`, схема `zone.svg`
  и 5 брендовых обложек блога (синий градиент, рисует GD в `e6_posts.php`);
- слайдер «до/после» (главная и `/do-posle/`) показывает **плейсхолдеры** — градиенты
  `jhb-before.png` / `jhb-after.png` (960×640);
- **нет `og:image`, нет favicon/site-icon** — ссылки в Telegram/ВК выглядят пустыми;
- 14 страниц услуг — без единой картинки, хаб `/uslugi/` — сплошной текст.

---

## 0. Общий стиль (база для ВСЕХ промптов)

Подставлять в начало каждого промпта. Обеспечивает единый вид: светлый, чистый,
«премиум-клининг» под палитру сайта (синий `#1b88d8`, фон `#f9f9f9`, Montserrat/Inter).

**База (EN):**
```
photorealistic advertising photography for a premium upholstery cleaning service,
modern Moscow apartment interior, bright soft natural daylight from a large window,
light neutral palette (white, light gray, pale wood floor) with subtle sky-blue accents,
clean uncluttered composition, shallow depth of field, high detail,
no text, no logos, no watermarks
```

**Негатив (для SD/Flux/Кандинский):**
```
text, letters, watermark, logo, brand names, clutter, dark moody lighting,
oversaturated colors, distorted furniture, extra limbs, jpeg artifacts
```

**Параметры:** Midjourney — добавить `--ar <соотношение> --style raw`;
DALL-E/Flux — указать размер в запросе; Шедеврум/Кандинский — промпт перевести
на русский по смыслу (сути ниже хватает).

**Правило лиц:** людей не генерируем крупным планом — только руки в перчатках,
вид со спины, силуэт за работой. Реальные лица мастеров даст клиент (Этап 6).

---

## 1. Карта размещения (что куда вставляем)

| Приор | Изображение | Куда | Размер | Соотн. |
|---|---|---|---|---|
| P1 | og:image фон | `<head>`, шaring | 1200×630 | 1.91:1 |
| P1 | Favicon / site-icon | **не ИИ**: из `jethim-logo.svg` → PNG 512 | 512×512 | 1:1 |
| P1 | «До» + «После» (кухонный уголок) | слайдер главной + `/do-posle/` (заменяет плейсхолдеры) | 1536×1024, экспорт 960×640 | 3:2 |
| P1 | 3 карточки: Мебель / Шторы / Ковры | секция «Услуги и цены» (главная), позже хаб `/uslugi/` | 800×600 | 4:3 |
| P2 | 14 страниц услуг (по 1) | под вступительным абзацем каждой страницы | 1600×900 | 16:9 |
| P2 | 2-я пара «до/после» (ковёр) | `/do-posle/` (второй слайдер) | 1536×1024 | 3:2 |
| P3 | «Знакомая проблема» | главная: секция → 2 колонки (текст 60% / фото 40%) | 1000×750 | 4:3 |
| P3 | «Безопасная химия» | главная: та же 2-колоночная схема | 1000×750 | 4:3 |
| P3 | «Кто мы» | главная (2 колонки) + `/o-kompanii/` | 1000×750 | 4:3 |
| P3 | Раскладка оборудования | `/o-kompanii/` (второе фото) | 1000×750 | 4:3 |

Приоритеты: **P1** — без этого сайт выглядит незаконченным (соцсети, слайдер-плейсхолдеры);
**P2** — главный визуальный выигрыш (14 пустых страниц); **P3** — оживление секций главной.

**Сознательно НЕ вставляем** (чтобы не сломать дизайн): фото на первом экране
(hero остаётся текстовым — скорость + панель статов), фон под CTA-блок и футер,
картинки в FAQ, таблицы цен, калькулятор, секции «Почему это стоит своих денег»,
«Отзывы», «Прайс и пакеты» (текстовые, убеждающие — photo только разбавит),
шаги «Как мы работаем» (5 карточек с цифрами — фото их убьют).

---

## 2. Промпты

### A. Служебные

**A1. og:image (1200×630)** — фон под текст, который накладывает наш GD-скрипт
(как в обложках блога). Копирайт слева, правая треть — «воздух»:
```
wide horizontal banner photo, close-up of a freshly cleaned light-gray fabric sofa
with soft even folds filling the right two-thirds of the frame, generous empty
light-gray wall space on the left for text overlay, bright daylight,
191:100 aspect ratio, no text, no logos
```
Альтернатива (быстрее, без ИИ): GD-карточка в стиле обложек блога — синий градиент
`#0b6e99 → #08597b` + надпись «JetHim — химчистка с выездом по Москве» (паттерн уже
есть в `p6_cover_png()`).

**A2. Favicon / site-icon** — **не генерируем**: конвертировать `jethim-logo.svg`
в PNG 512×512 и выставить через «Настройки → Общие → Пиктограмма сайта».

### B. Слайдер «до / после» (замена плейсхолдеров)

Важно: кадры слайдера должны быть **пиксель-в-пиксель одним ракурсом**.
Порядок генерации: (1) сгенерировать «ПОСЛЕ» — чистый; (2) **img2img / редактированием
по этой же картинке** добавить пятна → «ДО». Одинаковый кадр, одинаковый свет.

**B1. После — кухонный уголок** (`jc-ba-after.jpg`, 1536×1024, `--ar 3:2`):
```
photorealistic advertising photography for a premium upholstery cleaning service,
a freshly cleaned beige kitchen corner sofa (corner bench with dining table) in a
bright modern kitchen, spotless even fabric color, crisp cushions, soft daylight
from a window, light neutral palette, clean composition, no text, no logos, no people
```

**B2. До — та же картинка, испачканная** (`jc-ba-before.jpg`, генерировать из B1):
```
same image and exact same composition, but the sofa fabric is visibly soiled:
greasy stains along the armrest and seat edges, yellowed discoloration, dull faded
upholstery, crumbs in the seams, realistic heavy household soiling, same lighting, same angle
```
*alt:* «Кухонный уголок до чистки» / «…после чистки».

**B3. Вторая пара — ковёр** (`jc-ba-rug-before/after.jpg`, 1536×1024):
после — `a large light-gray patterned area rug freshly deep-cleaned in a living
room, fluffy even pile, bright colors, ...`; до — из той же картинки:
`the same rug heavily soiled: matted gray pile, spilled coffee stain in the center,
dull faded pattern, ground-in dirt along the walkway, same angle and lighting`.

### C. Карточки «Услуги и цены» (главная, 4:3, 800×600)

Вставляются **внутрь карточки** `.jc-services .wp-block-column` над `h3`
(см. §3). Все три — руки/процесс без лиц.

**C1. Мебель** `jc-svc-mebel.jpg`:
```
{БАЗА} close-up of a professional upholstery extraction wand cleaning a light-gray
fabric sofa, visible clean stripe on the cushion, water spray and suction,
cleaner's gloved hands only, 4:3
```
*alt:* «Экстрактор чистит диван — видна полоса чистой ткани».

**C2. Шторы** `jc-svc-shtory.jpg`:
```
{БАЗА} sheer white and beige curtains hanging by a tall window being cleaned in
place with a professional steam wand, fabric glowing in soft daylight,
gloved hand holding the nozzle, 4:3
```
*alt:* «Чистка штор на весу у окна».

**C3. Ковры** `jc-svc-kovry.jpg`:
```
{БАЗА} close-up of a patterned area rug being deep cleaned with an extraction
machine, foam and water spray, half of the pile visibly brighter than the other,
light wooden floor around, 4:3
```
*alt:* «Глубокая чистка ковра экстрактором».

### D. Секции главной (4:3, 1000×750) — P3, в 2-колоночную схему (§3.3)

**D1. Знакомая проблема** `jc-problem.jpg`:
```
{БАЗА} close-up of a worn light-gray sofa cushion showing real household soiling:
dull fabric, faint stains, pet hair in the seams, soft daylight, honest realistic
detail without grime exaggeration, no people, 4:3
```
*alt:* «Изношенная ткань дивана: пятна и шерсть в швах».

**D2. Безопасная химия** `jc-safe-chem.jpg`:
```
{БАЗА} still life on a light wooden table: unbranded spray bottles with clear
liquid, purple nitrile gloves, a soft brush and a folded white towel, a child's
teddy bear softly blurred in the background near a bright window, calm airy mood,
no text on labels, 4:3
```
*alt:* «Безопасные средства ухода: перчатки, распылитель, мягкая щётка».

**D3. Кто мы** `jc-team.jpg`:
```
{БАЗА} a professional cleaner in a plain blue uniform using an upholstery
extractor on a sofa, seen from behind at a slight angle, face not visible,
equipment and hoses neatly arranged, modern apartment, 4:3
```
*alt:* «Мастер JetHim за работой с экстрактором».

**D4. Раскладка оборудования** `jc-gear.jpg` (для `/o-kompanii/`):
```
{БАЗА} overhead flat lay of professional upholstery cleaning equipment on a light
gray floor: compact extractor machine, coiled hoses, nozzles, brushes, unbranded
bottles, purple gloves, tidy symmetrical arrangement, 4:3
```
*alt:* «ОборудованиеJetHim: экстрактор, насадки, средства».

### E. Страницы услуг (16:9, 1600×900) — P2

Все — под вступительным абзацем, над «Что входит». Список = 14 слагов из `$services`
(`e6_pages.php`). Общая база + уточнение:

| Слаг | Файл | Уточнение к `{БАЗА}` (16:9) | alt (кратко) |
|---|---|---|---|
| `himchistka-myagkoy-mebeli` | `jc-p-mebel.jpg` | `macro of an extraction nozzle lifting dirt from a sofa armrest, visible clean stripe, gloved hands` | Чистка мягкой мебели экстрактором |
| `divany` | `jc-p-divany.jpg` | `a freshly cleaned three-seat fabric sofa as the centerpiece of a bright living room, even color, crisp cushions` | Чистый диван после химчистки |
| `kresla-stulya-pufy` | `jc-p-kresla.jpg` | `cleaning a fabric dining chair seat with a small hand tool, wooden legs, bright dining area` | Чистка стульев и кресел |
| `krovati-podgolovniki` | `jc-p-krovati.jpg` | `a fabric headboard being cleaned above crisp white bed linen, bedroom with soft daylight` | Чистка подголовников и кроватей |
| `mebel-iz-kozhi` | `jc-p-kozha.jpg` | `a gloved hand conditioning a rich brown leather armchair with a soft cloth, supple leather texture, warm sheen` | Уход за мебелью из кожи |
| `himchistka-shtor` | `jc-p-shtory.jpg` | `tall sheer curtains being steam cleaned in place by a window, wand and gloved hand, glowing daylight` | Химчистка штор на весу |
| `himchistka-kovrov` | `jc-p-kovry.jpg` | `an extraction wand deep cleaning a patterned wool rug, clean trail through the pile` | Химчистка ковров |
| `himchistka-kovrolina` | `jc-p-kovrolin.jpg` | `a professional carpet cleaning machine leaving clean stripes on wall-to-wall carpet in an office corridor` | Чистка ковролина |
| `himchistka-matrasov` | `jc-p-matras.jpg` | `close-up of a white quilted mattress surface being extraction-cleaned, foam and suction, spotless fabric` | Химчистка матрасов |
| `udalenie-zapahov` | `jc-p-zapah.jpg` | `a spray bottle and cloth treating a sofa armrest next to an open window with sheer curtains, fresh airy light` | Удаление запахов: обработка очага |
| `udalenie-katyshkov` | `jc-p-katyshki.jpg` | `macro of a fabric shaver removing pills from knitted upholstery, half the surface already smooth and restored` | Удаление катышков, восстановление ворса |
| `zashchitnaya-propitka-tkani` | `jc-p-propitka.jpg` | `macro of water droplets beading up on a protected light fabric surface, hydrophobic effect, crisp refraction` | Гидрофобная пропитка ткани |
| `sushka-mebeli` | `jc-p-sushka.jpg` | `a professional air mover dryer aimed at a freshly cleaned sofa, machine on the floor, bright room` | Профессиональная сушка мебели |
| `dlya-yurlic` | `jc-p-b2b.jpg` | `interior of a stylish empty cafe with clean upholstered banquettes and chairs, tables set, ready for guests` | Чистка мебели для кафе и офисов |

### F. Хаб `/uslugi/` и прочее

- **F1.** Хаб: переиспользовать C1–C3 как три карточки-категории (Мебель/Шторы/Ковры) —
  новых генераций не нужно.
- **F2.** `/do-posle/`: B1+B2 (уголок) + B3-пара (ковёр); позже — реальные пары от клиента.
- **F3.** Блог: **оставляем текущие GD-обложки** (дешёвые, консистентные, с текстом);
  опционально позже — фотофон из A1 + наложение заголовка скриптом.

---

## 3. Как вставляем, чтобы не сломать дизайн

### 3.1 Общие правила вёрстки
- **Радиус 10px** (`--jc-radius`) у всех фото — как у карточек и панелей.
- **Фиксированное соотношение сторон** (`aspect-ratio` в CSS + `width`/`height`
  атрибута у `<img>`) — нулевой CLS, страница не «прыгает».
- `object-fit: cover`, `loading="lazy"` (кроме самой первой картинки на странице),
  `decoding="async"`, осмысленный **русский alt**.
- Формат: JPEG quality 82 (WebP по желанию), длинная сторона не больше 1600,
  вес < 300 КБ. Имена файлов — `jc-*.jpg`, как в таблицах выше.
- Никакого текста поверх фото — подписи только отдельным блоком
  (стиль `.jc-ba__caption`: 14px, opacity .75).

### 3.2 Куда писать код
Контент страниц генерируется `D:\dev\projects\.scripts\e6_pages.php` →
**ручные правки в WP затрутся при регенерации**. Значит:
1. загрузить картинки в медиатеку (или скриптом, паттерн загрузки уже есть в
   `e6_pages.php` рядом с `e6_svg_url()`); подключать по имени, как `e6_pair_images()`;
2. вставить разметку в `e6_pages.php` (секции помечены комментариями);
3. регенерация: `wsl -d Ubuntu -- bash -c "docker exec dev_php wp eval-file /srv/projects/.scripts/e6_pages.php --allow-root --path=/srv/projects/jc"`;
4. при необходимости — сброс opcache (`opcache_reset.sh`);
5. автоприёмка: `e6_check.sh` (текстовые проверки не пострадают) + `shot/rd_verify.js`.

### 3.3 Схемы вставки

**a) Карточки услуг (C1–C3)** — картинка первой внутри колонки, над `h3`,
во всю внутреннюю ширину карточки, `border-radius: 10px`, `margin: 0 0 14px`.
Карточки `text-align:center` — картинка будет по центру, ничего не ломает.

**b) Страницы услуг (E)** — `<figure class="wp-block-image size-full jc-img">`
сразу после вступительного `e6_paragraph()` перед `e6_heading('Что входит')`.

**c) Секции главной D1–D3** — переупаковать секцию в колонки по образцу hero
(текст 60% / фото 40%, как `jc-hero__cols` 57/43):
```html
<!-- wp:columns {"className":"jc-sec__split"} -->
  <div class="wp-block-column"> …текст секции… </div>
  <div class="wp-block-column"><figure class="jc-img">…</figure></div>
<!-- /wp:columns -->
```
CSS в `main.css`:
```css
.jc-sec__split { align-items: center; gap: 32px; }
.jc-sec__split .jc-img { margin: 0; }
.jc-img img { width: 100%; height: auto; border-radius: var(--jc-radius); display: block; }
@media (max-width: 782px) { .jc-sec__split { flex-direction: column; } }
```
На мобильном колонки WP и так складываются в стек (текст → фото) — поведение
соответствует текущему стеку hero.

**d) og:image** — вывод в `<head>` (му-плагин или тема) + мета `og:type/title/description`;
сейчас `og:image` отсутствует полностью.

### 3.4 Контроль
- `e6_check.sh` — зелёный (текст главной/услуг не меняется);
- `shot/rd_verify.js` + `shot/rd_fix2_check.js` — зелёные (ось 120, меню, футер не трогаем);
- визуально: главная 1440/390, 2–3 страницы услуг, `/do-posle/`.

---

## 4. Чего НЕ генерируем

- **Логотип и favicon** — SVG уже есть (конвертация, не ИИ).
- **Карта зоны выезда** — `zone.svg` готов (Этап 4).
- **Обложки блога** — рисует GD (`e6_posts.php`), с текстом и консистентные.
- **Реальные фото «до/после» и отзывы** — ждём от клиента (Этап 6);
  сгенерированные B1–B3 — временные заглушки до их прихода.
- **Скриншоты отзывов Юды, QR-коды, фото офиса** (офиса нет — только выезд),
  изображения в FAQ, таблицах и калькуляторе.
