<?php

use TaskTracker\App\App;
use TaskTracker\Infra\Storage\StorageConfig;
use TaskTracker\Infra\Storage\StorageFactory;

// Autoload (PSR-4)
spl_autoload_register(function ($class_name) {
    // Namespace prefix this autoloader handles
    $prefix = 'TaskTracker\\';

    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class_name, $len) !== 0) {
        return;
    }

    // Get the relative class name and build the file path
    $relative_class = substr($class_name, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Detect debug flag (--debug or -d) and strip it from the arguments
$debug = false;
$argv = array_values(array_filter($argv, function ($arg) use (&$debug) {
    if ($arg === '--debug' || $arg === '-d') {
        $debug = true;
        return false;
    }
    return true;
}));

try {
    $storagePath = __DIR__ . '/data';

    // The storage type is chosen by the user via `task-tracker storage <type>`
    // and persisted across invocations. Defaults to CSV when not set.
    $storageConfig = new StorageConfig($storagePath);
    $fileStorage = StorageFactory::create($storageConfig->get(), $storagePath);

    $app = new App($fileStorage, $storageConfig);
    echo $app->run($argv) . PHP_EOL;

} catch (Throwable $e) {
    if ($debug) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    } else {
        echo "An error occurred: " . $e->getMessage() . "\n";
    }
}
