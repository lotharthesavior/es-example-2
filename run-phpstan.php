<?php

// Helper to run PHPStan (needed because phpstan binary doesn't output to terminal)
$_SERVER['argv'] = array_merge(['phpstan', 'analyse', '--no-progress'], array_slice($argv, 1));
$_SERVER['argc'] = count($_SERVER['argv']);
ob_start();
try {
    require 'phar://'.realpath('vendor/phpstan/phpstan/phpstan.phar').'/bin/phpstan';
} catch (Throwable $e) {
}
echo ob_get_clean();
