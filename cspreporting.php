<?php

/* This script should be placed in a directory on the chosen reporting server, and its
address put in the constant CSP_REPORT_URL in wp-config.php of any u3a siteoworks server
where the site admin has chosen to allow CSP reports to be centrally monitored.
*/

// The payload is a JSON object. The reporting checks whether the payload recieved looks
// like an acceptable CSP report. All bogus input is discarded.

const VALID_FIELDS = ['blocked-uri','disposition','document-uri','effective-directive',
'original-policy','referrer','status-code','violated-directive'];

const MAX_FIELD_LENGTH = 400;

// The output file - this is written to the same directory as this script, so the directoy
// needs to be writable.

$log_file = dirname(__FILE__) . '/csp-violations.csv';
$log_file_size_limit = 1000000; // bytes - once exceeded a single backup is taken, and the file overwritten

$object = file_get_contents('php://input');

if (!$object) {
    http_response_code(404); // not a valid report.
    exit(0);
}

// The contents must be an object containing a single 'csp-report' or invalid

$json_data = json_decode($object);

if (!$json_data) {
    http_response_code(404); // not a valid report.
    exit(0);
}

$report = $json_data->{'csp-report'};

if (!$report) {
    http_response_code(404); // not a valid report.
    exit(0);
}

$csv = date("'Y-m-d:H:i:s'");
$csv = gmdate("'Y-m-d:H:i:s", time()) . " UTC'";

foreach (VALID_FIELDS as $fieldname) {
    $csv .= ",";
    $value = $report->{$fieldname};
    if (strlen(strval($value)) > MAX_FIELD_LENGTH) {
        http_response_code(404); // too large a field - not a valid report.
        exit(0);
    }
    $value = trim(strip_tags($value));
    $csv .= "'" . $value . "'";
}

// Happy with the input contents

http_response_code(204);

// backup of oversized file
if (file_exists($log_file)) {
    if (filesize($log_file) > $log_file_size_limit) {
        // try to back up the file - create a single backup
        $backup = dirname(__FILE__) . '/csp-violations-backup.log';
        rename($log_file, $backup, null); // will overwrite old backup
    }
}

// add csv headers to new file
if (!file_exists($log_file)) {
    $header = "'timestamp'";
    foreach (VALID_FIELDS as $fieldname) {
        $header .= ",'" . $fieldname . "'";
    }
    file_create_string($log_file, $header);
}

file_put_string($log_file, $csv);

function file_create_string($filePath, $content, $flags = LOCK_EX)
{
    // The report is in the current directory - so no attempt to create it - it must
    // exist. One last check for file exists - to make the window for duplicate creation
    // of the header line very small.
    if (!file_exists($filePath)) {
        file_put_contents($filePath, $content . "\r\n", $flags);
    }
}

function file_put_string($filePath, $content, $flags = FILE_APPEND | LOCK_EX)
{
    if (file_exists($filePath)) {
        file_put_contents($filePath, $content . "\r\n", $flags);
    }
}
