<?php
define('OPTIONS_PDO', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$isDev = getenv('APP_ENV') == "development";

//////CONFIG////////
ini_set('display_errors', $isDev ? 1 : 0);
ini_set('display_startup_errors', $isDev ? 1 : 0);
error_reporting($isDev ? E_ALL : 0);
