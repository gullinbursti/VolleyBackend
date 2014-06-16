<?php
$baseDir = __DIR__;

$prod_path = ".:/usr/share/php:/usr/share/pear";
$dev_path = "$baseDir/vendor/hamcrest/hamcrest-php/hamcrest:$baseDir/src/test/integration/lib";
$final_path = "$prod_path:$dev_path";

set_include_path($final_path);


require_once 'config/IntegrationTestConfig-pedro.php';
require_once 'IntegrationTestContext.php';

$config = new BIM_IntegrationTest_Config_Pedro();
$context = BIM_IntegrationTest_IntegrationTestContext::getContext();
$context->setConfiguration($config);


require_once 'vendor/autoload.php';
