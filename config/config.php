<?php
return [

	/**
	 * GENERAL CONFIGURATION
	 *
	 * These configuration items can be overwritten by application specific configuration.
	 * However they are probably the same for all applications.
	 */

	/**
	 * Setting debug to true will enable debug output and error display.
	 * Error email is not affected.
	 */
	'debug' => true,
	'errors_from' => 'errors@example.com',
	'errors_to' => 'errors@example.com',

	/**
	 * The default language that will be shown to the user if it can not be guessed
	 */
	'default_language' => 'en',

	/**
	 * Known VAT regexes
	 */
	'regexp_vat' => [
			'AT' => '/^U[0-9]{8}$/',
			'BE' => '/^[0-9]{10}$/',
			'BG' => '/^[0-9]{9,10}$/',
			'CY' => '/^[0-9a-zA-Z]{9}$/',
			'CZ' => '/^[0-9]{8,10}$/',
			'DE' => '/^[0-9]{9}$/',
			'DK' => '/^[0-9]{8}$/',
			'EE' => '/^[0-9]{9}$/',
			'EL' => '/^[0-9]{9}$/',
			'ES' => '/^[0-9a-zA-Z]{9}$/',
			'FI' => '/^[0-9]{8}$/',
			'FR' => '/^[a-zA-Z0-9]{2}[0-9]{9}$/',
			'HU' => '/^[0-9]{8}$/',
			'IT' => '/^[0-9]{11}$/',
			'LT' => '/^[0-9]{9,12}$/',
			'LU' => '/^[0-9]{8}$/',
			'LV' => '/^[0-9]{11}$/',
			'MT' => '/^[0-9]{8}$/',
			'NL' => '/^[a-zA-Z0-9]{12}$/',
			'PL' => '/^[0-9]{10}$/',
			'PT' => '/^[0-9]{9}$/',
			'RO' => '/^[0-9]{2,10}$/',
			'SE' => '/^[0-9]{12}$/',
			'SI' => '/^[0-9]{8}$/',
			'SK' => '/^[0-9]{10}$/',
	],

	/**
	 * VAT examples
	 */
	'example_vat' => [
			'AT' => 'U12345678',
			'BE' => '0123456789',
			'BG' => '123456789',
			'CY' => '12345678L',
			'CZ' => '123456789',
			'DE' => '123456789',
			'DK' => '12345678',
			'EE' => '123456789',
			'EL' => '123456789',
			'ES' => 'X9999999X',
			'FI' => '12345678',
			'FR' => '12123456789',
			'GB' => '123123412',
			'HU' => '12345678',
			'IE' => '1S23456L',
			'IT' => '12345678901',
			'LT' => '123456789',
			'LU' => '12345678',
			'LV' => '12345678901',
			'MT' => '12345678',
			'NL' => '123412123B12',
			'PL' => '1234567890',
			'PT' => '123456789',
			'RO' => '1234567890',
			'SE' => '123456789012',
			'SI' => '12345678',
			'SK' => '1234567890',
	],

	'transaction_pid' => '/home/tickoweb/transaction.pid',
	'transaction_monitor' => '/home/tickoweb/transaction.status',
];
