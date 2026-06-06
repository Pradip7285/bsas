<?php

use CodeIgniter\Boot;
use Config\Paths;

$minPhpVersion = '8.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo sprintf('Your PHP version must be %s or higher. Current: %s', $minPhpVersion, PHP_VERSION);
    exit(1);
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

$pathsConfig = FCPATH . '../app/Config/Paths.php';

// Support shared-hosting deployments where the public directory is copied
// into public_html but the full app lives elsewhere, e.g. /home/user/appname.
if (! is_file($pathsConfig)) {
    $sharedHostingPathsConfig = dirname(FCPATH, 2) . DIRECTORY_SEPARATOR . 'bsas' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Paths.php';

    if (is_file($sharedHostingPathsConfig)) {
        $pathsConfig = $sharedHostingPathsConfig;
    }
}

require $pathsConfig;

$paths = new Paths();

require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
