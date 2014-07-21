<?php
class BIM_EventDispatcher_Club extends BIM_EventDispatcher_Base {

    public function invitationToMember( $clubId, $actorMemberId, $inviteeMemberId ) {

        $this->dispatchEvent('club.tasks.invitation_sent', $clubId, $actorMemberId, $inviteeMemberId, 4);

    }

}

?>
