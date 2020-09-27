<?php
require 'TransIP_AccessToken.php';

// Main program consuming the TransIP_AccessToken class.
// The TransIP_AccessToken class is downloaded from: https://api.transip.nl/downloads/TransIP_AccessToken.tar.gz

function showHelp() {
    echo "Main program used for creating TransIP API tokens\n";
    echo "Usage: php TransIP_main.php --user=<user> --key=<private key> --label=<token label>\n";
    echo "\n";
    echo "Program options:\n";
    echo "  --user=USER              the TransIP user\n";
    echo "  --key=PRIVATE_KEY        the private key generated from the TransIP console\n";
    echo "                           see: https://www.transip.nl/cp/account/api/\n";
    echo "  --label=LABEL            the label for the created token \n";
    echo "  --read-only              when specified the created token can only be used in read only mode\n";
    echo "                           (default = false)\n";
    echo "  --global                 when specified the created token is not bound to the specified whitelist and can be\n";
    echo "                           used everywhere (default = false)\n";
    echo "  --expiration-time=TIME   the expiration time of the created token, with a maximum of 1 month (default = 30 minutes)\n";
    echo "                           available options: '30 minutes', '1 hour', '1 day', '1 week', '2 weeks', '1 month'\n";
    echo "  --dry-run                don't execute and instead print the values to the terminal\n";
}

// Set argument options
$val = getopt(null, ["user:", "key:", "label:", "read-only", "global", "expiration-time::", "dry-run", "help"]);

// parse '--help' option
if (array_key_exists('help', $val)) {
    showHelp();
    exit(0);
}

// validate whether all required options are set
if (!array_key_exists('user', $val) || !array_key_exists('key', $val) || !array_key_exists('label', $val)) {
    echo "Not all required options are specified, try again\n";
    echo "\n";
    showHelp();
    exit(1);
}

// parse required options
$login = $val['user'];
$private_key = $val['key'];
$label = $val['label'];

// parse '--read-only' option
$read_only = false;
if (array_key_exists('read-only', $val)) {
    $read_only = !$val['read-only']; // Inverse true/false because of PHP its weird defaults for boolean arguments
}

// parse '--global' option
$global = false;
if (array_key_exists('global', $val)) {
    $global = !$val['global']; // Inverse true/false because of PHP its weird defaults for boolean arguments
}

// parse '--expiration-time' option
$expiration_time = "30 minutes";
if (array_key_exists('expiration-time', $val)) {
    $expiration_time = $val['expiration-time'];
}

// parse '--dry-run' option
$dry_run = false;
if (array_key_exists('dry-run', $val)) {
    $dry_run = !$val['dry-run']; // Inverse true/false because of PHP its weird defaults for boolean arguments
}

if ($dry_run) {
    echo "Arguments passed:\n";
    echo "=================\n";
    echo "User:            " . $login . "\n";
    echo "Key:             <hidden>\n";
    echo "Label:           " . $label . "\n";
    echo "Read-only:       " . ($read_only ? "true" : "false") . "\n";
    echo "Global:          " . ($global ? "true" : "false") . "\n";
    echo "Expiration time: " . $expiration_time . "\n";
}

if (!$dry_run) {
    // create the token
    $transIpAccessToken = new TransIP_AccessToken($login, $private_key, $read_only, $global, $expiration_time);
    $transIpAccessToken->setLabel($label);
    echo $transIpAccessToken->createToken();
}

