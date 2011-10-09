<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

/**
 * Sorry for the ugliness ahead, I did not want to spend more time on just
 * maintaining a simple DB connection ~Lekensteyn
 */
class Database extends mysqli {
    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct() {
        global $db_host, $db_user, $db_pass, $db_name;
        parent::__construct($db_host, $db_user, $db_pass, $db_name);
        if ($this->connect_error) {
            throw new DatabaseException(
                'A database connection could not be established.'
            );
        }
    }

    public function prepare($query) {
        $stmt = parent::prepare($query);
        if (!$stmt) {
            throw new DatabaseException(
                'A database query could not be constructed.'
            );
        }
        return $stmt;
    }
}
class DatabaseException extends Exception {}
?>