<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

class LspciUpload extends Upload {
    public function __construct() {
        parent::__construct('lspci');
        // 3 kB should be a reasonable limit for lspci output which contains
        // hexadecimal values only
        $this->checkMaxSize(3e3);
    }

    public function validateUpload() {
        $fp = $this->getFilePointer();
        $lspci_line = '/^' .
            '[0-9a-f]{2}:[0-9a-f]{2}.\d' . // Bus ID
            ' [0-9a-f]{4}:' . // device class
            ' [0-9a-f]{4}:[0-9a-f]{4}' . // vendorID:deviceID
            '( \(rev [0-9a-f]{2}\))?' . // revision
            '$/';
        $line_count = 0;

        // a line is 33 bytes including LF, 63 bytes is more than enough
        while ($line = fgets($fp, 64)) {
            $line_count++;
            if (!preg_match($lspci_line, rtrim($line))) {
                fclose($fp);
                throw new UploadException(
                    "Line $line_count in lspci file is malformed"
                );
            }
        }
        fclose($fp);
        if (!$line_count) {
            throw new UploadException(
                "Empty lspci file received"
            );
        }
    }
}
?>
