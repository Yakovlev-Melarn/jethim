# IMAGE-PROMPTS.md — промпты для генерации изображений JetHim

## Как пользоваться (4 шага)

1. Берёшь промпт из разделов ниже — он **полный**, копируется целиком, ничего подставлять не надо.
2. В генераторе задаёшь **соотношение сторон** и **размер** из карточки.
3. Сохраняешь файлом из поля **«Файл»** в папку **`D:\dev\projects\.scripts\img\`**
   (для контейнера это `/srv/projects/.scripts/img/`).
4. Говоришь мне — я сам загружаю картинки в медиатеку и вставляю их в нужные места сайта.

Если генератор не даёт нужное соотношение — бери ближайшее и обрезай до размера из карточки.

---

## Общие правила (читать один раз)

- **Единый стиль:** светлый, чистый, «премиум-клининг» — дневной свет из окна, светлая нейтральная палитра (белый, светло-серый, светлое дерево) с бледно-голубыми акцентами. Каждый промпт ниже уже содержит эту базу.
- **Негатив** (вставить в negative prompt, если генератор его спрашивает: SD / Flux / Кандинский):
  `text, letters, watermark, logo, brand names, clutter, dark moody lighting, oversaturated colors, distorted furniture, extra limbs, jpeg artifacts`
- **Лица не генерируем** крупным планом: только руки в перчатках, вид со спины, силуэт за работой. Реальные фото мастеров даст клиент.
- **Текст и логотипы внутри картинки запрещены** — надписи накладывает сайт отдельными блоками.
- **Формат:** JPEG, качество 82, вес < 300 КБ, длинная сторона не больше 1600 px.
- **Midjourney:** добавить в конец `--ar <соотношение> --style raw`. **DALL-E / Flux / Ideogram** — задать размер прямо в запросе. **Шедеврум / Кандинский** — суть промпта перевести на русский.

---

## Чего НЕ генерируем

- **«До и после»** (слайдер на главной и страница `/do-posle/`) — ставим **реальные фото из портфолио клиента**.
- **Логотип и favicon** — SVG уже есть (`jethim-logo.svg`), нужна только конвертация в PNG 512×512.
- **Карта зоны выезда** — `zone.svg` уже готов.
- **FAQ, таблицы цен, калькулятор, шаги «Как мы работаем», «Отзывы», «Прайс и пакеты»** — остаются текстовыми, фото их ухудшит.
- **Hero первого экрана** — остаётся текстовым (скорость + панель статов).
- **Скриншоты отзывов, QR-коды.**

---

## 1. Обложки блога — 5 штук (каждой статье своя)

Сейчас у статей однотипные синие градиенты от GD — нужны 5 **разных** живых картинок.
Показываются в карточке блога (кроп `1200/630`) и в шапке статьи.

### 1.1 «Как чистить диван в домашних условиях и когда нужен мастер»

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, wide horizontal shot: close-up of a gloved hand vacuuming a light-gray fabric sofa with the soft brush attachment of a modern handheld vacuum, visible crumbs and pet hair lifting off the weave, bright modern Moscow apartment living room, soft natural daylight from a large window, light neutral palette (white, light gray, pale wood floor) with subtle sky-blue accents, shallow depth of field, high detail, no faces, no text, no logos, no watermarks
```

- Соотношение сторон: **191:100** (≈1.91:1)
- Размер: **1200×630**
- Файл: **`jc-cover-kak-chistit-divan.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → изображение записи статьи `kak-chistit-divan-doma`

### 1.2 «Сколько стоит химчистка дивана: из чего складывается цена»

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, wide horizontal shot: a freshly cleaned light-gray fabric sofa in a bright modern living room, a compact professional upholstery extractor machine with neatly coiled hoses standing on the pale wood floor in front of it, gloved cleaner's hands resting on the handle, ready-to-work arrangement, soft natural daylight from a large window, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no faces, no text, no logos, no watermarks
```

- Соотношение сторон: **191:100** (≈1.91:1)
- Размер: **1200×630**
- Файл: **`jc-cover-skolko-stoit-himchistka-divana.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → изображение записи статьи `skolko-stoit-himchistka-divana`

### 1.3 «Чем отличается сухая химчистка от влажной»

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, wide horizontal still life on a light wooden table: two square upholstery fabric samples side by side — the left one dry-cleaned with a soft brush resting on it, the right one damp after wet extraction with fine water droplets and the edge of a small nozzle, same fabric, even soft daylight from a large window, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no people, no text, no logos, no watermarks
```

- Соотношение сторон: **191:100** (≈1.91:1)
- Размер: **1200×630**
- Файл: **`jc-cover-suhaya-i-vlazhnaya-himchistka.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → изображение записи статьи `suhaya-i-vlazhnaya-himchistka`

### 1.4 «Как ухаживать за шторами между чистками»

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, wide horizontal shot: tall sheer white curtains by a tall window, a gloved hand gliding a soft brush vacuum attachment down the fabric, sunlight glowing through the sheer, fine dust motes floating in the beam, bright modern apartment, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no face, no text, no logos, no watermarks
```

- Соотношение сторон: **191:100** (≈1.91:1)
- Размер: **1200×630**
- Файл: **`jc-cover-uhod-za-shtorami-mezhdu-chistkami.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → изображение записи статьи `uhod-za-shtorami-mezhdu-chistkami`

### 1.5 «Почему матрас нужно чистить раз в год»

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, wide horizontal macro shot: white quilted mattress surface with a vacuum brush head lifting dust along the stitching, crisp folded bed linen at the edge of the frame, bright bedroom with soft daylight from a large window, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no people, no text, no logos, no watermarks
```

- Соотношение сторон: **191:100** (≈1.91:1)
- Размер: **1200×630**
- Файл: **`jc-cover-pochemu-matras-nuzhno-chistit.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → изображение записи статьи `pochemu-matras-nuzhno-chistit`

---

## 2. Служебные

### 2.1 og:image (картинка для ссылок в Telegram / ВК)

Фон под текст, который накладывает наш GD-скрипт: копирайт слева, правая треть — «воздух».

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, wide horizontal banner: close-up of a freshly cleaned light-gray fabric sofa with soft even folds filling the right two-thirds of the frame, generous empty light-neutral wall space on the left for text overlay, bright soft natural daylight, light neutral palette with subtle sky-blue accents, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **191:100** (1.91:1)
- Размер: **1200×630**
- Файл: **`jc-og.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → мета `og:image` в `<head>` (му-плагин/тема)

### 2.2 Favicon / site-icon — **не генерируем**

Конвертировать `wp-content/uploads/…/jethim-logo.svg` в PNG 512×512 и выставить
в «Настройки → Общие → Пиктограмма сайта».

---

## 3. Главная → секция «Услуги и цены» — 3 карточки

Встают внутрь карточки `.jc-services .wp-block-column`, **над заголовком `h3`**.

### 3.1 Мебель

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, close-up of a professional upholstery extraction wand cleaning a light-gray fabric sofa, visible clean stripe on the cushion, fine water spray and suction, only the cleaner's gloved hands in frame, bright modern Moscow apartment interior, soft natural daylight from a large window, light neutral palette (white, light gray, pale wood floor) with subtle sky-blue accents, clean uncluttered composition, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **4:3**
- Размер: **800×600**
- Файл: **`jc-svc-mebel.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → карточка «Мебель» на главной (позже — хаб `/uslugi/`)

### 3.2 Шторы

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, sheer white and beige curtains hanging by a tall window being cleaned in place with a professional steam wand, fabric glowing in soft daylight, gloved hand holding the nozzle, bright modern Moscow apartment interior, light neutral palette (white, light gray, pale wood floor) with subtle sky-blue accents, clean uncluttered composition, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **4:3**
- Размер: **800×600**
- Файл: **`jc-svc-shtory.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → карточка «Шторы» на главной (позже — хаб `/uslugi/`)

### 3.3 Ковры

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, close-up of a patterned area rug being deep cleaned with an extraction machine, foam and water spray, half of the pile visibly brighter than the other half, light wooden floor around, bright modern Moscow apartment interior, soft natural daylight, light neutral palette with subtle sky-blue accents, clean uncluttered composition, shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **4:3**
- Размер: **800×600**
- Файл: **`jc-svc-kovry.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → карточка «Ковры» на главной (позже — хаб `/uslugi/`)

---

## 4. Главная → секции с фото — 3 картинки

Встают во **вторую колонку** 2-колоночной схемы (текст ~60% / фото ~40%).

### 4.1 «Знакомая проблема?»

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, close-up of a worn light-gray sofa cushion showing honest real household soiling: dull fabric, faint stains, pet hair in the seams, soft natural daylight from a large window, bright modern Moscow apartment interior, light neutral palette (white, light gray, pale wood floor) with subtle sky-blue accents, shallow depth of field, high detail, no people, no text, no logos, no watermarks
```

- Соотношение сторон: **4:3**
- Размер: **1000×750**
- Файл: **`jc-problem.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → главная, секция «Знакомая проблема?» (правая колонка)

### 4.2 «Безопасная химия: дети, аллергики, животные»

**ПРОМПТ:**
```
photorealistic still life for a premium upholstery cleaning service, unbranded spray bottles with clear liquid, purple nitrile gloves, a soft brush and a folded white towel arranged on a light wooden table, a child's teddy bear softly blurred in the background near a bright window, calm airy mood, bright modern Moscow apartment interior, light neutral palette (white, light gray, pale wood) with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **4:3**
- Размер: **1000×750**
- Файл: **`jc-safe-chem.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → главная, секция «Безопасная химия: дети, аллергики, животные» (правая колонка)

### 4.3 «Кто мы»

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, a professional cleaner in a plain blue uniform using an upholstery extractor on a light-gray sofa, seen from behind at a slight angle, face not visible, equipment and hoses neatly arranged, bright modern Moscow apartment interior, soft natural daylight from a large window, light neutral palette (white, light gray, pale wood floor) with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **4:3**
- Размер: **1000×750**
- Файл: **`jc-team.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → главная, секция «Кто мы» (правая колонка; при необходимости — и на `/o-kompanii/`)

---

## 5. `/o-kompanii/` — оборудование

### 5.1 Раскладка оборудования

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, overhead flat lay of professional upholstery cleaning equipment on a light gray floor: compact extractor machine, coiled hoses, nozzles, brushes, unbranded bottles, purple nitrile gloves, tidy symmetrical arrangement, soft even daylight, light neutral palette with subtle sky-blue accents, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **4:3**
- Размер: **1000×750**
- Файл: **`jc-gear.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → страница `/o-kompanii/` (секция «Наше главное преимущество»)

---

## 6. Страницы услуг — 14 картинок

Встают **после вступительного абзаца, перед заголовком «Что входит»** каждой страницы.

### 6.1 Мягкая мебель (каталог)

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, macro of an extraction nozzle lifting dirt from a sofa armrest, visible clean stripe on the fabric, fine water spray, gloved hands only, bright modern Moscow apartment interior, soft natural daylight from a large window, light neutral palette (white, light gray, pale wood floor) with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-mebel.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-myagkoy-mebeli/`

### 6.2 Диваны

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, a freshly cleaned three-seat light-gray fabric sofa as the centerpiece of a bright modern living room, even clean fabric color, crisp cushions, soft natural daylight from a large window, light neutral palette (white, light gray, pale wood floor) with subtle sky-blue accents, clean uncluttered composition, shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-divany.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-myagkoy-mebeli/divany/`

### 6.3 Кресла, стулья, пуфы

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, cleaning a fabric dining chair seat with a small hand tool, wooden legs, bright dining area of a modern Moscow apartment, gloved hands only, soft natural daylight from a large window, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-kresla.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-myagkoy-mebeli/kresla-stulya-pufy/`

### 6.4 Кровати, подголовники

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, a fabric headboard being extraction-cleaned above crisp white bed linen, bright bedroom with soft natural daylight from a large window, gloved hands only, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-krovati.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-myagkoy-mebeli/krovati-podgolovniki/`

### 6.5 Мебель из кожи

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, a gloved hand conditioning a rich brown leather armchair with a soft cloth, supple leather texture and warm sheen, bright modern apartment, soft natural daylight from a large window, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-kozha.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-myagkoy-mebeli/mebel-iz-kozhi/`

### 6.6 Химчистка штор

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, tall sheer curtains being steam cleaned in place by a tall window, professional wand and gloved hand, fabric glowing in soft daylight, bright modern Moscow apartment, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-shtory.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-shtor/`

### 6.7 Химчистка ковров

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, an extraction wand deep cleaning a patterned wool rug, a clean bright trail through the pile, fine water spray, light wooden floor around, bright modern apartment, soft natural daylight, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-kovry.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-kovrov/`

### 6.8 Химчистка ковролина

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, a professional carpet cleaning machine leaving clean bright stripes on wall-to-wall carpet in a bright office corridor, soft even daylight, light neutral palette with subtle sky-blue accents, clean composition, shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-kovrolin.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-kovrolina/`

### 6.9 Химчистка матрасов

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, macro of a white quilted mattress surface being extraction-cleaned, fine foam and suction at the nozzle, spotless fabric, bright bedroom with soft natural daylight, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-matras.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/himchistka-matrasov/`

### 6.10 Удаление запахов

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, a spray bottle and a cloth treating a sofa armrest next to an open window with sheer curtains, fresh airy daylight, gloved hand only, bright modern Moscow apartment, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-zapah.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/udalenie-zapahov/`

### 6.11 Удаление катышков

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, macro of a fabric shaver removing pills from knitted upholstery, half of the surface already smooth and restored, gloved hand holding the device, soft natural daylight, light neutral palette with subtle sky-blue accents, shallow depth of field, high detail, no text, no logos, no watermarks
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-katyshki.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/udalenie-katyshkov/`

### 6.12 Защитная пропитка ткани

**ПРОМПТ:**
```
photorealistic macro advertising photography for a premium upholstery cleaning service, water droplets beading up on a protected light-gray fabric surface, hydrophobic effect with crisp refraction, soft natural daylight from a window, light neutral palette with subtle sky-blue accents, very shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-propitka.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/zashchitnaya-propitka-tkani/`

### 6.13 Сушка мебели

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, a professional air mover dryer aimed at a freshly cleaned light-gray sofa, machine standing on the pale wood floor, bright modern apartment room, soft natural daylight from a large window, light neutral palette with subtle sky-blue accents, clean composition, shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-sushka.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/sushka-mebeli/`

### 6.14 Для юрлиц

**ПРОМПТ:**
```
photorealistic advertising photography for a premium upholstery cleaning service, interior of a stylish empty cafe with clean upholstered banquettes and chairs, tables neatly set, ready for guests, large windows with soft daylight, light neutral palette (white, light gray, pale wood) with subtle sky-blue accents, clean uncluttered composition, shallow depth of field, high detail, no text, no logos, no watermarks, no people
```

- Соотношение сторон: **16:9**
- Размер: **1600×900**
- Файл: **`jc-p-b2b.jpg`**
- Куда: `D:\dev\projects\.scripts\img\` → `/uslugi/dlya-yurlic/`

---

## Технические заметки (для меня же)

- Всё, что лежит в `D:\dev\projects\.scripts\img\`, я загружаю скриптом в медиатеку
  (`wp_upload_bits()` / `wp_insert_attachment()`), ручные правки в WP не нужны.
- Контент страниц и постов генерируют `e6_pages.php` и `e6_posts.php` → вставлять
  картинки нужно **в эти скрипты**, иначе регенерация перезапишет разметку.
- Обложки блога: `e6_posts.php::p6_set_cover()` сейчас ищет файл `jc-cover-<slug>.png`
  и, не найдя его, рисует GD-градиент. Перед загрузкой новых обложек поиск нужно
  расширить на `.jpg`, чтобы скрипт подставил нашу картинку, а не нарисовал свою.
- Проверка после вставки: `e6_check.sh`, `shot/rd_verify.js`, визуально главная 1440/390,
  2–3 страницы услуг, блог.
