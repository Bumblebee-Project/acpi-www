<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

require_once __DIR__ . '/../includes/include.php';

$uploads = array();

try {
    $uploads['acpidump'] = new AcpidumpUpload();
    $uploads['lspci'] = new LspciUpload();
    $uploads['dmidecode'] = new DmidecodeUpload();
} catch (UploadException $ex) {
    echo htmlentities($ex->getMessage()), "\n";
    $uploads = null;
}

if ($uploads) {
    
}
?>
