<?php
/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

// XXX: refactor? perhaps the new uploaded file needs to be an object too
class Submission {
    private $submission_id;

    /**
     * Pushes some uploads to a queue folder
     * @param Upload $acpidump An acpidump.txt upload
     * @param Upload $lspci An lspci.txt upload
     * @param Upload $dmidecode An dmidecode.txt upload
     * @throws SubmissionException when the upload cannot be saved
     * @throws DatabaseException when a query fails to execute
     */
    public static function saveUploads(Upload $acpidump, Upload $lspci,
                                       Upload $dmidecode) {
        $db = Database::getInstance();

        $acpidump_hash = $acpidump->sha1sum();
        $dmidecode_hash = $dmidecode->sha1sum();
        $lspci_hash = $lspci->sha1sum();

        if (self::getSubmissionIdFromHashes($acpidump_hash, $lspci_hash,
                                       $dmidecode_hash) > 0) {
            throw new SubmissionException(
                'The data for your machine has been submitted before.'
            );
        }

        $stmt_ins_submission = $db->prepare(
            'INSERT INTO submission SET acpidump_hash=UNHEX(?),
            dmidecode_hash=UNHEX(?), lspci_hash=UNHEX(?)'
        );
        $stmt_ins_submission->bind_param('sss',
            $acpidump_hash,
            $dmidecode_hash,
            $lspci_hash
        );
        if (!$stmt_ins_submission->execute()) {
            throw new DatabaseException(
                'The submission could not be saved.'
            );
        }

        // integer cast in case the database behaves weird (safeguard)
        $submission_id = $stmt_ins_submission->insert_id;
        $stmt_ins_submission->close();

        $submission = new Submission($submission_id);

        try {
            self::saveUploadedFiles($submission->getDir(),
                                    $acpidump, $lspci, $dmidecode);
        } catch (Exception $ex) {
            $submission->remove();
            // cleaned up, now pass the error
            throw $ex;
        }

        return $submission;
    }
    private static function saveUploadedFiles($submission_dir,
        Upload $acpidump, Upload $lspci, Upload $dmidecode) {
        if (!mkdir($submission_dir)) {
            throw new SubmissionException(
                'The submission could not be saved'
            );
        }
        $acpidump->saveTo("$submission_dir/acpidump.txt");
        $lspci->saveTo("$submission_dir/lspci.txt");
        $dmidecode->saveTo("$submission_dir/dmidecode.txt");
    }

    /**
     * Gets the submission id for upload hashes if any
     * @param string $acpidump_hash The hash of acpidump.txt
     * @param string $lspci_hash The hash of lspci.txt
     * @param string $dmidecode_hash The hash of dmidecode.txt
     * @return int A number higher than zero if the submission ID was found,
     * zero otherwise
     * @throws DatabaseException when the submission_id lookup query fails
     */
    public static function getSubmissionIdFromHashes($acpidump_hash,
        $lspci_hash, $dmidecode_hash) {
        $db = Database::getInstance();

        $stmt_hash_exists = $db->prepare(
            'SELECT submission_id FROM submission WHERE
                acpidump_hash=UNHEX(?) &&
                dmidecode_hash=UNHEX(?) &&
                lspci_hash=UNHEX(?)
            LIMIT 1'
        );

        $stmt_hash_exists->bind_param('sss',
            $acpidump_hash,
            $dmidecode_hash,
            $lspci_hash
        );

        if (!$stmt_hash_exists->execute()) {
            throw new DatabaseException(
                'It did not became clear whether the files have been submitted'
                . 'before.'
            );
        }

        $stmt_hash_exists->bind_result($submission_id);
        if (!$stmt_hash_exists->fetch()) {
            $submission_id = 0;
        }
        $stmt_hash_exists->close();

        return $submission_id;
    }

    public function __construct($submission_id) {
        $this->submission_id = (int)$submission_id;
        if ($this->submission_id < 1) {
            throw SubmissionException('Invalid submission ID.');
        }
    }
    /**
     * Determines the path to the submission directory
     * @return string The path on the filesystem to the submission directory
     */
    public function getDir() {
        return SUBMISSIONS_DIR . '/' . $this->submission_id;
    }
    /**
     * Removes the submission entry in the database and related uploaded files
     */
    public function remove() {
        $this->removeDatabaseEntry();
        $this->removeFiles();
    }
    private function removeDatabaseEntry() {
        $db = Database::getInstance();
        // $submission_id is a number, this is enforced in the constructor
        $db->query('DELETE FROM submission WHERE submission_id=' .
                   $this->submission_id);
    }
    private function removeFiles() {
        $submission_dir = $this->getDir();
        foreach (array('acpidump.txt', 'lspci.txt', 'dmidecode.txt') as $file) {
            $path = "$submission_dir/$file";
            is_file($path) && unlink($path);
        }
        if (is_dir($submission_dir) && $dh = opendir($submission_dir)) {
            $isEmpty = true;
            while (($entry=readdir($dh)) !== false) {
                if ($entry != '.' && $entry != '..') {
                    $isEmpty = true;
                    break;
                }
            }

            closedir($dh);
            $isEmpty && rmdir($submission_dir);
        }
    }

    /**
     * Get the submission ID
     * @returns int The ID of the submission
     */
    public function getSubmissionId() {
        return $this->submission_id;
    }

    /**
     * Generates a key for claiming a submission
     * @return string A key of size 40 which can be used for claiming an upload
     * @throws DatabaseException when the key could not be retrieved
     */
    public function submissionClaimKey() {
        $db = Database::getInstance();
        // generate a 160-bit random string. @Mathematicans, any errors here?
        $claim_key = str_shuffle(sha1(time() . '-' . mt_rand()));

        $stmt_gen_key = $db->prepare('INSERT INTO submission_claim SET
                                     claim_key=UNHEX(?), submission_id=?');
        $stmt_gen_key->bind_param('sd', $claim_key, $this->submission_id);
        if (!$stmt_gen_key->execute()) {
            // The chance is higher that the issue is caused by a dead database
            // than a collision
            throw new DatabaseException(
                'A key for claiming this submission could not be generated.'
            );
        }
        $stmt_gen_key->close();
        return $claim_key;
    }
}
class SubmissionException extends Exception {}
?>