<?php
/**
 * App Configuration Class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
class Config_Api extends Config {

	/**
	 * Config array
	 *
	 * @var array
	 * @access private
	 */
	protected $config_data = [

		/**
		 * Hostnames
		 */
		'hostnames'		=>	['api.*'],

		/**
		 * Default language. If no language is requested
		 */
		'default_language'	=>	'en',

		/**
		 * Routes
		 */
		'routes' => []

	];

}
