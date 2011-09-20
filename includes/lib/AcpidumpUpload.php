<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

class AcpidumpUpload extends Upload {
    public function __construct() {
        parent::__construct('acpidump');
        $this->checkMaxSize(1e6);
    }

    public function validateUpload() {
        
    }
}
?>
