<?php
class BIM_EventDispatcher_StatusUpdate extends BIM_EventDispatcher_Base {

    protected function _now() {
        $utc = new DateTimeZone("UTC");
        $now = new DateTime( "now", $utc );
        return $now->format(DateTime::ISO8601);
    }

    public function post_status_update( $clubId, $actorMemberId, $inviteeMemberId ) {
        $this->dispatchEvent('challenge.tasks.post_status_update', $clubId, $actorMemberId, $inviteeMemberId, $this->_now());
    }

}

?>
