<?php
class BIM_EventDispatcher_User extends BIM_EventDispatcher_Base {

    protected function _now() {
        $utc = new DateTimeZone("UTC");
        $now = new DateTime( "now", $utc );
        return $now->format(DateTime::ISO8601);
    }

    public function convertInvitations( $memberId, $memberPhoneNumber) {
        $this->dispatchEvent('member.tasks.convert_invitations', $memberId, $memberPhoneNumber, $this->_now());
    }

}

?>
