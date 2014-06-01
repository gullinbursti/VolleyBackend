<?php

class BIM_Controller_UserPhone extends BIM_Controller_Base {

    public function updatePhone() {
        $input = (object) ($_POST ? $_POST : $_GET);
        $usedId = isset($input->userID) ? $input->userID : null;
        $phone = isset($input->phone) ? $input->phone : null;

        return null;
    }

    public function validatePhone() {
        $input = (object) ($_POST ? $_POST : $_GET);
        $usedId = isset($input->userID) ? $input->userID : null;
        $pin = isset($input->pin) ? $input->pin : null;

        return null;
    }
}
