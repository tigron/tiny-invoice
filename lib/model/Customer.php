<?php
/**
 * User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/model/Country.php';
require_once LIB_PATH . '/model/Language.php';

class Customer {
	use Model, Get, Save, Delete, Page;
}
