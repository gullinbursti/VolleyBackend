<?php
class BIM_EventDispatcher_Moji extends BIM_EventDispatcher_Base {

    protected function _now() {
        $utc = new DateTimeZone("UTC");
        $now = new DateTime( "now", $utc );
        return $now->format(DateTime::ISO8601);
    }

    public function invitationToMember( $actorMemberId, $emoji, $inviteeMemberId, $inviteePhoneNumber ) {
        $this->dispatchEvent('messaging.tasks.send_moji_push_invitation', $actorMemberId, $emoji, $inviteeMemberId, $inviteePhoneNumber, $this->_now());
    }

    public function invitationToNonMember( $actorMemberId, $emoji, $inviteePhoneNumber ) {
        $this->dispatchEvent('messaging.tasks.send_moji_sms_invitation', $actorMemberId, $emoji, $inviteePhoneNumber, $this->_now());
    }

}

?>
