<?php

// Load environment variables
$bimProjectBaseDir = getenv('BIM_PROJECT_BASE_DIR');
$bimConfigDynamic = getenv('BIM_CONFIG_DYNAMIC');

// Set defaults
$baseDir = !empty($bimProjectBaseDir)
    ? $bimProjectBaseDir
    : __DIR__;

$configDynamic = !empty($bimConfigDynamic)
    ? $bimConfigDynamic
    : __DIR__ . '/phpunit-Config-Dynamic-DEFAULT.php';

// Setup path
$prodPath = "$baseDir:/usr/share/php:/usr/share/pear:$baseDir/classes:$baseDir/lib:$baseDir/lib/smtp_mailer_swift/lib/classes";
$devPath = "$baseDir/vendor/hamcrest/hamcrest-php/hamcrest";
$finalPath = "$prodPath:$devPath";
set_include_path($finalPath);

// Load configuration
require_once "$configDynamic";

// Load other libraries
require_once "$baseDir/lib/vendor/autoload.php";
