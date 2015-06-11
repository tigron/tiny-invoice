<?php
/**
 * App Configuration Class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
class Config_Admin extends Config {

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
		'hostnames'		=>	['application.example.tld'],

		/**
		 * Default language. If no language is requested
		 */
		'default_language'	=>	'en',

		/**
		 * Routes
		 */
		'routes' => [

			'web_module_index' => [
				
				'$language/default/route/to/index',
				'$language/default/route/to/index/$action',
				'$language/default/route/to/index/$action/$id',

				'$language[en]/test/routing/engine',
				'$language[en]/test/routing/engine/$action',
				'$language[en]/test/routing/engine/$action/$id'
			
			],

			'web_module_demo' => [

				'$language/default/route/to/demo',
				'$language/default/route/to/demo/$action',
				
				'$language[nl]/standaard/route/naar/demo',
				'$language[nl]/standaard/route/naar/demo/$action'
			
			]

		]

	];

}