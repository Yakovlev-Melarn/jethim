/**
 * JetHim: редактор блока «калькулятор стоимости».
 *
 * Всё, что правит заказчик (цены, матрицы, допы, тексты), лежит в атрибуте
 * data и уходит на сервер — там же считается итог (includes/calc.php).
 * Превью в редакторе — серверный рендер (POST), живое и интерактивное:
 * view.js цепляется к [data-jc-calc] через MutationObserver.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var registerBlockType = wp.blocks.registerBlockType;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var PanelRow = wp.components.PanelRow;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var Button = wp.components.Button;
	var ServerSideRender = wp.serverSideRender.ServerSideRender;

	var DEFAULTS = ( window.JHB && window.JHB.calc ) || {};

	function clone( value ) {
		return JSON.parse( JSON.stringify( value ) );
	}

	/* ------------------------------------------------------------------
	   Мелкие контролы.
	------------------------------------------------------------------ */

	/**
	 * Поле числа: значение в инспекторе держим строкой, в атрибут пишем
	 * число — так можно стереть поле, чтобы набрать новую цену.
	 */
	function NumberField( props ) {
		var local = useState( '' + ( props.value === undefined || props.value === null ? '' : props.value ) );
		var value = local[ 0 ];
		var setValue = local[ 1 ];

		useEffect( function () {
			setValue( '' + ( props.value === undefined || props.value === null ? '' : props.value ) );
		}, [ props.value ] );

		function commit() {
			var parsed = String( value ).replace( ',', '.' );
			if ( parsed === '' ) {
				props.onChange( props.allowEmpty ? null : 0 );
				return;
			}
			var num = parseFloat( parsed );
			props.onChange( isNaN( num ) ? 0 : ( props.integer === false ? num : Math.round( num ) ) );
		}

		return el( TextControl, {
			label: props.label,
			help: props.help,
			hideLabelFromVision: !! props.hideLabel,
			type: 'number',
			value: value,
			onChange: setValue,
			onBlur: commit,
			onKeyDown: function ( e ) {
				if ( e.key === 'Enter' ) {
					commit();
				}
			}
		} );
	}

	/**
	 * Строка «подпись + цена + удалить».
	 */
	function ItemRow( props ) {
		return el( 'div', { className: 'jhb-row' },
			el( TextControl, {
				label: __( 'Название', 'jet-him-blocks' ),
				value: props.label,
				onChange: props.onLabel
			} ),
			el( NumberField, {
				label: props.priceLabel || __( 'Цена, ₽', 'jet-him-blocks' ),
				value: props.price,
				allowEmpty: props.allowEmpty,
				onChange: props.onPrice
			} ),
			props.onRemove ? el( Button, {
				variant: 'secondary',
				isSmall: true,
				className: 'jhb-row__remove',
				label: __( 'Удалить', 'jet-him-blocks' ),
				onClick: props.onRemove
			}, '×' ) : null
		);
	}

	/* ------------------------------------------------------------------
	   Панели инспектора.
	------------------------------------------------------------------ */

	function FurniturePanel( props ) {
		var cfg = props.cfg;
		var update = props.update;

		function setItem( index, key, value ) {
			var list = clone( cfg.furniture );
			list[ index ][ key ] = value;
			update( 'furniture', list );
		}

		function removeItem( index ) {
			var list = clone( cfg.furniture );
			list.splice( index, 1 );
			update( 'furniture', list );
		}

		function addItem( section ) {
			var list = clone( cfg.furniture );
			list.push( {
				id: 'custom-' + Date.now(),
				section: section,
				label: __( 'Новая позиция', 'jet-him-blocks' ),
				price: 1000,
				type: 'qty'
			} );
			update( 'furniture', list );
		}

		var sections = [];
		cfg.furniture.forEach( function ( item ) {
			if ( sections.indexOf( item.section ) === -1 ) {
				sections.push( item.section );
			}
		} );

		var children = [];
		sections.forEach( function ( section ) {
			children.push( el( 'h4', { key: 'h-' + section, className: 'jhb-subhead' }, section ) );
			cfg.furniture.forEach( function ( item, index ) {
				if ( item.section !== section ) {
					return;
				}
				children.push(
					el( ItemRow, {
						key: item.id,
						label: item.label,
						price: item.price,
						onLabel: function ( v ) { setItem( index, 'label', v ); },
						onPrice: function ( v ) { setItem( index, 'price', v ); },
						onRemove: function () { removeItem( index ); }
					} )
				);
			} );
			children.push(
				el( Button, {
					key: 'add-' + section,
					variant: 'secondary',
					isSmall: true,
					onClick: function () { addItem( section ); }
				}, __( '+ Позиция', 'jet-him-blocks' ) )
			);
		} );

		return el( PanelBody, {
			title: __( 'Мебель — цены', 'jet-him-blocks' ),
			initialOpen: false
		}, children );
	}

	/**
	 * Матрица «материал × конструкция/тип» — общая для штор и ковров.
	 */
	function MatrixPanel( props ) {
		var cfg = props.cfg;          // { materials, builds|types, prices }
		var prices = cfg.prices;
		var rowsKey = props.rowsKey;  // 'materials'
		var colsKey = props.colsKey;  // 'builds' | 'types'
		var update = props.update;
		var cols = cfg[ colsKey ];
		var rows = cfg[ rowsKey ];

		function key( rowId, colId ) {
			return rowId + '|' + colId;
		}

		function setPrice( rowId, colId, value ) {
			var next = clone( prices );
			if ( value === null ) {
				delete next[ key( rowId, colId ) ];
			} else {
				next[ key( rowId, colId ) ] = value;
			}
			update( 'prices', next );
		}

		function setLabel( which, index, value ) {
			var list = clone( cfg );
			list[ which ][ index ].label = value;
			update( null, list );
		}

		function removeRow( which, index, id ) {
			var list = clone( cfg );
			list[ which ].splice( index, 1 );
			var nextPrices = clone( prices );
			Object.keys( nextPrices ).forEach( function ( k ) {
				var parts = k.split( '|' );
				if ( parts[ 0 ] === id || parts[ 1 ] === id ) {
					delete nextPrices[ k ];
				}
			} );
			list.prices = nextPrices;
			update( null, list );
		}

		function addRow( which, prefix ) {
			var list = clone( cfg );
			list[ which ].push( {
				id: prefix + '-' + Date.now(),
				label: __( 'Новый вариант', 'jet-him-blocks' )
			} );
			update( null, list );
		}

		var children = [];

		// Шапка: подписи колонок + кнопка добавления.
		var head = el( 'div', { key: 'head', className: 'jhb-matrix jhb-matrix--head' },
			el( 'span', { className: 'jhb-matrix__corner' }, props.cornerLabel ),
			cols.map( function ( col, i ) {
				return el( 'span', { key: col.id, className: 'jhb-matrix__col' },
					el( TextControl, {
						label: __( 'Тип', 'jet-him-blocks' ),
						hideLabelFromVision: true,
						value: col.label,
						onChange: function ( v ) { setLabel( colsKey, i, v ); }
					} ),
					el( Button, {
						variant: 'secondary',
						isSmall: true,
						'aria-label': __( 'Удалить тип', 'jet-him-blocks' ),
						onClick: function () { removeRow( colsKey, i, col.id ); }
					}, '×' )
				);
			} )
		);
		children.push( head );

		rows.forEach( function ( row, rIndex ) {
			children.push(
				el( 'div', { key: row.id, className: 'jhb-matrix' },
					el( 'span', { className: 'jhb-matrix__row-name' },
						el( TextControl, {
							label: __( 'Материал', 'jet-him-blocks' ),
							hideLabelFromVision: true,
							value: row.label,
							onChange: function ( v ) { setLabel( rowsKey, rIndex, v ); }
						} ),
						el( Button, {
							variant: 'secondary',
							isSmall: true,
							'aria-label': __( 'Удалить материал', 'jet-him-blocks' ),
							onClick: function () { removeRow( rowsKey, rIndex, row.id ); }
						}, '×' )
					),
					cols.map( function ( col ) {
						var k = key( row.id, col.id );
						var exists = prices[ k ] !== undefined;
						if ( ! exists ) {
							return el( 'span', { key: k, className: 'jhb-matrix__cell' },
								el( Button, {
									variant: 'secondary',
									isSmall: true,
									title: __( 'Добавить цену для этой комбинации', 'jet-him-blocks' ),
									onClick: function () { setPrice( row.id, col.id, 0 ); }
								}, '—' )
							);
						}
						return el( 'span', { key: k, className: 'jhb-matrix__cell' },
							el( NumberField, {
								label: __( 'Цена, ₽/м²', 'jet-him-blocks' ),
								hideLabel: true,
								value: prices[ k ],
								onChange: function ( v ) { setPrice( row.id, col.id, v ); }
							} ),
							el( Button, {
								variant: 'secondary',
								isSmall: true,
								'aria-label': __( 'Убрать комбинацию', 'jet-him-blocks' ),
								onClick: function () { setPrice( row.id, col.id, null ); }
							}, '×' )
						);
					} )
				)
			);
		} );

		children.push(
			el( PanelRow, { key: 'add-row' },
				el( Button, { variant: 'secondary', onClick: function () { addRow( rowsKey, 'mat' ); } },
					props.addRowLabel )
			),
			el( PanelRow, { key: 'add-col' },
				el( Button, { variant: 'secondary', onClick: function () { addRow( colsKey, 'col' ); } },
					props.addColLabel )
			)
		);

		return el( PanelBody, {
			title: props.title,
			initialOpen: false
		}, children );
	}

	function ExtrasPanel( props ) {
		var cfg = props.cfg;
		var update = props.update;

		function setExtra( index, key, value ) {
			var list = clone( cfg );
			list[ index ][ key ] = value;
			update( null, list );
		}

		var controlOptions = [
			{ label: __( 'Счётчик 30-минутных интервалов', 'jet-him-blocks' ), value: 'counter' },
			{ label: __( 'Количество единиц', 'jet-him-blocks' ), value: 'qty' },
			{ label: __( 'Чекбокс без цены', 'jet-him-blocks' ), value: 'check' }
		];

		var children = cfg.map( function ( extra, index ) {
			return el( 'div', { key: extra.id, className: 'jhb-row jhb-row--extra' },
				el( TextControl, {
					label: __( 'Название', 'jet-him-blocks' ),
					value: extra.label,
					onChange: function ( v ) { setExtra( index, 'label', v ); }
				} ),
				el( NumberField, {
					label: __( 'Цена, ₽ (пусто — без цены)', 'jet-him-blocks' ),
					value: extra.price,
					allowEmpty: true,
					onChange: function ( v ) { setExtra( index, 'price', v ); }
				} ),
				el( SelectControl, {
					label: __( 'Как считаем', 'jet-him-blocks' ),
					value: extra.control,
					options: controlOptions,
					onChange: function ( v ) { setExtra( index, 'control', v ); }
				} ),
				el( Button, {
					variant: 'secondary',
					isSmall: true,
					className: 'jhb-row__remove',
					'aria-label': __( 'Удалить доп', 'jet-him-blocks' ),
					onClick: function () {
						var list = clone( cfg );
						list.splice( index, 1 );
						update( null, list );
					}
				}, '×' )
			);
		} );

		children.push(
			el( Button, {
				key: 'add',
				variant: 'secondary',
				onClick: function () {
					var list = clone( cfg );
					list.push( {
						id: 'extra-' + Date.now(),
						label: __( 'Новая услуга', 'jet-him-blocks' ),
						price: 500,
						control: 'qty'
					} );
					update( null, list );
				}
			}, __( '+ Доп-услуга', 'jet-him-blocks' ) )
		);

		return el( PanelBody, {
			title: __( 'Доп-услуги', 'jet-him-blocks' ),
			initialOpen: false
		}, children );
	}

	/* ------------------------------------------------------------------
	   Блок.
	------------------------------------------------------------------ */
	function CalcEdit( props ) {
		var cfg = props.attributes.data;
		var setAttributes = props.setAttributes;

		useEffect( function () {
			if ( ! cfg || ! Object.keys( cfg ).length ) {
				setAttributes( { data: clone( DEFAULTS ) } );
			}
		}, [] );

		if ( ! cfg || ! Object.keys( cfg ).length ) {
			cfg = DEFAULTS;
		}

		function update( key, value ) {
			var next = clone( cfg );
			if ( key === null ) {
				next = value;
			} else {
				next[ key ] = value;
			}
			setAttributes( { data: next } );
		}

		var blockProps = useBlockProps( { className: 'jhb-calc-editor' } );

		return el( Fragment, null,
			el( InspectorControls, null,

				el( FurniturePanel, { cfg: cfg, update: update } ),

				el( MatrixPanel, {
					title: __( 'Шторы — цена за м²', 'jet-him-blocks' ),
					cfg: cfg.curtains,
					rowsKey: 'materials',
					colsKey: 'builds',
					cornerLabel: __( 'Материал / конструкция', 'jet-him-blocks' ),
					addRowLabel: __( '+ Материал', 'jet-him-blocks' ),
					addColLabel: __( '+ Конструкция', 'jet-him-blocks' ),
					update: function ( key, value ) {
						var next = clone( cfg );
						next.curtains = key === null ? value : Object.assign( {}, next.curtains, ( function ( o ) { o[ key ] = value; return o; } )( {} ) );
						setAttributes( { data: next } );
					}
				} ),

				el( MatrixPanel, {
					title: __( 'Ковры — матрица за м²', 'jet-him-blocks' ),
					cfg: cfg.carpets,
					rowsKey: 'materials',
					colsKey: 'types',
					cornerLabel: __( 'Материал / тип', 'jet-him-blocks' ),
					addRowLabel: __( '+ Материал', 'jet-him-blocks' ),
					addColLabel: __( '+ Тип ковра', 'jet-him-blocks' ),
					update: function ( key, value ) {
						var next = clone( cfg );
						next.carpets = key === null ? value : Object.assign( {}, next.carpets, ( function ( o ) { o[ key ] = value; return o; } )( {} ) );
						setAttributes( { data: next } );
					}
				} ),

				el( ExtrasPanel, { cfg: cfg.extras, update: function ( key, value ) {
					var next = clone( cfg );
					next.extras = key === null ? value : Object.assign( {}, next.extras, ( function ( o ) { o[ key ] = value; return o; } )( {} ) );
					setAttributes( { data: next } );
				} } ),

				el( PanelBody, { title: __( 'Выезд и скидка', 'jet-him-blocks' ), initialOpen: false },
					el( NumberField, {
						label: __( 'Бесплатный выезд, км от МКАД', 'jet-him-blocks' ),
						value: cfg.trip.free_km,
						onChange: function ( v ) {
							var next = clone( cfg );
							next.trip.free_km = v;
							update( null, next );
						}
					} ),
					el( NumberField, {
						label: __( 'Цена за км сверх лимита, ₽', 'jet-him-blocks' ),
						value: cfg.trip.per_km,
						onChange: function ( v ) {
							var next = clone( cfg );
							next.trip.per_km = v;
							update( null, next );
						}
					} ),
					el( NumberField, {
						label: __( 'Скидка постоянному клиенту, %', 'jet-him-blocks' ),
						value: cfg.discount.percent,
						onChange: function ( v ) {
							var next = clone( cfg );
							next.discount.percent = v;
							update( null, next );
						}
					} )
				),

				el( PanelBody, { title: __( 'Тексты под итогом', 'jet-him-blocks' ), initialOpen: false },
					el( TextareaControl, {
						label: __( 'Обязательная сноска (прайс, раздел F)', 'jet-him-blocks' ),
						rows: 4,
						value: cfg.texts.note,
						onChange: function ( v ) {
							var next = clone( cfg );
							next.texts.note = v;
							update( null, next );
						}
					} ),
					el( TextareaControl, {
						label: __( 'Про сроки выезда', 'jet-him-blocks' ),
						rows: 2,
						value: cfg.texts.lead_time,
						onChange: function ( v ) {
							var next = clone( cfg );
							next.texts.lead_time = v;
							update( null, next );
						}
					} )
				)
			),

			el( 'div', blockProps,
				el( ServerSideRender, {
					block: 'jc/calc',
					attributes: { data: cfg },
					httpMethod: 'POST'
				} )
			)
		);
	}

	registerBlockType( 'jc/calc', {
		title: __( 'JetHim: калькулятор стоимости', 'jet-him-blocks' ),
		description: __( 'Калькулятор по прайсу: мебель, шторы, ковры, допы, выезд и скидка.', 'jet-him-blocks' ),
		category: 'jet-him',
		icon: 'calculator',
		keywords: [ 'калькулятор', 'расчёт', 'цена', 'прайс', 'стоимость' ],
		attributes: {
			data: { type: 'object', default: {} }
		},
		supports: {
			html: false,
			align: [ 'wide' ]
		},
		edit: CalcEdit,
		save: function () {
			return null;
		}
	} );
} )( window.wp );
