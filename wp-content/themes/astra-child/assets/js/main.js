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
} )();
