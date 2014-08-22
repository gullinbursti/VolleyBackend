<?php
class BIM_EventDispatcher_Club extends BIM_EventDispatcher_Base {

    protected function _now() {
        $utc = new DateTimeZone("UTC");
        $now = new DateTime( "now", $utc );
        return $now->format(DateTime::ISO8601);
    }

    public function invitationToMember( $clubId, $actorMemberId, $inviteeMemberId, $inviteePhoneNumber ) {
        $this->dispatchEvent('club.tasks.invitation_sent', $clubId, $actorMemberId, $inviteeMemberId, $inviteePhoneNumber, $this->_now());
    }

    public function invitationToNonMember( $clubId, $actorMemberId, $inviteePhoneNumber ) {
        $this->dispatchEvent('messaging.tasks.send_sms_invitation', $clubId, $actorMemberId, $inviteePhoneNumber, $this->_now());
    }

    public function memberJoined( $clubId, $actorMemberId) {
        $this->dispatchEvent('club.tasks.joined', $clubId, $actorMemberId, $this->_now());
    }

}

?>
