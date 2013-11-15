<?php 
class BIM_App_Admin{
    
    public static function showWebstaContacts(){
        $input = (object)( $_POST? $_POST : $_GET);
        
        $sql = '
        	select *, concat("http://web.stagram.com/p/",url) as target_img
        	from growth.webstagram_contact_log 
        	where logged > 0 
        	order by logged desc 
        	limit 300
        ';
        
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $stmt = $dao->prepareAndExecute( $sql );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        
        /**
            [time] => 1384471859
            [url] => 589314179606345900_38865605
            [type] => photo
            [comment] => gimme a shoutout? #dgoqvw
            [network] => webstagram
            [name] => RyanBr909
            [logged] => 2013-11-14 23:29:22
            [target_img] => http://web.stagram.com/p/589314179606345900_38865605
         */
        
        echo("
        <html>
        <head>
		<script src='http://code.jquery.com/jquery-1.10.1.min.js'></script>
        </head>
        <body>
        
		<hr>Latest Webstagram Contacts - ".count( $data )."<hr>\n
		
        <table border=1 cellpadding=10>
        <tr>
        <th>Bot Name</th>
        <th>Comment</th>
        <th>Target</th>
        <th>Date</th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $data as $info ){
            echo "
                <tr>
                <td>
                    $info->name
                </td>
                <td>
                	$info->comment
            	</td>
                <td>
                    <a href='$info->target_img'>$info->target_img</a>
                </td>
                <td>
                	$info->logged
            	</td>
                </tr>
            ";
        }
        echo("
        </table>
        </body>
        </html>
        ");
        exit;
    }
    
    public static function getRandomTags(){
        if( $_POST ){
            for( $n = 0; $n < 20; $n++){
                $str = str_split('abcdefghijklmnopqrstuvwxyz');
                $tag = array_rand( $str, 6 );
                foreach( $tag as &$char ){
                    $char = $str[$char];
                }
                $tag = join('',$tag);
                echo "#$tag ";
            }
        } else {
            echo "<form method='POST'><input name='submit' type='submit' value='get random tags'></form>";
        }
    }
    
    public static function shoutout(){
        $input = (object)( $_POST? $_POST : $_GET);
        if( !empty( $input->volleyId ) ){
            $volley = BIM_Model_Volley::get( $input->volleyId );
            if( $volley->isExtant() ){
                $suffix = 'Large_640x1136.jpg';
                $namePrefix = 'TV_Volley_Image-'.uniqid(true);
                $name = "{$namePrefix}{$suffix}";
                $imgUrlPrefix = "https://d1fqnfrnudpaz6.cloudfront.net/$namePrefix";
                $imgUrl = $volley->creator->img.$suffix;
                
                BIM_Utils::putImage( $imgUrl, $name );
                BIM_Utils::processImage($imgUrlPrefix);
                
                $hashTag = "#shoutout";
                $teamVolleyId = BIM_Config::app()->team_volley_id;
                $volley = BIM_Model_Volley::create( $teamVolleyId, $hashTag, $imgUrlPrefix );
                BIM_Push::shoutoutPush( $volley );
                print_r( json_encode( $volley ) );
            }
        }
    }
    
    public static function doEditVolley( $input ){
        $volley = BIM_Model_Volley::get( $input->volley_id );
        
        if( !empty( $input->hashtag ) ){
            $volley->updateHashTag( $input->hashtag );
        }
                    
        if( !empty( $_FILES['volley_img']['tmp_name'] ) ){
            $volleyImagePath = $_FILES['volley_img']['tmp_name'];
            
            $namePrefix = 'TV_Volley_Image-'.uniqid(true);
            $name = "{$namePrefix}Large_640x1136.jpg";
            $imgUrlPrefix = "https://d1fqnfrnudpaz6.cloudfront.net/$namePrefix";
            
            //echo("BIM_Utils::putImage( $volleyImagePath, $name )\n");
            BIM_Utils::putImage( $volleyImagePath, $name );
            //echo("BIM_Utils::processImage(".$volley->creator->img.")\n");
            BIM_Utils::processImage($imgUrlPrefix);
            //echo "updating image to $imgUrlPrefix\n";
            $volley->updateImage($imgUrlPrefix);
            $volley->purgeFromCache();
        }
        
        if( !empty($input->delete) ){
            $volleyIds = array($input->delete);
            BIM_Model_Volley::deleteVolleys( $volleyIds );
        }
    }
    
    public static function doCreateVolley( $input ){
        if( !empty( $_FILES['image']['tmp_name'] ) && !empty( $input->hashtag ) ){
            $teamVolleyId = BIM_Config::app()->team_volley_id;
            $imagePath = $_FILES['image']['tmp_name'];
            $namePrefix = 'TV_Volley_Image-'.uniqid(true);
            $name = "{$namePrefix}Large_640x1136.jpg";
            $imgUrlPrefix = "https://d1fqnfrnudpaz6.cloudfront.net/$namePrefix";
            
            BIM_Utils::putImage( $imagePath, $name );
            BIM_Utils::processImage($imgUrlPrefix);
            
            $hashTag = trim($input->hashtag,'#');
            $hashTag = "#$hashTag";
            BIM_Model_Volley::create( $teamVolleyId, $hashTag, $imgUrlPrefix );
        }
    }
    /**
     * if we are receiving a posted image
     * first we upload the image to s3
     * then we generate the small images
     * then we write the volley
     * 
     * and redirect the user back to the create page
     * 
     * if we do not receive a posted image
     * we just simply print the form and the volleys beneath it
     * 
     */
    
    public static function createVolley(){
        $input = (object)( $_POST? $_POST : $_GET);
        
        if( !empty( $input->edit_volley ) ){
            self::doEditVolley( $input );
        } else if( $input->create_volley ) {
            self::doCreateVolley( $input );
        }
        
        echo("
        <html>
        <head>
		<script src='http://code.jquery.com/jquery-1.10.1.min.js'></script>
        </head>
        <body>
        ");
        
        $teamVolleyId = BIM_Config::app()->team_volley_id;
        $volleys = BIM_Model_Volley::getVolleys($teamVolleyId);
        
        echo("
        Create a new Volley for Team Volley
        <br><br>
		<form method='post'enctype='multipart/form-data'>
        	Hash Tag: <input type='text' size='100' name='hashtag'>
        	<br>
			Volley Image: <input type='file' name='image'>
			<br>
        <input type='submit' name='create_volley' value='create_volley'>
        </form>
        
		<hr>Team Volley volleys - ".count( $volleys )."<hr>\n
		
        <table border=1 cellpadding=10>
        <tr>
        <th>Volley Id</th>
        <th>Image</th>
        <th>Creator</th>
        <th>Hash Tag</th>
        <th>Challengers</th>
        <th>Creation Date</th>
        <th>Last Updated</th>
        <th>Remove Volley</th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $volleys as $volley ){
            $creator = $volley->creator;
            $totalChallengers = count($volley->challengers);
            $img = $volley->getCreatorImage();
            if( $volley->isExtant() ){
                echo "
                <tr>
                <td>
                    $volley->id<br>
					<form method='post'enctype='multipart/form-data'>
        			<input type='hidden' name='volley_id' value='$volley->id'>
					<input type='submit' name='edit_volley' value='edit_volley'>
                </td>
                <td>
                	<img src='$img'><br>
					New Volley Image: <input type='file' name='volley_img'>
                	</td>
                <td>$creator->username</td>
                <td>
                    $volley->subject<br>
                    New Hash Tag:<input type='text' name='hashtag' value=''>
                </td>
                <td>$totalChallengers</td>
                <td>$volley->added</td>
                <td>$volley->updated</td>
                <td>
                	<input type='checkbox' name='delete' value='$volley->id'>
                	</form>
                </td>
                </tr>
                ";
            }
        }
        echo("
        </table>
        </form>
        </body>
        </html>
        ");
        exit;
    }
    
    /**
     * We present a formn with the top 100 voleys by date 
     * 
     * There will be a drop down that alows for changing the 
     * view using sorted by top votes
     * 
     * There will be a check box next to each volley description
     * submitting the form will cause the volley ids to go into a table
     * 
     * The discover code will select all of the ids from this table
     * and get 16 random ones and retunr the volleys like normal
     * 
     */
    
    public static function manageExplore(){
        $input = (object)( $_POST? $_POST : $_GET);
        $volleyIds = array();
        if( property_exists($input, 'volleyIds') || (!empty( $_SERVER['REQUEST_METHOD'] ) && strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' ) ){
            $volleyIds = !empty($input->volleyIds) ? $input->volleyIds : array();
            $volleyData = array();
            foreach( $volleyIds as $volleyId ){
                $volley = BIM_Model_Volley::get( $volleyId );
                if( $volley->isExtant() ){
                    $volleyData[] = $volley;
                }
            }

            // now we figure out which creators to push
            // and send them a push
            $currentVolleyIds = BIM_Model_Volley::getExploreIds();
            $volleysToPush = array_diff($volleyIds, $currentVolleyIds);
            $volleysToPush = BIM_Model_Volley::getMulti( $volleysToPush );
            BIM_Push::pushCreators( $volleysToPush );
            
            BIM_Model_Volley::updateExploreIds( $volleyData );
        } else {
            $volleyIds = BIM_Model_Volley::getExploreIds();
        }
        echo("
        <html>
        <head>
		<script src='http://code.jquery.com/jquery-1.10.1.min.js'></script>
        <script type='text/javascript'>
        	function clearAll(){
        		$('[name=\"volleyIds[]\"]').each(function(index,el){ $(el).prop('checked',false);} )
        	}
        	
            var successCallback = function( options, success, response ){ 
                console.log(options, success, response);
            };
            
            var errorCallback = function(jqXHR, errorType, exceptionObject){ 
            	console.log( jqXHR, errorType, exceptionObject ); 
    		}
            
        	function shoutout( volleyId ){
                $.ajax({
                    url: '/admin/shoutout.php?volleyId=' + volleyId,
                    dataType: 'json',
                    type: 'GET',
                    context: this,
                    success: successCallback,
                    error: errorCallback
                  });
        	}
        </script>
        </head>
        <body>
        ");
        
        $volleys = BIM_Model_Volley::getMulti( $volleyIds );
        echo "<hr><a id='current'>Currently Chosen Volleys - ".count( $volleys )."</a>&nbsp;&nbsp;<a href='#recent'>Most Recent</a>&nbsp;&nbsp;<a href='#likes'>By Likes</a><hr>\n";
        
        echo("
        <form method='POST'>
        <input type='submit'>
        <table border=1 cellpadding=10>
        <tr>
        <th>Volley Id</th>
        <th>Image</th>
        <th>Creator</th>
        <th>Hash Tag</th>
        <th>Challengers</th>
        <th>Creation Date</th>
        <th>Last Updated</th>
        <th>Display <input type='button' value='clear' onClick='clearAll();'></th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $volleys as $volley ){
            $creator = $volley->creator;
            $totalChallengers = count($volley->challengers);
            $img = $volley->getCreatorImage();
            $checked = in_array( $volley->id, $volleyIds ) ? ' checked ' : '';
            if( $volley->isExtant() ){
                echo "
                <tr>
                <td>$volley->id - <input type='button' onClick='shoutout($volley->id);' value='shout out'></td>
                <td><img src='$img'></td>
                <td>$creator->username</td>
                <td>$volley->subject</td>
                <td>$totalChallengers</td>
                <td>$volley->added</td>
                <td>$volley->updated</td>
                <td><input type='checkbox' $checked name='volleyIds[]' value='$volley->id'></td>
                </tr>
                ";
            }
        }
        echo("
        </table>
        ");
        // $volleys = BIM_Model_Volley::getTopVolleysByVotes();
        $volleys = BIM_Model_Volley::getTopVolleysByVotes( 86400 * 120, 500 );
        //$rem = array();
        //$volleyArr = $volleys;
        //foreach( $volleyArr as $idx => $volley ){
          //  if( in_array( $volley->id, $volleyIds ) ){
            //    unset( $volleys[ $volley->id ] );
            //}
        //}
        // $volleys = array_diff( $volleys, $volleyIds );
        echo "<hr><a id='likes'>Top Volleys By Likes - ".count( $volleys )."</a>&nbsp;&nbsp;<a href='#current'>Current</a>&nbsp;&nbsp;<a href='#recent'>Most Recent</a><hr>\n";
        
        echo("
        <table border=1 cellpadding=10>
        <tr>
        <th>Volley Id</th>
        <th>Image</th>
        <th>Creator</th>
        <th>Hash Tag</th>
        <th>Challengers</th>
        <th>Creation Date</th>
        <th>Last Updated</th>
        <th>Display <input type='button' value='clear' onClick='clearAll();'></th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $volleys as $volley ){
            $creator = $volley->creator;
            $totalChallengers = count($volley->challengers);
            $img = $volley->getCreatorImage();
            $checked = in_array( $volley->id, $volleyIds ) ? ' checked ' : '';
            if( $volley->isExtant() ){
                echo "
                <tr>
                <td>$volley->id - <input type='button' onClick='shoutout($volley->id);' value='shout out'></td>
                <td><img src='$img'></td>
                <td>$creator->username</td>
                <td>$volley->subject</td>
                <td>$totalChallengers</td>
                <td>$volley->added</td>
                <td>$volley->updated</td>
                <td><input type='checkbox' $checked name='volleyIds[]' value='$volley->id'></td>
                </tr>
                ";
            }
        }
        echo("
        </table>
        ");
        
        $v = new BIM_App_Votes();
        $volleys = $v->getChallengesByCreationTime( 500 );
        echo "<hr><a id='recent'>Most Recent Volleys - ".count( $volleys )."</a>&nbsp;&nbsp;<a href='#current'>Current</a>&nbsp;&nbsp;<a href='#likes'>By Likes</a><hr>\n";
        
        echo("
        <table border=1 cellpadding=10>
        <tr>
        <th>Volley Id</th>
        <th>Image</th>
        <th>Creator</th>
        <th>Hash Tag</th>
        <th>Challengers</th>
        <th>Creation Date</th>
        <th>Last Updated</th>
        <th>Display</th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $volleys as $volley ){
            $creator = $volley->creator;
            $totalChallengers = count($volley->challengers);
            $img = $volley->getCreatorImage();
            $checked = in_array( $volley->id, $volleyIds ) ? ' checked ' : '';
            if( $volley->isExtant() ){
                echo "
                <tr>
                <td>$volley->id - <input type='button' onClick='shoutout($volley->id);' value='shout out'></td>
                <td><img src='$img'></td>
                <td>$creator->username</td>
                <td>$volley->subject</td>
                <td>$totalChallengers</td>
                <td>$volley->added</td>
                <td>$volley->updated</td>
                <td><input type='checkbox' $checked name='volleyIds[]' value='$volley->id'></td>
                </tr>
                ";
            }
        }
        echo("
        </table>
        ");
         echo("
        <input type='submit'>
        </form>
        </body>
        </html>
        ");
        exit;
    }
    
    public static function getSearchUserForm(){
        return "
<html>
<head>
    <link rel='stylesheet' href='http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css'>
    <script src='http://code.jquery.com/jquery-1.9.1.js'></script>
	<script src='http://code.jquery.com/ui/1.10.3/jquery-ui.js'></script>
</head>
<body>
<form method=post>
Find a user: <input type='text' name='search' size='75'> <input type='submit' value='Get User'>
</form>
</body>
</html>
        ";
    }
    
    public static function getEditUserForm( $user, $errors = null ){
        $image = $user->getAvatarUrl();
        $image = preg_replace('/\.jpg/','', $user->img_url );
        $image = preg_replace('/Large_640x1136/','', $image );
        $image = "{$image}Small_160x160.jpg";
        
        return "
<html>
<head>
<!--
    <link rel='stylesheet' href='http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css'>
	<script src='http://code.jquery.com/jquery-1.9.1.js'></script>
	<script src='http://code.jquery.com/ui/1.10.3/jquery-ui.js'></script>
-->
</head>
<body>
<h4>
Editing: $user->username - id: $user->id
</h4>
<form method=post enctype='multipart/form-data'>
Username: <input type='text' name='user[username]' size='50' value='$user->username'>
<br><br>
Email: <input type='text' name='user[email]' size='50' value='$user->email'>
<br><br>
Birthdate: <input id='age' type='text' name='user[age]' size='50' value='$user->age'>
<!--
<div id='datepicker'></div>
<script>
$( '#datepicker' ).datepicker();
$( '#datepicker' ).datepicker('setDate', '$user->age' );
$( '#datepicker' ).datepicker('option','showOtherMonths' );
$( '#datepicker' ).datepicker('option','stepMonths' );
$( '#datepicker' ).datepicker('option','onSelect', function(text,o){ $('#age').val(text) } );
</script>
-->
<br><br>
Avatar Image: <input type='file' name='avatar'>
<br>
<img src='$image'>
<br>
<br>
<input type='hidden' value='$user->id' name='user[id]'>
<input type='submit' value='edit user'>
</form>
</body>
</html>
        ";
    }
    
    public static function validateUserData( $input ){
        $errors = (object) array('ok' => true, 'fields' => (object) array() );
        if( !empty( $input->username ) ){
            if( BIM_Model_User::usernameExists( $input->username ) ){
                $errors->fields->username = $input->username;
            }
        }
        
        if( !empty( $input->email ) ){
            if( !filter_var( $input->email, FILTER_VALIDATE_EMAIL) 
                || BIM_Model_User::emailExists( $input->username ) )
            {
                $errors->fields->email = $input->email;
            }
        }
        
        if( !empty( $input->age ) ){
            // make sure the date is valid
            $date = date_parse( $input->age );
            if( empty($date['month']) || empty($date['day']) ||  empty( $date['year'] ) ){
                $errors->fields->age = $input->age;
            } else if( !checkdate( $date['month'], $date['day'], $date['year'] ) ){
                $errors->fields->age = $input->age;
            }
        }
        
        // check that the birthdate is a valid date
        return $errors;
    }
    
    public static function updateUser( $user, $update ){
        $setSql = array();
        $params = array();
        if( !empty( $update->username ) ){
            $setSql[] = "username = ?";
            $params[] = $update->username;
            $user->username = $update->username;
        }
        
        if( !empty( $update->email ) ){
            $setSql[] = "email = ?";
            $params[] = $update->email;
            $user->email = $update->email;
        }
        
        if( !empty( $update->age ) ){
            $setSql[] = "age = ?";
            $d = new DateTime( $update->age );
            $age = $d->getTimestamp();
            $params[] = $age;
            $user->age = $update->age;
        }
        
        if( !empty( $update->img_url ) ){
            $setSql[] = "img_url = ?";
            $params[] = $update->img_url;
            $user->img_url = $update->img_url;
        }
        
        $params[] = $user->id;
        
        $setSql = join(',', $setSql );
        
        $sql = "update `hotornot-dev`.tblUsers set $setSql where id = ?";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        
    }
    
    public static function handleUserImage( $imgPath ){
        $suffix = 'Large_640x1136.jpg';
        $namePrefix = 'profile-'.uniqid(true);
        $name = "{$namePrefix}{$suffix}";
        $imgUrlPrefix = "https://d3j8du2hyvd35p.cloudfront.net/$namePrefix";
        BIM_Utils::putImage( $imgPath, $name, 'hotornot-avatars' );
        BIM_Utils::processImage( $imgUrlPrefix, 'hotornot-avatars'  );
        return $imgUrlPrefix;
    }
    
    public static function manageUser(){
        $input = (object) ( $_POST ? $_POST : $_GET );
        if( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'get' ){
            //prin the form to search for a user
            echo self::getSearchUserForm();
        } else if( !empty($input->search)  ) {
            // print the user details
            $input->search = trim($input->search);
            $user = BIM_Model_User::getByUsername($input->search);
            echo self::getEditUserForm( $user );
        } else if( !empty($input->user)  ){
            $errors = self::validateUserData( $input->user );
            if( $errors->ok ){
                $user = BIM_Model_User::get( $input->user['id'] );
                if( !empty($_FILES['avatar']['tmp_name'] ) ){
                    $input->user['img_url'] = self::handleUserImage( $_FILES['avatar']['tmp_name']);
                }
                self::updateUser( $user, (object) $input->user );
                $user->purgeFromCache();
            }
            echo self::getEditUserForm( $user, $errors );
        }
    }
}