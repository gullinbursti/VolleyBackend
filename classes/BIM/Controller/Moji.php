<?php
/**
 * Supporting the Moji app
 */
class BIM_Controller_Moji extends BIM_Controller_Base {

    public function invite(){
        $invited = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) ){
            $memberId = $this->resolveUserId($input->userID);
            $nonUsers = !empty( $input->nonUsers ) ? json_decode($input->nonUsers) : array();
            $users = !empty( $input->users ) ? json_decode($input->users) : array();
            $emoji = self::validateEmoji($input->emoji);
            $invited = BIM_App_Moji::invite( $memberId, $emoji, $users, $nonUsers );
        }
        return $invited;
    }

    private static function extractUsers( $users ){
        $users = explode( '|||', $users );
        $toRemove = array();
        foreach( $users as $idx => &$user ){
            $user = explode( ':::', $user );
            if( count( $user ) <= 1 ){
                $toRemove[] = $idx;
            } else if( count( $user ) == 2 ){
                $user[] = '';
            }
        }
        foreach( $toRemove as $idx ){
            unset( $users[ $idx ] );
        }
        return $users;
    }

    private static function validateEmoji( $emoji ){
        return $emoji;
    }

}
