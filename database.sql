/**
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @author Lekensteyn <lekensteyn@gmail.com
 */

-- Holds references to machines, identified by unique acpidump submissions
CREATE TABLE machine (
    acpidump_hash BINARY(20) NOT NULL,
    PRIMARY KEY (acpidump_hash)
);

-- Uploaded acpidump, lspci and dmidecode files (dupe check)
CREATE TABLE submission (
    submission_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    submission_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    acpidump_hash BINARY(20) NOT NULL,
    dmidecode_hash BINARY(20) NOT NULL,
    lspci_hash BINARY(20) NOT NULL,
    PRIMARY KEY (submission_id),
    UNIQUE (acpidump_hash, dmidecode_hash, lspci_hash)
) AUTO_INCREMENT=1;

-- For quick search and browsing by the keywords
CREATE TABLE dmidecode (
    dmidecode_hash BINARY(20) NOT NULL,
    sys_manufacturer VARCHAR(32),
    sys_name VARCHAR(32),
    bb_manufacturer VARCHAR(32),
    bb_name VARCHAR(32),
    PRIMARY KEY (dmidecode_hash)
);

-- For claiming submissions later
CREATE TABLE submission_claim (
    claim_key BINARY(20) NOT NULL,
    submission_id BINARY(20) NOT NULL,
    PRIMARY KEY (claim_key),
    UNIQUE (submission_id)
);