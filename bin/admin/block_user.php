<?php
require_once 'vendor/autoload.php';
if( $_POST && !empty( $_POST['username'] ) ){
    echo("<pre>");
    print_r(BIM_Model_User::blockByName( array($_POST['username']) ));
    echo("</pre>");
} else {
    echo(
    "
    <html>
    <head>
    </head>
    <body>
    <form method='POST'>
    <input type='submit' value='block user'><input type='text' size='50' name='username'>
    </form>
    </body>
    </html>
    "
    );
}

