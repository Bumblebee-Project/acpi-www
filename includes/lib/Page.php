<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

class Page {
    protected $template_vars = array();
    protected $name;
    protected $type;

    public function __construct($name) {
        $this->name = $name;
        $this->type = self::detectRequestType();
    }

    protected static function detectRequestType() {
        // assume HTML by default
        $type = 'html';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
            if (strpos($ua, 'curl/') === 0) {
                $type = 'text';
            }
        }
        return $type;
    }

    /**
     * Sets a value for a template variable
     * @param string $varName The template variable to be set
     * @param string $value The value to be set fir the template variable
     */
    public function setVar($varName, $value) {
        $this->template_vars[$varName] = $value;
    }

    /**
     * Retrieves a template variable
     * @param string $varName The name of the template variable
     * @param string $defaultValue The value to be returned if no template
     * variable is found. If null, an error will be shown if the variable is
     * not found
     * @return string The value of the template variable
     */
    public function getVar($varName, $defaultValue=null) {
        if ($this->hasVar($varName)) {
            return $this->template_vars[$varName];
        }
        if ($defaultValue !== null) {
            return $defaultValue;
        }
        return "[ Template var $varName not found ]";
    }

    /**
     * Checks whther a template variable exists or not
     * @param string $varName The name of the template variable
     * @return boolean True if the template var exists, false otherwise
     */
    public function hasVar($varName) {
        return isset($this->template_vars[$varName]);
    }

    /**
     * Retrieves a template variable, escaping any special HTML characters
     * @param string $varName The name of the template variable
     * @param string $defaultValue The value to be returned if no template
     * variable is found. If null, an error will be shown if the variable is
     * not found
     * @return string The value of the template variable
     */
    public function getVarAsHtml($varName, $defaultValue=null) {
        return htmlspecialchars($this->getVar($varName), $defaultValue);
    }

    public function display() {
        $this->loadTemplate('base');
    }

    /**
     * Loads a template (as PHP). The TEMPLATES_DIR will be searched for a file
     * matching $tplName with the extension html or txt depending on the
     * requested type. If both are not found, the php extension is tried.
     * Otherwise, an optional error message is displayed
     * @param string $tplName the name of the template
     * @param boolean $silentFail Whether an error should be displayed if the
     * template cannot be found
     */
    protected function loadTemplate($tplName, $silentFail=false) {
        // Warning: do not set $tplName to untrusted user input!
        $ext = $this->type == 'text' ? 'txt' : 'html';
        $fileName = TEMPLATES_DIR . "/$tplName";
        if (file_exists("$fileName.$ext")) {
            include "$fileName.$ext";
        } else if (file_exists("$fileName.php")) {
            include "$file.php";
        } else if (!$silentFail) {
            echo "[ Template $tplName not found ]";
        }
    }

    
    protected function loadMain() {
        $this->loadTemplate($this->name);
    }
}
?>