<?php
/**
 * Route configuration class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */
class Config_Routes {

	/**
	 * Routes array
	 *
	 * @var $routes
	 * @access public
	 */
	public static $routes = array(

		/**
		 * This is a demo route
		*/
		// Application
		'admin' => array(
			// Module
			'index' => array(
				// Routes with language as key, use default as key for default
				'routes' => array(
					'default' => '/default/route/to/index',
					'en' => '/test/routing/engine',
				),
				// Variables that match the route, if no variables should match as well, use ''
				'variables' => array(
					'',
					'$action',
					'$action/$id',
				),
			),

			'administrative/customer' => array(
				'routes' => array(
					'default' => '/administrative/customer',
					'nl' => '/administratief/klant',
				),
				'variables' => array(
					'',
					'$action',
					'$action/$id',
				)
			),
			'administrative/invoice' => array(
				'routes' => array(
					'default' => '/administrative/invoice',
					'nl' => '/administratief/factuur',
				),
				'variables' => array(
					'',
					'$action',
					'$action/$id',
				)
			),
			'user' => array(
				'routes' => array(
					'default' => '/user',
					'nl' => '/gebruiker',
				),
				'variables' => array(
					'',
					'$action',
					'$action/$id',
				)
			),
			'cron' => array(
				'routes' => array(
					'default' => '/cron',
				),
				'variables' => array(
					'',
					'$action'
				)
			)
		),
	);
}
