<?php

declare(strict_types=1);

/**
 * Handler file
 *
 * Calls the Handler class
 */

require_once 'lib/base/Bootstrap.php';
Bootstrap::boot();
\Skeleton\Core\Http\Handler::Run();