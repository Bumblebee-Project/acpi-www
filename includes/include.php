<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

define('LIBS', __DIR__ . '/lib');
define('WEBROOT', dirname(__DIR__) . '/www');
define('SUBMISSIONS_DIR', WEBROOT . '/submissions');
define('TEMPLATES_DIR', __DIR__ . '/templates');

define('WWW_ROOT', 'http://localhost/acpi-www/www');

define('SUBMISSION_CLAIM_URL', WWW_ROOT . '/claim.php?key=');
define('SUBMISSION_URL', WWW_ROOT . '/submissions/');
define('UPLOAD_HOME_URL', WWW_ROOT . '/upload.html');

function autoload_libs($className) {
    $file = LIBS . DIRECTORY_SEPARATOR . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('autoload_libs');

require_once __DIR__ . '/secrets.php';
?>