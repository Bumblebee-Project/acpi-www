<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

class DmidecodeUpload extends Upload {
    static $valid_keywords = array(
        "baseboard-manufacturer",
        "baseboard-product-name",
        "baseboard-version",
        "system-manufacturer",
        "system-product-name",
        "system-version"
    );
    const MAX_VALUE_LENGTH = 32;

    public function __construct() {
        parent::__construct('dmidecode');
        // 6 fields: 24 for key, colon and space; 32 for value, 2 for CRLF
        $this->checkMaxSize(6 * (24 + 32 + 2));
    }

    public function validateUpload() {
        $fp = $this->getFilePointer();
        while ($line = fgets($fp)) {
            if (strpos($line, ":") !== false) {
                list($keyword, $val) = explode(":", $line, 2);
                $keyword = trim($keyword);
                $val = trim($val);
            } else {
                $keyword = '';
                $val = '';
            }
            if (!in_array($keyword, self::$valid_keywords) ||
                strlen($val) > self::MAX_VALUE_LENGTH) {
                throw new UploadException(
                    "Line $line_count in dmidecode file is malformed"
                );
            }
        }
        fclose($fp);
    }
}
?>
