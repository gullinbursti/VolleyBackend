<?php

class BIM_DAO_Mysql_UserPhone extends BIM_DAO_Mysql{

    public function create( $userId, $phoneNumberEnc, $verifyCode, $verifyCountDown ) {

        $query = "
            INSERT INTO `hotornot-dev`.tblUserPhones
                (
                    user_id,
                    phone_number_enc,
                    verified,
                    verified_date,
                    verify_code,
                    verify_count_down,
                    verify_count_total,
                    verify_last_attempt,
                    created,
                    updated
                )
                VALUE (?, ?, 0, NULL, ?, ?, 0, NULL, NOW(), NOW())
        ";

        $params = array( $userId, $phoneNumberEnc, $verifyCode, $verifyCountDown );
        $stmt = $this->prepareAndExecute( $query, $params );

        $id = $this->lastInsertId ? $this->lastInsertId : null;

        return $id;
    }

    public function readById( $id ) {
        $query = "SELECT * FROM `hotornot-dev`.tblUserPhones WHERE id = ?";
        $params = array( $id );
        $stmt = $this->prepareAndExecute( $query, $params );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        return $data;
    }


    public function deleteByPhoneNumberEnc( $phoneNumberEnc ) {
        $query = "DELETE FROM `hotornot-dev`.tblUserPhones WHERE phone_number_enc = ?";
        $params = array( $phoneNumberEnc );
        $this->prepareAndExecute( $query, $params );
        return $this->rowCount;
    }



}