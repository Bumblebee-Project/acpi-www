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
        $dsdt_found = false;

        // for efficiency, we're not validating the file contents which should
        // be performed by acpixtract and iasl. Rather, we just check if the
        // required DSDT line is found and leave that as is
        $fp = $this->getFilePointer();

        // a line should not be longer than 128 chars, in fact, a line is only
        // 74 bytes (counting a LF)
        while ($line = fread($fp, 128)) {
            if (substr($line, 0, 4) == 'DSDT') {
                $dsdt_found = true;
                break;
            }
        }
        fclose($fp);
        if (!$dsdt_found) {
            throw new UploadException("Table DSDT was not found");
        }
    }
}
?>
