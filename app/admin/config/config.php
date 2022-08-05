<?php
/**
 * App Configuration Class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
return [

	/**
	 * Hostnames
	 */
	'hostnames'		=>	['*'],

	/**
	 * Default language. If no language is requested
	 */
	'default_language'	=>	'en',

	/**
	 * Routes
	 */
	'routes' => [

		'web_module_financial_account_transaction' => [
			'$language/financial/account/$bank_account_id/transaction',
			'$language/financial/account/$bank_account_id/transaction/$id',
			'$language/financial/account/$bank_account_id/transaction/$id/$action',
		],

	]

];

