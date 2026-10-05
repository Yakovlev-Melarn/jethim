/**
 * JetHim: редактор блока «контакты / CTA».
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender.ServerSideRender;

	function CtaEdit( props ) {
		var attrs = props.attributes;
		var setAttributes = props.setAttributes;

		var blockProps = useBlockProps( { className: 'jhb-cta-editor' } );

		return el( Fragment, null,
			el( InspectorControls, null,
				el( PanelBody, { title: __( 'Текст', 'jet-him-blocks' ), initialOpen: true },
					el( TextControl, {
						label: __( 'Заголовок', 'jet-him-blocks' ),
						value: attrs.title,
						onChange: function ( v ) { setAttributes( { title: v } ); }
					} ),
					el( TextareaControl, {
						label: __( 'Текст', 'jet-him-blocks' ),
						rows: 3,
						value: attrs.text,
						onChange: function ( v ) { setAttributes( { text: v } ); }
					} ),
					el( TextControl, {
						label: __( 'Надпись на кнопке', 'jet-him-blocks' ),
						value: attrs.buttonLabel,
						onChange: function ( v ) { setAttributes( { buttonLabel: v } ); }
					} )
				),
				el( PanelBody, { title: __( 'Контакты', 'jet-him-blocks' ), initialOpen: false },
					el( ToggleControl, {
						label: __( 'Показывать телефон и кнопку звонка', 'jet-him-blocks' ),
						checked: attrs.showPhone,
						onChange: function ( v ) { setAttributes( { showPhone: v } ); }
					} ),
					el( ToggleControl, {
						label: __( 'Показывать Telegram и ВКонтакте', 'jet-him-blocks' ),
						checked: attrs.showSocial,
						onChange: function ( v ) { setAttributes( { showSocial: v } ); }
					} ),
					el( ToggleControl, {
						label: __( 'Показывать адрес', 'jet-him-blocks' ),
						checked: attrs.showAddress,
						onChange: function ( v ) { setAttributes( { showAddress: v } ); }
					} ),
					el( 'p', { className: 'jhb-hint' },
						__( 'Сами значения — в functions.php дочерней темы, блок подставляет их автоматически.', 'jet-him-blocks' ) )
				)
			),
			el( 'div', blockProps,
				el( ServerSideRender, {
					block: 'jc/cta',
					attributes: attrs,
					httpMethod: 'POST'
				} )
			)
		);
	}

	registerBlockType( 'jc/cta', {
		title: __( 'JetHim: блок контактов / CTA', 'jet-him-blocks' ),
		description: __( 'Призыв к действию с телефоном и мессенджерами.', 'jet-him-blocks' ),
		category: 'jet-him',
		icon: 'phone',
		keywords: [ 'контакты', 'звонок', 'кнопка', 'телефон' ],
		attributes: {
			title: { type: 'string', default: 'Рассчитаем стоимость по фото' },
			text: { type: 'string', default: 'Пришлите фото мебели — оценим цену и подтвердим время выезда. Расчёт бесплатный, ни к чему не обязывает.' },
			buttonLabel: { type: 'string', default: 'Рассчитать стоимость' },
			showPhone: { type: 'boolean', default: true },
			showSocial: { type: 'boolean', default: true },
			showAddress: { type: 'boolean', default: false }
		},
		supports: {
			html: false,
			align: [ 'wide' ]
		},
		edit: CtaEdit,
		save: function () {
			return null;
		}
	} );
} )( window.wp );
