<?php
class BIM_App_Moji extends BIM_App_Base{

    public static function invite( $memberId, $emoji, $users = array(), $nonUsers = array() ) {
        $invited = false;
        foreach ($nonUsers as $user) {
            if ( count($user) != 1 ) {
                // We only want phone numbers
                return false;
            }
        }
        if( $memberId ) {
            // For there to be a memberId here, resolveUserId succeeded
            $invited = BIM_Model_Moji::invite( $memberId, $emoji, $users, $nonUsers );
            if( $invited ){
                self::postInvitationEvents($memberId, $emoji, $users, $nonUsers);
            }
        }
        return $invited;
    }

    public static function postInvitationEvents( $actorMemberId, $emoji, $invitees, $nonUsers ) {
        if ( $actorMemberId && $emoji && (count($invitees) >= 1 || count($nonUsers) >= 1)) {
            $eventDispatcher = new BIM_EventDispatcher_Moji();
            if ( is_object($eventDispatcher) ) {
                $dao = new BIM_DAO_Mysql_UserPhone( BIM_Config::db() );
                foreach ( $invitees as $inviteeMemberId ) {
                    $memberPhoneObject = $dao->readExistingByUserId( $inviteeMemberId );
                    if ($memberPhoneObject) {
                        $memberSMS = BIM_Utils::blowfishDecrypt($memberPhoneObject->phone_number_enc);
                    } else {
                        $memberSMS = null;
                    }
                    $eventDispatcher->invitationToMember($actorMemberId, $emoji, $inviteeMemberId, $memberSMS);
                }
                if (count($nonUsers) >= 1) {
                    /**
                     * We want to ensure submitted numbers have country codes.
                     * We'll start by focusing on N.America, so if the inviter
                     * has "+1" as the first two digits, and the invitee has
                     * only ten digits (and the first isn't '+', we'll add "+1"
                     * for N.America. Many U.S. people don't store +1 in their
                     * phones.
                     */
                    $actorPrefix = null;
                    $actorPhoneObject = $dao->readExistingByUserId( $actorMemberId );
                    if ($actorPhoneObject) {
                        $actorSMS = BIM_Utils::blowfishDecrypt($actorPhoneObject->phone_number_enc);
                        $actorPrefix = substr($actorSMS, 0, 2);
                    }
                    foreach ( $nonUsers as $inviteePhone ) {
                        if ($inviteePhone) {
                            if ('+1' == $actorPrefix && '+' != substr($inviteePhone, 0, 1)) {
                                // No + means this could be a sloppy number
                                if (10 == strlen($inviteePhone)) {
                                    $inviteePhone = '+1' . $inviteePhone;
                                }
                            }
                            $eventDispatcher->invitationToNonMember($actorMemberId, $emoji, $inviteePhone);
                        }
                    }
                }
            }
        }
    }

}
