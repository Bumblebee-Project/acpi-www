<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

class Upload {
    public static $error_messages = array(
        1 => 'The uploaded file exceeds the maximum upload size set',
        'The uploaded file exceeds the maximum upload size as defined in the form',
        'The uploaded file was only partially uploaded',
        'No file was uploaded',
        6 => 'Missing a temporary folder',
        'Failed to write file to disk',
        'A PHP extension stopped the file upload'
    );

    protected $name;
    protected $size;
    protected $tmp_name;
    protected $error;

    public function __construct ($upload_name) {
        if (!isset($_FILES[$upload_name]['error'])) {
            throw new UploadException("$upload_name is not an upload");
        }

        $this->name = $upload_name;
        $this->size = $_FILES[$this->name]['size'];
        $this->tmp_name = $_FILES[$this->name]['tmp_name'];
        $this->error = $_FILES[$this->name]['error'];

        $this->checkUploadErrors();
        $this->validateUpload();
    }

    public function checkUploadErrors() {
        if ($this->error != UPLOAD_ERR_OK) {
            if (isset(self::$error_messages[$this->error])) {
                $msg = self::$error_messages[$this->error];
            } else {
                $msg = "An unknown upload error occured ($this->error)";
            }
            throw new UploadException("Upload error for $this->name: $msg");
        }
    }

    public function checkMaxSize($size_limit) {
        if ($size_limit && $this->size > $size_limit) {
            throw new UploadException(
                "$this->name exceeds the maximum upload size of $size_limit" .
                " (received: $this->size)"
            );
        }
    }

    public function getFilePointer($mode='r') {
        $fp = fopen($this->tmp_name, $mode);
        if (!$fp) {
            throw new UploadException(
                "The uploaded file for $this->name could not be opened."
            );
        }
        return $fp;
    }

    public function saveTo($new_file) {
        if (!move_uploaded_file($this->tmp_name, $new_file)) {
            throw new UploadException("$this->name could not be saved.");
        }
    }

    public function sha1sum() {
        return sha1_file($this->tmp_name);
    }

    public function validateUpload() {}
}
class UploadException extends Exception {}
?>