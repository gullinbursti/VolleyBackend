<?php

// Run from 'classes' with `php ../bin/admin/send_invites.php`

require_once '../lib/vendor/autoload.php';

$eventDispatcher = new BIM_EventDispatcher_Club();
$dbConfig = BIM_Config::db();
$dao = new BIM_DAO_Mysql_Club(BIM_Config::db());

$data = $dao->getUnsentInvites('2014-09-01');
if ($data) {
    foreach ($data as $row) {
        if (isset($row->club_id) && isset($row->owner_id) && isset($row->mobile_number)) {
            // TODO: Standardize mobile_number
            $mobileNumber = $row->mobile_number;
            if (10 == strlen($mobileNumber)) {
                $mobileNumber = '+1' . $mobileNumber;
            }
            echo "eventDispatcher->invitationToNonMember($row->club_id, $row->owner_id, $mobileNumber);\n";
            //$eventDispatcher->invitationToNonMember($row->club_id, $row->owner_id, $mobileNumber);
        }
    }
}
