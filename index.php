<?php
$baseDir = __DIR__;
set_include_path(".:/usr/share/php:/usr/share/pear:$baseDir/classes:$baseDir/lib:$baseDir/lib/smtp_mailer_swift/lib/classes");
require_once 'vendor/autoload.php';
$r = new BIM_Controller;
$r->handleReq();