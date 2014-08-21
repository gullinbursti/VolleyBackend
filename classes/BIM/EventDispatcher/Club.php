<?php
class BIM_EventDispatcher_Club extends BIM_EventDispatcher_Base {

    public function invitationToMember( $clubId, $actorMemberId, $inviteeMemberId ) {
        $utc = new DateTimeZone("UTC");
        $now = new DateTime( "now", $utc );
        $nowString = $now->format(DateTime::ISO8601);
        $this->dispatchEvent('club.tasks.invitation_sent', $clubId, $actorMemberId, $inviteeMemberId, $nowString);
    }

    public function invitationToNonMember( $clubId, $actorMemberId, $inviteePhoneNumber ) {
        $utc = new DateTimeZone("UTC");
        $now = new DateTime( "now", $utc );
        $nowString = $now->format(DateTime::ISO8601);
        $this->dispatchEvent('messaging.tasks.send_sms_invitation', $clubId, $actorMemberId, $inviteePhoneNumber, $nowString);
    }

    public function memberJoined( $clubId, $actorMemberId) {
        $utc = new DateTimeZone("UTC");
        $now = new DateTime( "now", $utc );
        $nowString = $now->format(DateTime::ISO8601);
        $this->dispatchEvent('club.tasks.joined', $clubId, $actorMemberId, $nowString);
    }

}

?>
