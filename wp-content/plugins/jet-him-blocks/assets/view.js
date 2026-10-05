/**
 * JetHim: живой пересчёт калькулятора.
 *
 * Формулы — дубль server-side логики в jet-him-blocks/includes/calc.php
 * (Итог = позиции + допы + выезд − скидка). Если меняете одну сторону —
 * меняйте и вторую, тесты сверки с PRICE.md гоняются по PHP.
 *
 * Работает и в редакторе: элемент ищется по [data-jc-calc], события —
 * делегированием на document, поэтому замена DOM при перерисовке блока
 * ничего не ломает.
 */
( function () {
	'use strict';

	/* ------------------------------------------------------------------
	   Расчёт (без DOM) — state и config читаемые, результат разбивка.
	------------------------------------------------------------------ */
	function calcTotal( config, state ) {
		var lines = [];
		var flags = [];
		var i, item, n, sum, key, rate;

		// Мебель: цена × кол-во, матрас «обе стороны» → ×2.
		var furniture = 0;
		for ( i = 0; i < config.furniture.length; i++ ) {
			item = config.furniture[ i ];
			n = parseInt( state.qty[ item.id ], 10 ) || 0;
			if ( n < 1 ) {
				continue;
			}
			sum = item.price * n;
			if ( item.type === 'mattress' && state.both[ item.id ] ) {
				sum *= 2;
			}
			furniture += sum;
			lines.push( {
				label: item.label + ( item.type === 'mattress' && state.both[ item.id ] ? ', обе стороны' : '' ) + ' × ' + n,
				sum: sum
			} );
		}

		// Шторы: за м² × площадь.
		var curtains = 0;
		if ( state.curtain.area > 0 && state.curtain.material && state.curtain.build ) {
			key = state.curtain.material + '|' + state.curtain.build;
			rate = config.curtains.prices[ key ] || 0;
			curtains = rate * state.curtain.area;
			if ( curtains > 0 ) {
				lines.push( {
					label: labelOf( config.curtains.materials, state.curtain.material ) + ', ' +
						labelOf( config.curtains.builds, state.curtain.build ) + ' — ' +
						num( state.curtain.area ) + ' м²',
					sum: curtains
				} );
			}
		}

		// Ковры: за м² × площадь, только существующие комбинации.
		var carpets = 0;
		if ( state.carpet.area > 0 && state.carpet.material && state.carpet.type ) {
			key = state.carpet.material + '|' + state.carpet.type;
			rate = config.carpets.prices[ key ] || 0;
			carpets = rate * state.carpet.area;
			if ( carpets > 0 ) {
				lines.push( {
					label: labelOf( config.carpets.materials, state.carpet.material ) + ', ' +
						labelOf( config.carpets.types, state.carpet.type ) + ' — ' +
						num( state.carpet.area ) + ' м²',
					sum: carpets
				} );
			}
		}

		// Допы: с ценой — в сумму, без цены — только флаги.
		var extras = 0;
		for ( i = 0; i < config.extras.length; i++ ) {
			var extra = config.extras[ i ];
			var price = extra.price === null || extra.price === '' || typeof extra.price === 'undefined' ? null : extra.price;
			if ( price === null ) {
				if ( state.flags[ extra.id ] ) {
					flags.push( extra.label );
				}
				continue;
			}
			n = parseInt( state.extras[ extra.id ], 10 ) || 0;
			if ( n < 1 ) {
				continue;
			}
			extras += price * n;
			lines.push( { label: extra.label + ' × ' + n, sum: price * n } );
		}

		// Выезд: до free_km бесплатно, дальше per_km ₽/км.
		var km = parseInt( state.km, 10 ) || 0;
		var trip = km > config.trip.free_km ? ( km - config.trip.free_km ) * config.trip.per_km : 0;
		if ( trip > 0 ) {
			lines.push( { label: 'Выезд: ' + km + ' км от МКАД', sum: trip } );
		}

		var subtotal = furniture + curtains + carpets + extras + trip;
		var discount = state.discount ? Math.round( subtotal * config.discount.percent / 100 ) : 0;
		if ( discount > 0 ) {
			lines.push( {
				label: 'Скидка постоянному клиенту −' + config.discount.percent + '%',
				sum: -discount
			} );
		}

		return {
			furniture: furniture,
			curtains: curtains,
			carpets: carpets,
			extras: extras,
			trip: trip,
			subtotal: subtotal,
			discount: discount,
			total: subtotal - discount,
			lines: lines,
			flags: flags
		};
	}

	/* ------------------------------------------------------------------
	   Форматирование и подписи.
	------------------------------------------------------------------ */
	function num( n ) {
		var rounded = Math.round( ( Number( n ) || 0 ) * 100 ) / 100;
		return String( rounded ).replace( '.', ',' );
	}

	function money( n ) {
		try {
			return Number( Math.round( Number( n ) || 0 ) ).toLocaleString( 'ru-RU' );
		} catch ( e ) {
			return String( Math.round( Number( n ) || 0 ) );
		}
	}

	function labelOf( list, id ) {
		for ( var i = 0; i < list.length; i++ ) {
			if ( list[ i ].id === id ) {
				return list[ i ].label;
			}
		}
		return '';
	}

	/* ------------------------------------------------------------------
	   Состояние и DOM.
	------------------------------------------------------------------ */
	function readConfig( el ) {
		if ( el.dataset.jcConfig ) {
			try {
				return JSON.parse( el.dataset.jcConfig );
			} catch ( e ) {}
		}
		return window.JHB && window.JHB.calc ? window.JHB.calc : null;
	}

	function makeState( el ) {
		var state = {
			qty: {},
			both: {},
			extras: {},
			flags: {},
			curtain: { material: '', build: '', w: 0, h: 0, area: 0 },
			carpet: { material: '', type: '', area: 0 },
			km: 0,
			discount: false,
			tab: 'furniture'
		};

		var cm = el.querySelector( '[data-jc-curtain="material"]' );
		var cb = el.querySelector( '[data-jc-curtain="build"]' );
		state.curtain.material = cm ? cm.value : '';
		state.curtain.build = cb ? cb.value : '';

		var pm = el.querySelector( '[data-jc-carpet="material"]' );
		var pt = el.querySelector( '[data-jc-carpet="type"]' );
		state.carpet.material = pm ? pm.value : '';
		state.carpet.type = pt ? pt.value : '';

		return state;
	}

	function refresh( el ) {
		if ( ! el._jc || ! el._jc.config ) {
			return;
		}

		var config = el._jc.config;
		var state = el._jc.state;
		var result = calcTotal( config, state );

		// Вкладки.
		var tabs = el.querySelectorAll( '[data-jc-tab]' );
		var panels = el.querySelectorAll( '[data-jc-panel]' );
		Array.prototype.forEach.call( tabs, function ( tab ) {
			var active = tab.getAttribute( 'data-jc-tab' ) === state.tab;
			tab.classList.toggle( 'is-active', active );
			tab.setAttribute( 'aria-selected', active ? 'true' : 'false' );
		} );
		Array.prototype.forEach.call( panels, function ( panel ) {
			var active = panel.getAttribute( 'data-jc-panel' ) === state.tab;
			panel.classList.toggle( 'is-active', active );
			if ( active ) {
				panel.removeAttribute( 'hidden' );
			} else {
				panel.setAttribute( 'hidden', '' );
			}
		} );

		// Цены за м² и фильтрация несуществующих комбинаций.
		syncCombos( el, config, state );

		// Итог и разбивка.
		var total = el.querySelector( '[data-jc-total]' );
		if ( total ) {
			total.textContent = money( result.total ) + ' ₽';
		}

		var list = el.querySelector( '[data-jc-lines]' );
		if ( list ) {
			list.innerHTML = '';
			if ( result.lines.length ) {
				result.lines.forEach( function ( line ) {
					var li = document.createElement( 'li' );
					var label = document.createElement( 'span' );
					label.textContent = line.label;
					var sum = document.createElement( 'span' );
					sum.className = 'jc-calc__line-sum';
					sum.textContent = money( line.sum ) + ' ₽';
					li.appendChild( label );
					li.appendChild( sum );
					list.appendChild( li );
				} );
				list.removeAttribute( 'hidden' );
			} else {
				list.setAttribute( 'hidden', '' );
			}
		}

		// Скрытые поля формы (Этап 5 подхватит их Fluent Forms).
		var totalField = el.querySelector( '[data-jc-total-field]' );
		if ( totalField ) {
			totalField.value = String( result.total );
		}
		var detailsField = el.querySelector( '[data-jc-details-field]' );
		if ( detailsField ) {
			var parts = result.lines.map( function ( line ) {
				return line.label + ' — ' + money( line.sum ) + ' ₽';
			} );
			if ( result.flags.length ) {
				parts.push( 'Флаги: ' + result.flags.join( ', ' ) );
			}
			detailsField.value = parts.join( '; ' );
		}

		if ( window.JC && window.JC.emit ) {
			window.JC.emit( 'calc:change', { total: result.total, lines: result.lines, flags: result.flags } );
		}
	}

	function syncCombos( el, config, state ) {
		var cRate = el.querySelector( '[data-jc-curtain-rate]' );
		if ( cRate ) {
			var cKey = state.curtain.material + '|' + state.curtain.build;
			cRate.textContent = config.curtains.prices[ cKey ] ? money( config.curtains.prices[ cKey ] ) : '—';
		}
		var pRate = el.querySelector( '[data-jc-carpet-rate]' );
		if ( pRate ) {
			var pKey = state.carpet.material + '|' + state.carpet.type;
			pRate.textContent = config.carpets.prices[ pKey ] ? money( config.carpets.prices[ pKey ] ) : '—';
		}

		filterSelect( el, '[data-jc-curtain="material"]', state.curtain.material, config.curtains, state.curtain.build, 'material', function ( value ) {
			state.curtain.material = value;
		} );
		filterSelect( el, '[data-jc-curtain="build"]', state.curtain.build, config.curtains, state.curtain.material, 'build', function ( value ) {
			state.curtain.build = value;
		} );
		filterSelect( el, '[data-jc-carpet="material"]', state.carpet.material, config.carpets, state.carpet.type, 'material', function ( value ) {
			state.carpet.material = value;
		} );
		filterSelect( el, '[data-jc-carpet="type"]', state.carpet.type, config.carpets, state.carpet.material, 'type', function ( value ) {
			state.carpet.type = value;
		} );
	}

	/**
	 * Оставляет в селекте только комбинации с ценой; если текущая
	 * выбранная опция стала невалидной — переключает на первую валидную.
	 *
	 * @param {string}   selector   Селект.
	 * @param {string}   current    Текущее значение.
	 * @param {object}   group      curtains|carpets (materials/builds/types, prices).
	 * @param {string}   otherValue Значение второй оси (для material — build|type).
	 * @param {string}   kind       'material' | 'build' | 'type'.
	 * @param {Function} apply      Куда записать подставленное значение.
	 */
	function filterSelect( el, selector, current, group, otherValue, kind, apply ) {
		var select = el.querySelector( selector );
		if ( ! select || ! group ) {
			return;
		}
		var items = group[ kind + 's' ];
		var prices = group.prices;
		var valid = {};

		items.forEach( function ( item ) {
			var key = kind === 'material' ? item.id + '|' + otherValue : otherValue + '|' + item.id;
			if ( prices[ key ] ) {
				valid[ item.id ] = true;
			}
		} );

		var firstValid = '';
		Array.prototype.forEach.call( select.options, function ( option ) {
			var ok = !! valid[ option.value ];
			option.disabled = ! ok;
			if ( ok && ! firstValid ) {
				firstValid = option.value;
			}
		} );

		if ( ! valid[ current ] && firstValid ) {
			select.value = firstValid;
			apply( firstValid );
		}
	}

	function init( el ) {
		if ( ! el._jc ) {
			return;
		}
		refresh( el );
	}

	function prepare( el ) {
		if ( el.dataset.jcReady ) {
			return;
		}
		var config = readConfig( el );
		if ( ! config ) {
			return;
		}
		el.dataset.jcReady = '1';
		el._jc = { config: config, state: makeState( el ) };
		init( el );
	}

	/**
	 * Слайдер «до/после»: нативный input[type=range] уже умеет мышь,
	 * палец и стрелки — от него только двигаем CSS-переменную --pos.
	 */
	function prepareBa( frame ) {
		if ( frame.dataset.jcReadyBa ) {
			return;
		}
		frame.dataset.jcReadyBa = '1';

		var range = frame.querySelector( '[data-jc-ba-range]' );
		if ( ! range ) {
			return;
		}

		function sync() {
			frame.style.setProperty( '--pos', range.value + '%' );
		}

		range.addEventListener( 'input', sync );
		range.addEventListener( 'change', sync );
		sync();
	}

	function scan( root ) {
		if ( ! root || ! root.querySelectorAll ) {
			return;
		}
		if ( root.matches ) {
			if ( root.matches( '[data-jc-calc]' ) ) {
				prepare( root );
			}
			if ( root.matches( '[data-jc-ba]' ) ) {
				prepareBa( root );
			}
		}
		Array.prototype.forEach.call( root.querySelectorAll( '[data-jc-calc]' ), prepare );
		Array.prototype.forEach.call( root.querySelectorAll( '[data-jc-ba]' ), prepareBa );
	}

	/* ------------------------------------------------------------------
	   Обработчики (делегирование — переживает перерисовку в редакторе).
	------------------------------------------------------------------ */
	function rootOf( target ) {
		var node = target && target.closest ? target.closest( '[data-jc-calc]' ) : null;
		return node && node._jc ? node : null;
	}

	function clampQty( value ) {
		var n = parseInt( value, 10 );
		if ( isNaN( n ) || n < 0 ) {
			n = 0;
		}
		return n;
	}

	document.addEventListener( 'click', function ( e ) {
		var target = e.target;
		if ( ! target || ! target.closest ) {
			return;
		}

		// Вкладка.
		var tab = target.closest( '[data-jc-tab]' );
		if ( tab ) {
			var el = rootOf( tab );
			if ( el ) {
				el._jc.state.tab = tab.getAttribute( 'data-jc-tab' );
				refresh( el );
			}
			return;
		}

		// Степпер мебели.
		var step = target.closest( '[data-jc-step]' );
		if ( step ) {
			var elStep = rootOf( step );
			if ( elStep ) {
				var id = step.getAttribute( 'data-jc-step' );
				var dir = parseInt( step.getAttribute( 'data-jc-dir' ), 10 ) || 0;
				var next = clampQty( ( elStep._jc.state.qty[ id ] || 0 ) + dir );
				elStep._jc.state.qty[ id ] = next;
				var input = elStep.querySelector( '[data-jc-qty="' + id + '"]' );
				if ( input ) {
					input.value = next;
				}
				refresh( elStep );
			}
			return;
		}

		// Степпер допов.
		var stepEx = target.closest( '[data-jc-step-extra]' );
		if ( stepEx ) {
			var elEx = rootOf( stepEx );
			if ( elEx ) {
				var exId = stepEx.getAttribute( 'data-jc-step-extra' );
				var exDir = parseInt( stepEx.getAttribute( 'data-jc-dir' ), 10 ) || 0;
				var exNext = clampQty( ( elEx._jc.state.extras[ exId ] || 0 ) + exDir );
				elEx._jc.state.extras[ exId ] = exNext;
				var exInput = elEx.querySelector( '[data-jc-extra="' + exId + '"]' );
				if ( exInput ) {
					exInput.value = exNext;
				}
				refresh( elEx );
			}
			return;
		}

		// Отправка расчёта (кнопка «Отправить расчёт»).
		var submit = target.closest( '[data-jc-calc-submit]' );
		if ( submit ) {
			var elSubmit = rootOf( submit );
			if ( elSubmit ) {
				refresh( elSubmit );
				var payload = {
					total: elSubmit.querySelector( '[data-jc-total-field]' ).value,
					details: elSubmit.querySelector( '[data-jc-details-field]' ).value
				};
				if ( window.JC && window.JC.emit ) {
					window.JC.emit( 'calc:submit', payload );
				}
				var form = document.querySelector( '[data-jc-form]' );
				if ( form ) {
					form.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					var first = form.querySelector( 'input:not([type="hidden"]), textarea' );
					if ( first ) {
						first.focus( { preventScroll: true } );
					}
				}
			}
		}
	} );

	function handleField( e ) {
		var target = e.target;
		var el = rootOf( target );
		if ( ! el || ! target.hasAttribute ) {
			return;
		}

		var state = el._jc.state;
		var config = el._jc.config;
		var attr;

		if ( target.hasAttribute( 'data-jc-qty' ) ) {
			state.qty[ target.getAttribute( 'data-jc-qty' ) ] = clampQty( target.value );
		} else if ( target.hasAttribute( 'data-jc-extra' ) ) {
			state.extras[ target.getAttribute( 'data-jc-extra' ) ] = clampQty( target.value );
		} else if ( target.hasAttribute( 'data-jc-both' ) ) {
			state.both[ target.getAttribute( 'data-jc-both' ) ] = target.checked;
		} else if ( target.hasAttribute( 'data-jc-flag' ) ) {
			state.flags[ target.getAttribute( 'data-jc-flag' ) ] = target.checked;
		} else if ( target.hasAttribute( 'data-jc-discount' ) ) {
			state.discount = target.checked;
		} else if ( target.hasAttribute( 'data-jc-km' ) ) {
			state.km = clampQty( target.value );
		} else if ( ( attr = target.getAttribute( 'data-jc-curtain' ) ) ) {
			state.curtain[ attr ] = target.value;
		} else if ( ( attr = target.getAttribute( 'data-jc-carpet' ) ) ) {
			state.carpet[ attr ] = target.value;
		} else if ( ( attr = target.getAttribute( 'data-jc-curtain-size' ) ) ) {
			var v = Math.max( 0, parseFloat( String( target.value ).replace( ',', '.' ) ) || 0 );
			if ( attr === 'area' ) {
				state.curtain.area = v;
				state.curtain.w = 0;
				state.curtain.h = 0;
				var w = el.querySelector( '[data-jc-curtain-size="w"]' );
				var h = el.querySelector( '[data-jc-curtain-size="h"]' );
				if ( w ) {
					w.value = '';
				}
				if ( h ) {
					h.value = '';
				}
			} else {
				state.curtain[ attr ] = v;
				state.curtain.area = Math.round( state.curtain.w * state.curtain.h * 100 ) / 100;
				var areaInput = el.querySelector( '[data-jc-curtain-size="area"]' );
				if ( areaInput && document.activeElement !== areaInput ) {
					areaInput.value = state.curtain.area || '';
				}
			}
		} else if ( ( attr = target.getAttribute( 'data-jc-carpet-size' ) ) ) {
			state.carpet[ attr ] = Math.max( 0, parseFloat( String( target.value ).replace( ',', '.' ) ) || 0 );
		} else {
			return;
		}

		refresh( el );
	}

	document.addEventListener( 'change', handleField );
	document.addEventListener( 'input', handleField );

	/* ------------------------------------------------------------------
	   Инициализация: первый проход + подглядка за заменой DOM
	   (превью блока в редакторе перерисовывается сервером при правке).
	------------------------------------------------------------------ */
	function boot() {
		scan( document );

		if ( window.MutationObserver && document.body ) {
			var observer = new MutationObserver( function ( mutations ) {
				mutations.forEach( function ( mutation ) {
					Array.prototype.forEach.call( mutation.addedNodes, function ( node ) {
						if ( node.nodeType === 1 ) {
							scan( node );
						}
					} );
				} );
			} );
			observer.observe( document.body, { childList: true, subtree: true } );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// Доступно для тестов и для будущих блоков (Этап 3+).
	window.JC = window.JC || {};
	window.JC.calcTotal = calcTotal;
	window.JC.money = money;
} )();
