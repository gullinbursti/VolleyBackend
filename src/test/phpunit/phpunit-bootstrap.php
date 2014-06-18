<?php

$bimProjectBaseDir = getenv('BIM_PROJECT_BASE_DIR');
$baseDir = !empty($bimProjectBaseDir)
    ? $bimProjectBaseDir
    : __DIR__;

$prodPath = "$baseDir:/usr/share/php:/usr/share/pear:$baseDir/classes:$baseDir/lib:$baseDir/lib/smtp_mailer_swift/lib/classes";
$devPath = "$baseDir/vendor/hamcrest/hamcrest-php/hamcrest";
$finalPath = "$prodPath:$devPath";

set_include_path($finalPath);

require_once __DIR__ . '/phpunit-Dynamic-MOCKED.php';

require_once "$baseDir/lib/vendor/autoload.php";
