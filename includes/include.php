<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

define('LIBS', __DIR__ . '/lib');
function autoload_libs($className) {
    $file = LIBS . DIRECTORY_SEPARATOR . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('autoload_libs');

require_once __DIR__ . '/secrets.php';
?>