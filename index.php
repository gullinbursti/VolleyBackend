<?php
bootstrap();
$r = new BIM_Controller;
$r->handleReq();

function bootstrap(){
    $baseDir = __DIR__;
    $incPath = ".:/usr/share/php:/usr/share/pear:$baseDir/classes:$baseDir/lib:$baseDir/lib/smtp_mailer_swift/lib/classes";
    set_include_path($incPath);
    require_once 'vendor/autoload.php';
}
