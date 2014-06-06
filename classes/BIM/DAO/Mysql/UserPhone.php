<?php

class BIM_DAO_Mysql_UserPhone extends BIM_DAO_Mysql{

    public function create( $userId, $phoneNumberEnc, $verifyCode,
            $verifyCountDown ) {

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
        $raw_data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        $data = isset($raw_data[0])
                ? $raw_data[0]
                : null;
        return $data;
    }

    public function readExistingByUserId( $userId ) {
        $query = "
            SELECT
                id, user_id, phone_number_enc
            FROM `hotornot-dev`.tblUserPhones WHERE user_id = ?
            ";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
        $raw_data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        $data = isset($raw_data[0])
                ? $raw_data[0]
                : null;
        return $data;
    }

    public function readVerifyDataByUserId( $userId ) {
        $query = "
            SELECT
                id,
                user_id,
                phone_number_enc,
                verified,
                verified_date,
                verify_code,
                verify_count_down,
                verify_count_total,
                verify_last_attempt,
                updated
            FROM `hotornot-dev`.tblUserPhones WHERE user_id = ?
            ";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
        $raw_data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        $data = isset($raw_data[0])
                ? $raw_data[0]
                : null;
        return $data;
    }


    public function updateNewPhone( $phoneId, $userId, $phoneNumberEnc,
            $verifyCode, $verifyCountDown) {
        $query = "
            UPDATE `hotornot-dev`.tblUserPhones
            SET
                user_id = ?,
                phone_number_enc =?,
                verified = 0,
                verified_date = NULL,
                verify_code = ?,
                verify_count_down = ?,
                updated = NOW()
            WHERE id = ?
            ";

        $params = array( $userId, $phoneNumberEnc, $verifyCode,
                $verifyCountDown, $phoneId );

        $this->prepareAndExecute( $query, $params );
        return $this->rowCount;
    }

    public function updateVerifyPhonePin( $userId, $phoneNumberEnc, $verifyCode ) {
        $query = "
            UPDATE `hotornot-dev`.tblUserPhones
            SET
                verified = 1,
                verified_date = NOW(),
                verify_code = NULL,
                verify_last_attempt = NOW(),
                verify_count_down = verify_count_down - 1,
                verify_count_total = verify_count_total + 1,
                updated = NOW()
            WHERE
                user_id = ?
                    AND phone_number_enc = ?
                    AND verify_code = ?
                    AND verify_count_down > 0;
            ";

        $params = array( $userId, $phoneNumberEnc, $verifyCode );

        $this->prepareAndExecute( $query, $params );
        return $this->rowCount == 1 ? true : false;
    }

    public function updatePhonePinVerifyFailed( $userId, $phoneNumberEnc ) {
        $query = "
            UPDATE `hotornot-dev`.tblUserPhones
            SET
                verify_last_attempt = NOW(),
                verify_count_down = verify_count_down - 1,
                verify_count_total = verify_count_total + 1,
                updated = NOW()
            WHERE
                user_id = ?
                    AND phone_number_enc = ?
                    AND verify_count_down > 0;
            ";

        $params = array( $userId, $phoneNumberEnc );
        $this->prepareAndExecute( $query, $params );
        return $this->rowCount == 1 ? true : false;
    }

    public function deleteByPhoneNumberEnc( $phoneNumberEnc ) {
        $query = "DELETE FROM `hotornot-dev`.tblUserPhones WHERE phone_number_enc = ?";
        $params = array( $phoneNumberEnc );
        $this->prepareAndExecute( $query, $params );
        return $this->rowCount;
    }

    public function deleteById( $id ) {
        $query = "DELETE FROM `hotornot-dev`.tblUserPhones WHERE id = ?";
        $params = array( $id );
        $this->prepareAndExecute( $query, $params );
        return $this->rowCount;
    }
}
