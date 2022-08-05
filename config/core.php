<?php
/**
 * Skeleton configuration
 */
return [
	/**
	 * Core
	 */
	'application_dir' => dirname(__FILE__) . '/../app/',
	'asset_paths' => [
		dirname(__FILE__) . '/../lib/external/assets/',
	],
	'tmp_dir' => dirname(__FILE__) . '/../tmp/',
	'lib_dir' => dirname(__FILE__) . '/../lib/',

	/**
	 * Security
	 */
	'csrf_enabled' => true,

	/**
	 * Pager
	 */
	'items_per_page' => 20,

	/**
	 * Default language. Used for sending mails when the language is not given
	 */
	'default_language' => 'en',
];

