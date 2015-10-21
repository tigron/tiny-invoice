<?php
/**
 * Initialize the application
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */
ini_set('display_errors', '1');

require_once 'config/global.php';

\Skeleton\Core\Web\Handler::Run();
