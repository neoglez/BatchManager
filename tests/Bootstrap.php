<?php
/**
* BatchManager Module (@todo yansel write link in here)
*
* @link @todo yansel write link in here
* @license @todo yansel write license in here
* @author Yansel González Tejeda <neoglez@gmail.com>
*/

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = include __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    $loader = include __DIR__ . '/../../../autoload.php';
} else {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

/* var $loader \Composer\Autoload\ClassLoader */
$loader->add('BatchManagerTest\\', __DIR__);

$configFiles = array(__DIR__ . '/TestConfiguration.php', __DIR__ . '/TestConfiguration.php.dist');

foreach ($configFiles as $configFile) {
    if (file_exists($configFile)) {
        $config = require $configFile;

        break;
    }
}

//ServiceManagerFactory::setApplicationConfig($config);
//unset($files, $file, $loader, $configFiles, $configFile, $config);