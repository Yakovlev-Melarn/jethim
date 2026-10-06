<?php
/**
 * Plugin Name: JetHim — уведомления в Telegram
 * Description: Отправка заявок из Fluent Forms в Telegram (хук fluentform/submission_inserted). Токен и chat_id хранятся в опциях БД (jc_tg_token / jc_tg_chat_id).
 *
 * Этап 5. Настройка:
 *   wp option update jc_tg_token <токен>          — уже сделано e5_setup.php
 *   боту нужно отправить /start                   — chat_id подхватится сам
 *   wp option update jc_tg_chat_id <id>           — или указать вручную
 *
 * CLI:
 *   wp jc-tg status   — токен/chat_id/getUpdates
 *   wp jc-tg discover — поиск chat_id в getUpdates
 *   wp jc-tg send "Текст" — тестовое сообщение
 */

defined( 'ABSPATH' ) || exit;

/**
 * Токен бота (опция, не в git).
 */
function jc_tg_token() {
	return (string) get_option( 'jc_tg_token', '' );
}

/**
 * ID чата (опция; пусто → авто-поиск в getUpdates).
 */
function jc_tg_chat_id() {
	return (string) get_option( 'jc_tg_chat_id', '' );
}

/**
 * Последние апдейты бота (для поиска chat_id).
 */
function jc_tg_get_updates() {
	$token = jc_tg_token();
	if ( ! $token ) {
		return new WP_Error( 'jc_tg_no_token', 'Не задан токен (опция jc_tg_token)' );
	}

	$res = wp_remote_get(
		'https://api.telegram.org/bot' . $token . '/getUpdates?timeout=0',
		array( 'timeout' => 15 )
	);

	if ( is_wp_error( $res ) ) {
		return $res;
	}

	$body = json_decode( wp_remote_retrieve_body( $res ), true );

	return is_array( $body ) ? $body : new WP_Error( 'jc_tg_bad_response', 'Пустой ответ Telegram' );
}

/**
 * Найти chat_id в getUpdates (первый чат, куда писал человек).
 */
function jc_tg_discover_chat_id() {
	$data = jc_tg_get_updates();
	if ( is_wp_error( $data ) || empty( $data['ok'] ) || empty( $data['result'] ) ) {
		return '';
	}

	foreach ( $data['result'] as $update ) {
		foreach ( array( 'message', 'edited_message', 'my_chat_member', 'channel_post' ) as $type ) {
			if ( ! empty( $update[ $type ]['chat']['id'] ) ) {
				return (string) $update[ $type ]['chat']['id'];
			}
		}
	}

	return '';
}

/**
 * Отправить текст в чат. Возвращает true или WP_Error.
 *
 * @param string $text Текст (HTML parse_mode).
 */
function jc_tg_send( $text ) {
	$token = jc_tg_token();
	if ( ! $token ) {
		return new WP_Error( 'jc_tg_no_token', 'Не задан токен (опция jc_tg_token)' );
	}

	$chat_id = jc_tg_chat_id();
	if ( ! $chat_id ) {
		$chat_id = jc_tg_discover_chat_id();
		if ( $chat_id ) {
			update_option( 'jc_tg_chat_id', $chat_id );
		}
	}

	if ( ! $chat_id ) {
		return new WP_Error( 'jc_tg_no_chat', 'Нет chat_id — отправьте /start боту или: wp option update jc_tg_chat_id <id>' );
	}

	$res = wp_remote_post(
		'https://api.telegram.org/bot' . $token . '/sendMessage',
		array(
			'timeout' => 15,
			'body'    => array(
				'chat_id'                  => $chat_id,
				'text'                     => $text,
				'parse_mode'               => 'HTML',
				'disable_web_page_preview' => true,
			),
		)
	);

	if ( is_wp_error( $res ) ) {
		return $res;
	}

	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $body['ok'] ) ) {
		return new WP_Error( 'jc_tg_send_failed', 'Telegram ответил ошибкой: ' . wp_json_encode( $body ) );
	}

	return true;
}

/**
 * Карта «имя поля → подпись» из form_fields формы.
 */
function jc_tg_field_labels( $form ) {
	$labels = array();
	$decoded = json_decode( (string) $form->form_fields, true );
	if ( empty( $decoded['fields'] ) || ! is_array( $decoded['fields'] ) ) {
		return $labels;
	}

	$walk = function ( $fields ) use ( &$walk, &$labels ) {
		foreach ( $fields as $field ) {
			if ( empty( $field['element'] ) ) {
				continue;
			}
			$name = isset( $field['attributes']['name'] ) ? $field['attributes']['name'] : '';
			if ( $name && isset( $field['settings']['label'] ) && '' !== $field['settings']['label'] ) {
				$labels[ $name ] = $field['settings']['label'];
			}
			if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
				$walk( $field['fields'] );
			}
			if ( ! empty( $field['columns'] ) && is_array( $field['columns'] ) ) {
				foreach ( $field['columns'] as $column ) {
					if ( ! empty( $column['fields'] ) ) {
						$walk( $column['fields'] );
					}
				}
			}
		}
	};

	$walk( $decoded['fields'] );

	return $labels;
}

/**
 * Значение поля → строка для сообщения.
 */
function jc_tg_value_string( $value ) {
	if ( is_array( $value ) ) {
		$value = implode( ', ', array_filter( array_map( 'strval', $value ) ) );
	}
	if ( is_bool( $value ) ) {
		return $value ? 'да' : 'нет';
	}

	return trim( (string) $value );
}

/* --------------------------------------------------------------------
 * Отправка после каждой записи заявки
 * ------------------------------------------------------------------ */
add_action(
	'fluentform/submission_inserted',
	function ( $insert_id, $formData, $form ) {
		$labels = jc_tg_field_labels( $form );
		if ( ! $labels ) {
			return;
		}

		$lines   = array();
		$lines[] = "\xF0\x9F\x93\x8B <b>Новая заявка — " . esc_html( $form->title ) . '</b>';

		foreach ( $labels as $name => $label ) {
			if ( ! isset( $formData[ $name ] ) ) {
				continue;
			}
			$value = jc_tg_value_string( $formData[ $name ] );
			if ( '' === $value ) {
				continue;
			}
			$lines[] = '<b>' . esc_html( $label ) . ':</b> ' . esc_html( $value );
		}

		$lines[] = '<b>Страница:</b> ' . esc_url( wp_get_referer() ? wp_get_referer() : home_url( '/' ) );
		$lines[] = '<b>Заявка №</b>' . (int) $insert_id;
		$lines[] = '<i>' . current_time( 'd.m.Y H:i' ) . '</i>';

		$result = jc_tg_send( implode( "\n", $lines ) );
		if ( is_wp_error( $result ) ) {
			// Не ломаем отправку формы — просто пишем в лог.
			error_log( '[jc-telegram] ' . $result->get_error_message() );
		}
	},
	10,
	3
);

/* --------------------------------------------------------------------
 * CLI: wp jc-tg status|discover|send
 * ------------------------------------------------------------------ */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	class Jc_Tg_Command {

		/**
		 * Статус настроек.
		 */
		public function status() {
			WP_CLI::log( 'token: ' . ( jc_tg_token() ? 'задан (' . substr( jc_tg_token(), 0, 10 ) . '…)' : 'НЕ ЗАДАН' ) );
			WP_CLI::log( 'chat_id: ' . ( jc_tg_chat_id() ? jc_tg_chat_id() : 'пусто (нужен /start боту)' ) );
		}

		/**
		 * Поиск chat_id в getUpdates.
		 *
		 * ## OPTIONS
		 * [--save]
		 * : Сохранить найденный chat_id в опцию.
		 */
		public function discover( $args = array(), $assoc_args = array() ) {
			$chat_id = jc_tg_discover_chat_id();
			if ( ! $chat_id ) {
				WP_CLI::error( 'chat_id не найден — отправьте боту /start и повторите' );
			}
			WP_CLI::log( 'chat_id: ' . $chat_id );
			if ( ! empty( $assoc_args['save'] ) ) {
				update_option( 'jc_tg_chat_id', $chat_id );
				WP_CLI::success( 'Сохранено в опцию jc_tg_chat_id' );
			}
		}

		/**
		 * Тестовое сообщение.
		 *
		 * ## OPTIONS
		 * <text>
		 * : Текст сообщения.
		 */
		public function send( $args ) {
			$result = jc_tg_send( (string) $args[0] );
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( $result->get_error_message() );
			}
			WP_CLI::success( 'Отправлено' );
		}
	}

	WP_CLI::add_command( 'jc-tg', 'Jc_Tg_Command' );
}
