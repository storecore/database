<?php
/**
 * StoreCore Framework Bootloader
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright (c) 2015 StoreCore
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.1.0
 */

// Load, instantiate, and register the StoreCore autoloader
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Autoloader.php';
$loader = new \StoreCore\Autoloader();
$loader->register();

// Link namespaces to directories
$loader->addNamespace('Psr\Log', __DIR__ . DIRECTORY_SEPARATOR . 'Psr/Log');
$loader->addNamespace('StoreCore\FileSystem', __DIR__ . DIRECTORY_SEPARATOR . 'FileSystem');

// Load core classes
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Registry.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'AbstractModel.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'AbstractController.php';
