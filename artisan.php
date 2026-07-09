<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $command = ($argv[1] ?? "");

    match ($command) {
        'migrate' => new App\MigrationManager(),
        default => print("Command not found. \n")
    };
} catch (\Throwable $th) {
    printr($th);die;
}
