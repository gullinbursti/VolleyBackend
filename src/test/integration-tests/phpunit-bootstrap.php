<?php

// Load environment variables
$bimProjectBaseDir = getenv('BIM_PROJECT_BASE_DIR');

// Set defaults
$baseDir = !empty($bimProjectBaseDir)
    ? $bimProjectBaseDir
    : __DIR__;

// Setup path
$prodPath = "$baseDir:/usr/share/php:/usr/share/pear";
$devPath = "$baseDir/vendor/hamcrest/hamcrest-php/hamcrest:$baseDir/src/test/integration-tests/lib";
$finalPath = "$prodPath:$devPath";
set_include_path($finalPath);


require_once 'config/IntegrationTestConfig-pedro.php';
require_once 'IntegrationTestContext.php';

$config = new BIM_IntegrationTest_Config_Pedro();
$context = BIM_IntegrationTest_IntegrationTestContext::getContext();
$context->setConfiguration($config);

// Load other libraries
require_once "$baseDir/vendor/autoload.php";
