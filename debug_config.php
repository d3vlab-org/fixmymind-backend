<?php

require_once '/var/www/html/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
$dotenv->load();

// Helper function for debugging
function debugConfigSection($name, $config) {
    echo "=== Checking $name configuration ===\n";
    
    $mergeableOptions = [
        'auth' => ['guards', 'providers', 'passwords'],
        'broadcasting' => ['connections'],
        'cache' => ['stores'],
        'database' => ['connections'],
        'filesystems' => ['disks'],
        'logging' => ['channels'],
        'mail' => ['mailers'],
        'queue' => ['connections'],
    ];
    
    if (!isset($mergeableOptions[$name])) {
        echo "No mergeable options for $name\n\n";
        return;
    }
    
    foreach ($mergeableOptions[$name] as $option) {
        if (isset($config[$option])) {
            echo "Option '$option': " . gettype($config[$option]) . "\n";
            if (!is_array($config[$option])) {
                echo "ERROR: '$option' should be an array but is: " . gettype($config[$option]) . "\n";
                echo "Value: ";
                var_dump($config[$option]);
            }
        }
    }
    echo "\n";
}

try {
    // Check each configuration file that has mergeable options
    $configFiles = ['auth', 'cache', 'database', 'logging', 'queue'];
    
    foreach ($configFiles as $configName) {
        if (file_exists("/var/www/html/config/$configName.php")) {
            $config = require "/var/www/html/config/$configName.php";
            debugConfigSection($configName, $config);
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

