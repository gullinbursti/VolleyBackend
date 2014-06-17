<?php
$baseDir = __DIR__;

$prod_path = ".:/usr/share/php:/usr/share/pear:$baseDir/classes:$baseDir/lib:$baseDir/lib/smtp_mailer_swift/lib/classes";
$dev_path = "$baseDir/vendor/hamcrest/hamcrest-php/hamcrest";
$final_path = "$prod_path:$dev_path";

set_include_path($final_path);

require_once 'config/Dynamic-phpunit-MOCKED.php';

require_once 'lib/vendor/autoload.php';
