<?php
/**
 * Initialize the application
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once 'config/global.php';
require_once LIB_PATH . '/base/Web/Handler.php';

Web_Handler::Run();