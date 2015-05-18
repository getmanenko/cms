<?php
/**
 * SamsonCMS Init script
 * @author Vitaly Iegorov <egorov@samsonos.com>
 */

/** Set default locale */
//define('DEFAULT_LOCALE', 'ru');

/** Require composer autoloader */
require_once('../vendor/autoload.php');

/** Automatic parent web-application configuration read */
if (file_exists('../../../app/config')) {
    /** Special constant to disable local ActiveRecord configuration */
    define('EXTERNAL_CONFIG', true);
    // Signal core configure event with pathes to parent application configuration folder instead of local one
    \samsonphp\event\Event::signal('core.configure', array('../../../'.__SAMSON_CONFIG_PATH, __SAMSON_PUBLIC_PATH.__SAMSON_BASE__));
}

// Set supported locales
setlocales('en', 'ua', 'ru');

// Start SamsonPHP application
s()
    ->environment(getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : null)
    ->composer()
    ->subscribe('core.routing', array(url(),'router'));

/** Automatic external SamsonCMS Application searching  */
if (file_exists('../../../src/')) {
    // Get resource map to find all modules in src folder
    foreach(\samson\core\ResourceMap::get('../../../src/')->modules as $module) {
        // We are only interested in SamsonCMS application ancestors
        if (in_array('samson\cms\App', class_parents($module[2])) !== false) {
            // Remove possible '/src/' path from module path
            if (($pos = strripos($module[1], '/src/')) !== false) {
                $module[1] = substr($module[1], 0, $pos);
            }
            // Load
            s()->load($module[1]);
        }
    }
}

require 'old.php';

s()->start('template');
