<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

require_once __DIR__ . '/../includes/include.php';

$page = new Page('upload');
$page->setVar('title', 'Upload ACPI tables');
$page->setVar('upload_home', UPLOAD_HOME_URL);
$errors = array();
$uploads = array();

try {
    $uploads['acpidump'] = new AcpidumpUpload();
    $uploads['lspci'] = new LspciUpload();
    $uploads['dmidecode'] = new DmidecodeUpload();
} catch (UploadException $ex) {
    $page->setVar('upload_error', $ex->getMessage());
    $uploads = null;
}

if ($uploads) {
    try {
        $submission_id = Submission::getSubmissionIdFromHashes(
            $uploads['acpidump']->sha1sum(),
            $uploads['lspci']->sha1sum(),
            $uploads['dmidecode']->sha1sum()
        );
        if ($submission_id > 0) {
            $page->setVar('submission_url', SUBMISSION_URL . $submission_id);
            throw new SubmissionException(
                'The uploaded files were seen before.'
            );
        }
        $submission = Submission::saveUploads(
            $uploads['acpidump'],
            $uploads['lspci'],
            $uploads['dmidecode']
        );

        $page->setVar('upload_success', true);
        $page->setVar('submission_url', SUBMISSION_URL . $submission->getSubmissionId());
        try {
            $claimKey = $submission->submissionClaimKey();

            $page->setVar('claim_url', SUBMISSION_CLAIM_URL . $claimKey);
        } catch (DatabaseException $ex) {
            $page->setVar('claim_key_error', $ex->getMessage());
        }
    } catch (DatabaseException $ex) {
        $page->setVar('database_error', $ex->getMessage());
    } catch (SubmissionException $ex) {
        $page->setVar('upload_error', $ex->getMessage());
    }
}
$page->display();
?>
