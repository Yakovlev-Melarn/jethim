/**
 * JetHim — скрипты дочерней темы.
 *
 * Этап 3: сюда пойдут логика калькулятора (jc/calc) и слайдера до/после
 * (jc/before-after). Блоки подключают собственные файлы, этот — только
 * общее: поведение шапки, подвала, мелкие UI-фиксы.
 */
( function () {
	'use strict';

	// Общие точки расширения для будущих блоков.
	window.JC = window.JC || {
		contacts: window.JC && window.JC.contacts ? window.JC.contacts : {},
		hooks: {}
	};

	/**
	 * Зарегистрировать обработчик события блока.
	 *
	 * @param {string}   name Имя события.
	 * @param {Function} fn   Обработчик.
	 */
	window.JC.on = function ( name, fn ) {
		( this.hooks[ name ] = this.hooks[ name ] || [] ).push( fn );
	};

	/**
	 * Отправить событие блока.
	 *
	 * @param {string} name Имя события.
	 * @param {*}      data Данные.
	 */
	window.JC.emit = function ( name, data ) {
		( this.hooks[ name ] || [] ).forEach( function ( fn ) {
			fn( data );
		} );
	};

	/* ------------------------------------------------------------------
	   Календарь дат (Fluent Forms → flatpickr v4): русская локаль.
	   FF инициализирует flatpickr лениво — ждём появления инстанса,
	   локализуем глобально (для новых) и доправляем уже созданный.
	------------------------------------------------------------------ */
	( function jcCalendarLocale() {
		var RU = {
			weekdays: {
				shorthand: [ 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб' ],
				longhand: [ 'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота' ]
			},
			months: {
				shorthand: [ 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек' ],
				longhand: [ 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь' ]
			},
			firstDayOfWeek: 1,
			rangeSeparator: ' — ',
			weekAbbreviation: 'Нед',
			scrollTitle: 'Прокрутите, чтобы увеличить',
			toggleTitle: 'Переключить',
			amPM: [ 'ДП', 'ПП' ],
			yearAriaLabel: 'Год',
			time_24hr: true
		};
		var EN_MONTHS = [ 'January', 'February', 'March', 'April', 'May', 'June',
			'July', 'August', 'September', 'October', 'November', 'December' ];
		var EN2RU = { Mon: 'Пн', Tue: 'Вт', Wed: 'Ср', Thu: 'Чт', Fri: 'Пт', Sat: 'Сб', Sun: 'Вс' };
		var localized = false;

		function localizeGlobal() {
			if ( localized || ! window.flatpickr ) {
				return;
			}
			try {
				if ( window.flatpickr.localize ) {
					window.flatpickr.localize( RU );
				}
				if ( window.flatpickr.l10ns ) {
					window.flatpickr.l10ns.ru = RU;
				}
				localized = true;
			} catch ( e ) {}
		}

		function patchInstance( el ) {
			var inst = el._flatpickr;
			if ( ! inst || el.__jcRu ) {
				return;
			}
			el.__jcRu = true;
			try {
				inst.l10n = Object.assign( {}, inst.l10n, RU );
				// Выезд не раньше послезавтра — ближайший возможный день.
				var min = new Date();
				min.setHours( 0, 0, 0, 0 );
				min.setDate( min.getDate() + 2 );
				if ( inst.set ) {
					inst.set( 'minDate', min );
				}
				if ( inst.redraw ) {
					inst.redraw();
				}
			} catch ( e ) {}
		}

		function patchCalendarDom() {
			var cal = document.querySelector( '.flatpickr-calendar' );
			if ( ! cal ) {
				return;
			}
			// Месяцы в дропдауне (v4: monthDropdown) — опции строятся один раз.
			var sel = cal.querySelector( '.flatpickr-monthDropdown-months' );
			if ( sel && sel.options ) {
				Array.prototype.forEach.call( sel.options, function ( o, i ) {
					if ( EN_MONTHS.indexOf( ( o.textContent || '' ).trim() ) >= 0 && RU.months.longhand[ i ] ) {
						o.textContent = RU.months.longhand[ i ];
					}
				} );
			}
			// Заголовок месяца (тип static).
			var cm = cal.querySelector( '.cur-month' );
			if ( cm && ! cm.options ) {
				var idx = EN_MONTHS.indexOf( ( cm.textContent || '' ).trim() );
				if ( idx >= 0 ) {
					cm.textContent = RU.months.longhand[ idx ];
				}
			}
			// Подписи дней недели.
			Array.prototype.forEach.call( cal.querySelectorAll( '.flatpickr-weekday' ), function ( w ) {
				var t = ( w.textContent || '' ).trim().slice( 0, 3 );
				if ( EN2RU[ t ] ) {
					w.textContent = EN2RU[ t ];
				}
			} );
		}

		var tries = 0;
		var timer = setInterval( function () {
			++tries;
			localizeGlobal();
			Array.prototype.forEach.call( document.querySelectorAll( '.flatpickr-input' ), patchInstance );
			patchCalendarDom();
			if ( tries > 60 ) {
				clearInterval( timer );
			}
		}, 500 );
	} )();

	/* ------------------------------------------------------------------
	   Этап 5: калькулятор → скрытые поля формы Fluent Forms.
	   Блок calc рисует свои поля jc_calc_total[<id>], а форма заявки
	   несёт скрытые jc_calc_total / jc_calc_details — копируем значение,
	   чтобы в заявке уехала сумма и разбивка.
	------------------------------------------------------------------ */
	function jcSyncCalcToForm() {
		var calc = document.querySelector( '[data-jc-calc]' );
		if ( ! calc ) {
			return;
		}
		var totalField = calc.querySelector( '[data-jc-total-field]' );
		var detailsField = calc.querySelector( '[data-jc-details-field]' );
		if ( ! totalField ) {
			return;
		}
		var total = totalField.value;
		var details = detailsField ? detailsField.value : '';

		Array.prototype.forEach.call(
			document.querySelectorAll( 'form [name="jc_calc_total"]' ),
			function ( el ) { el.value = total; }
		);
		Array.prototype.forEach.call(
			document.querySelectorAll( 'form [name="jc_calc_details"]' ),
			function ( el ) { el.value = details; }
		);
	}

	[ 'input', 'change', 'click' ].forEach( function ( type ) {
		document.addEventListener( type, function ( e ) {
			var t = e.target;
			if ( t && t.closest && t.closest( '[data-jc-calc]' ) ) {
				jcSyncCalcToForm();
			}
		}, true );
	} );

	if ( window.JC && window.JC.on ) {
		window.JC.on( 'calc:submit', jcSyncCalcToForm );
	}
	jcSyncCalcToForm();

	/* Имя услуги в скрытое поле формы (страница услуги). */
	var serviceField = document.querySelector( 'form [name="service"]' );
	if ( serviceField && ! serviceField.value ) {
		var h1 = document.querySelector( 'h1' );
		if ( h1 ) {
			serviceField.value = h1.textContent.replace( /\s+/g, ' ' ).trim();
		}
	}

	/* ------------------------------------------------------------------
	   Модальные окна с формами: [data-jc-modal-open="ключ"] открывает
	   #jc-modal-ключ (рендерятся темой в подвале), закрытие — по крестику,
	   фону и Esc.
	------------------------------------------------------------------ */
	( function jcModals() {
		var lastFocus = null;

		function open( key ) {
			var m = document.getElementById( 'jc-modal-' + key );
			if ( ! m ) {
				return false;
			}
			lastFocus = document.activeElement;
			m.classList.add( 'is-open' );
			m.setAttribute( 'aria-hidden', 'false' );
			document.body.classList.add( 'jc-modal-open' );
			// Фокус — на карточку окна (tabindex="-1"): Esc/Tab работают сразу,
			// крестик остаётся «тихим». Откладываем на кадр — окно должно показаться.
			var card = m.querySelector( '.jc-modal__card' );
			if ( card && card.focus ) {
				window.requestAnimationFrame( function () {
					card.focus();
				} );
			}
			return true;
		}

		function close( m ) {
			m.classList.remove( 'is-open' );
			m.setAttribute( 'aria-hidden', 'true' );
			document.body.classList.remove( 'jc-modal-open' );
			if ( lastFocus && lastFocus.focus ) {
				lastFocus.focus();
			}
		}

		document.addEventListener( 'click', function ( e ) {
			var t = e.target;
			if ( ! t || ! t.closest ) {
				return;
			}

			var opener = t.closest( '[data-jc-modal-open]' );
			if ( opener ) {
				if ( open( opener.getAttribute( 'data-jc-modal-open' ) ) ) {
					e.preventDefault();
				}
				return;
			}

			var closer = t.closest( '[data-jc-modal-close]' );
			if ( closer ) {
				var m = closer.closest( '.jc-modal' );
				if ( m ) {
					close( m );
				}
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' || e.keyCode === 27 ) {
				var opened = document.querySelector( '.jc-modal.is-open' );
				if ( opened ) {
					close( opened );
				}
			}
		} );
	} )();

	/* ------------------------------------------------------------------
	   FAQ-аккордеон: первый вопрос открыт, остальные свёрнуты.
	------------------------------------------------------------------ */
	( function jcFaqAccordion() {
		Array.prototype.forEach.call( document.querySelectorAll( '.jc-faq' ), function ( faq ) {
			var items = faq.querySelectorAll( '.jc-faq__item' );
			if ( ! items.length ) {
				return;
			}
			faq.classList.add( 'is-js' );
			Array.prototype.forEach.call( items, function ( item, i ) {
				var h = item.querySelector( 'h3' );
				if ( ! h ) {
					return;
				}
				h.setAttribute( 'role', 'button' );
				h.setAttribute( 'tabindex', '0' );
				if ( i === 0 ) {
					item.classList.add( 'is-open' );
				}
				h.setAttribute( 'aria-expanded', item.classList.contains( 'is-open' ) ? 'true' : 'false' );

				var toggle = function () {
					var isOpen = item.classList.toggle( 'is-open' );
					h.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
				};
				h.addEventListener( 'click', toggle );
				h.addEventListener( 'keydown', function ( e ) {
					if ( e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32 ) {
						e.preventDefault();
						toggle();
					}
				} );
			} );
		} );
	} )();

	/* ------------------------------------------------------------------
	   Гамбургер полосы меню (.jc-menubar): на мобильных список пунктов
	   сворачивается, раскрывается кнопкой.
	------------------------------------------------------------------ */
	( function jcMenuBar() {
		var toggle = document.querySelector( '.jc-menubar__toggle' );
		if ( ! toggle ) {
			return;
		}
		toggle.addEventListener( 'click', function () {
			var bar = toggle.closest( '.jc-menubar' );
			if ( ! bar ) {
				return;
			}
			var isOpen = bar.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );

		/* На мобильных пункт с подменю раскрывается аккордеоном (сама
		   ссылка при первом тапе не открывается — раскрытие приоритетнее). */
		document.addEventListener( 'click', function ( e ) {
			if ( window.innerWidth > 782 ) {
				return;
			}
			var a = e.target.closest ? e.target.closest( '.jc-menubar__nav > ul > li > a' ) : null;
			if ( ! a ) {
				return;
			}
			var li = a.parentElement;
			if ( ! li.querySelector( ':scope > .sub-menu' ) ) {
				return;
			}
			e.preventDefault();
			var open = li.classList.toggle( 'is-sub-open' );
			a.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
	} )();

	/* ------------------------------------------------------------------
	   Загрузка фото к заявке: Fluent Forms free без загрузки файлов,
	   поэтому свой file-контрол → REST jc/v1/photo → URL в скрытое
	   поле photo_url формы.
	------------------------------------------------------------------ */
	( function jcPhotoUpload() {
		if ( ! window.JC_REST || ! window.JC_REST.root ) {
			return;
		}

		var ALLOWED = [ 'image/jpeg', 'image/png', 'image/webp' ];
		var MAX = 10 * 1024 * 1024;

		Array.prototype.forEach.call( document.querySelectorAll( 'form [name="photo_url"]' ), function ( field ) {
			var group = field.closest( '.ff-el-group' ) || field.parentNode;
			if ( ! group || ! group.parentNode ) {
				return;
			}

			var box = document.createElement( 'div' );
			box.className = 'jc-photo';
			box.innerHTML =
				'<span class="jc-photo__label">Фото загрязнения</span>' +
				'<button type="button" class="jc-photo__btn">Загрузить фото…</button>' +
				'<input type="file" accept="image/jpeg,image/png,image/webp" hidden>' +
				'<div class="jc-photo__preview"><img alt=""><span class="jc-photo__name"></span>' +
				'<button type="button" class="jc-photo__remove">Убрать</button></div>' +
				'<div class="jc-photo__hint">До 10 МБ: JPG, PNG или WebP. Можно и вручную вставить ссылку в поле ниже.</div>' +
				'<div class="jc-photo__status" role="status"></div>';
			group.parentNode.insertBefore( box, group );

			var btn = box.querySelector( '.jc-photo__btn' );
			var input = box.querySelector( 'input[type="file"]' );
			var preview = box.querySelector( '.jc-photo__preview' );
			var img = box.querySelector( '.jc-photo__preview img' );
			var name = box.querySelector( '.jc-photo__name' );
			var remove = box.querySelector( '.jc-photo__remove' );
			var status = box.querySelector( '.jc-photo__status' );

			var setStatus = function ( text, cls ) {
				status.textContent = text || '';
				status.className = 'jc-photo__status' + ( cls ? ' ' + cls : '' );
			};

			var clear = function () {
				field.value = '';
				input.value = '';
				preview.classList.remove( 'is-visible' );
				setStatus( '' );
			};

			btn.addEventListener( 'click', function () {
				input.click();
			} );
			remove.addEventListener( 'click', clear );

			input.addEventListener( 'change', function () {
				var file = input.files && input.files[ 0 ];
				if ( ! file ) {
					return;
				}
				if ( file.size > MAX ) {
					setStatus( 'Файл больше 10 МБ — выберите фото поменьше.', 'is-error' );
					input.value = '';
					return;
				}
				if ( file.type && ALLOWED.indexOf( file.type ) < 0 ) {
					setStatus( 'Нужен JPG, PNG или WebP.', 'is-error' );
					input.value = '';
					return;
				}

				setStatus( 'Загружаем…' );
				var data = new FormData();
				data.append( 'photo', file, file.name );

				fetch( window.JC_REST.root + '/photo', {
					method: 'POST',
					headers: { 'X-WP-Nonce': window.JC_REST.nonce },
					body: data
				} )
					.then( function ( r ) {
						return r.json().catch( function () {
							return null;
						} );
					} )
					.then( function ( res ) {
						if ( ! res || ! res.url ) {
							setStatus(
								( res && res.message ) ||
									'Не удалось загрузить фото — вставьте ссылку ниже или отправьте без фото.',
								'is-error'
							);
							return;
						}
						field.value = res.url;
						img.src = res.url;
						name.textContent = res.name || file.name;
						preview.classList.add( 'is-visible' );
						setStatus( 'Фото загружено — приложим к заявке.', 'is-ok' );
					} )
					.catch( function () {
						setStatus(
							'Ошибка сети — фото не загрузилось. Вставьте ссылку ниже или отправьте без фото.',
							'is-error'
						);
					} );
			} );
		} );
	} )();
} )();
