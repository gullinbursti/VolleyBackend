<?php 
class BIM_App_Admin{
    
    public static function showIGPromoters(){
        $input = (object)( $_POST? $_POST : $_GET);
        
        $sql = "
	        select * from growth.ig_promoters 
	        where email is not null and email != ''
	        order by time desc
        ";
        
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
        
		<hr>IG Promoters - ".count( $data )."<hr>\n
		
        <table border=1 cellpadding=10>
        <tr>
        <th>Name</th>
        <th>Followers</th>
        <th>Url</th>
        <th>Email</th>
        <th>Date Found</th>
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
                	$info->followers
            	</td>
                <td>
                    <a href='$info->url'>$info->url</a>
                </td>
                <td>
                	$info->email
            	</td>
                <td>
                	$info->time
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
    
    public static function printRandomTags( ){
        if( $_POST ){
            $tags = BIM_Utils::getRandomTags();
            foreach( $tags as $tag ){
                echo $tag.' ';
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
                $shoutout = BIM_Model_Volley::create( $teamVolleyId, $hashTag, $imgUrlPrefix );
                BIM_Model_Volley::logShoutout( $shoutout->id, $volley->id, $volley->creator->id );
                BIM_Push::shoutoutPushToAll( $volley->creator->id, $shoutout->id );
                if( !empty($input->addLikes) ){
                    $likers = BIM_Model_User::getRandomIds($input->addLikes,array(),"2013-09-01");
                    $likers = BIM_Model_User::getMulti($likers);
                    foreach( $likers as $liker ){
                        $shoutout->upvote( $shoutout->creator->id, $liker->id, $imgUrlPrefix );
                    }
                }
                print_r( json_encode( $shoutout ) );
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
            BIM_Utils::copyImage( $volleyImagePath, $name );
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
            
            $teamVolleyId = empty( $input->userId ) 
                        ? BIM_Config::app()->team_volley_id 
                        : $input->userId;
            
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
    
    public static function getComments(){
        $tags = BIM_Utils::getTagsFromPool();
        $comments = array();
        $attempts = 0;
        while( $tags && count( $comments ) < 5000 && $attempts++ < 10000 ){
            $tag = $tags[ array_rand($tags) ];
            $comment = BIM_Utils::getRandomComment( $tag );
            $comments[ $comment ] = true;
        }
        foreach( $comments as $comment => $bool ){
            echo "$comment\n";
        }
    }
    
    public static function getCaptions(){
        $input = (object)( $_POST? $_POST : $_GET);
        
        if( !empty( $input->get_captions ) ){
            $totalCaptions = $input->total_captions;
            $captions = self::makeCaptions( $totalCaptions );
            echo("<pre>");
            foreach( $captions as $caption ){
                echo "$caption\n";
            }
            echo("</pre>");
        } else {
        
            echo("
            <html>
            <head>
    		<script src='http://code.jquery.com/jquery-1.10.1.min.js'></script>
            </head>
            <body>
            ");
            
            $teamVolleyId = BIM_Config::app()->team_volley_id;
            
            echo("
            Gnerate Media Captions.  
            <br>
            <br>
            Enter the number of captions you would like and click the \"Generate Captions\" button.
            <br><br>
            Caveat Emptor: 
            <blockquote>
            After you have generated your captions, 
            you must include each one of the captions
            with a media post on a network (one caption per post) 
            otherwise our workers will be dropping links to dead ends and empty pages.
            </blockquote>
    		<form method='post'enctype='multipart/form-data'>
        	Total Captions: <input type='text' size='5' name='total_captions'>
			<input type='submit' name='get_captions' value='generate captions'>
            </form>
            </body>
            </html>
            ");
        }
    }
    
    public static function makeCaptions( $totalCaptions ){
        $captions = array();
        for( $n = 0; $n < $totalCaptions; $n++ ){
            $tags = BIM_Utils::getRandomTags(5);
            BIM_Utils::saveToTagPool( $tags );
            $captions[] = self::makeCaption($tags);
        }
        return $captions;
    }
    
    /**
     * genertate captions and with each caption a tag
     */
    public static function makeCaption( $tags ){
        
        $portension = array(
            '@selfieclub is coming sooooon :) #selfieclub',
            '10 days for @selfieclub to launch #selfieclub',
            'who wants in? @selfieclub #selfieclub',
            'who wants to join? @selfieclub #selfieclub',
            'wanna join?  @selfieclub #selfieclub',
            'join?  @selfieclub #selfieclub',
            'join selfieclub?  @selfieclub #selfieclub',
            'want to join?  @selfieclub #selfieclub',
            'it is coming soon! blow this app up  @selfieclub #selfieclub',
            'help blow this app up?  @selfieclub #selfieclub',
            'blow this app up?  @selfieclub #selfieclub',
            'wnnna join?  @selfieclub #selfieclub',
            'wanna join selfieclub?  @selfieclub #selfieclub',
            'join now?  @selfieclub #selfieclub',
            'want in?  @selfieclub #selfieclub',
            'join???  @selfieclub #selfieclub',
            'join selfieclub!!!!!  @selfieclub #selfieclub',
            'omg 10 days till we launch, help blow it up!?  @selfieclub #selfieclub',
        );
        
        $cta = array(
            'kik to join: selfieclub >>>>>>',
            'kik2join: selfieclub >>>',
            'kik: selfieclub >>',
            'kik id: selfieclub >>>>',
            'kik us to join kik: selfieclub >',
            'join kik us! kikid: selfieclub >>',
            'KIK to join id: selfieclub >>>',
            'KIK2JOIN: selfieclub >>>>',
            'kik2join: selfieclub >>>>>>',
            'kik them to join kik: selfieclub >>>>',
            'kik 4 invite: selfieclub >>>>',
            'kik4invite: selfieclub >>>',
            'kik to get an invite: selfieclub >>>',
            'kik us for invite: seflieclub >>>',
            'kik us tojoin: selfieclub >>>>>',
            'kik selfieclub to join >>',
            'kik us to joinn selfieclub >>>>',
            'kik selfieclub to join >>>',
            'kik: selfieclub for invites >>>',
            'kik us: selfieclub to join >>>',
        );
        
        $portend = $portension[array_rand( $portension )];
        $callToAction = $cta[array_rand( $cta )];
        
        $tags = join(' ', $tags);
        $comment = "$portend $callToAction $tags";
        return $comment;
    }
    
    public static function createAnyVolley(){
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
        
        $teamVolleyId = $input->userId;
        $volleys = BIM_Model_Volley::getVolleys($teamVolleyId);
        $user = BIM_Model_User::get( $teamVolleyId );
        echo("
        Create a new Volley for $user->username
        <br><br>
		<form method='post'enctype='multipart/form-data'>
		<input type='hidden'' name='userId' value='$teamVolleyId'>
        	Hash Tag: <input type='text' size='100' name='hashtag'>
        	<br>
			Volley Image: <input type='file' name='image'>
			<br>
        <input type='submit' name='create_volley' value='create_volley'>
        </form>
        
		<hr> $user->username volleys - ".count( $volleys )."<hr>\n
		
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
					<form method='post' enctype='multipart/form-data'>
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
					<form method='post' enctype='multipart/form-data'>
        			<input type='hidden' name='volley_id' value='$volley->id'>
					<input type='submit' name='edit_volley' value='edit_volley'>
                </td>
                <td>
                	<img src='$img'> <a href='manage_volley_content.php?volley_id=$volley->id' target='_blank'>Manage Content</a><br>
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
    
    public static function removePic( $params ){
        $userId = $params[0];
        $imgPrefix = BIM_Utils::blowfishDecrypt($params[1]);
        BIM_Model_Volley::deleteImageByUserIdAndImage($userId, $imgPrefix);
    }
    
    public static function removeVolley( $params ){
        $ids = array($params[0]);
        BIM_Model_Volley::deleteVolleys($ids);
    }
    
    public static function suspendUser( $params ){
        $userId = trim($params[0]);
        BIM_Model_User::archiveUser($userId);
    }
    
    public static function manageVolleyContent(){
        $input = (object)( $_POST? $_POST : $_GET);
        if( empty( $input->volley_id ) ){
            echo "no volley id!";
        }
        
        foreach( $input as $prop => $value ){
            if( preg_match('@suspend_user_|remove_pic_|remove_volley_@i',$prop) ){
                $call = explode('_',$prop);
                $func = $call[0].ucwords($call[1]);
                $params = array_splice($call, 2);
                self::$func( $params );
            }
        }
        
        $volley = BIM_Model_Volley::get($input->volley_id);
        
        echo("
        <html>
        <head>
		<script src='http://code.jquery.com/jquery-1.10.1.min.js'></script>
        </head>
        <body>
        Manage Selfieclub content
        <br><br>
        <form method=POST>
        <input type='hidden' name='volley_id' value='$volley->id'>
        <table border=1 cellpadding=10>
        <tr>
        <td>
        Id: $volley->id
        <br>
        Subject: $volley->subject
        <br>
        Creator: ".$volley->creator->username."
        <br>
        <img src='".$volley->creator->img."Small_160x160.jpg'>
        <br>
        <input type='submit' value='remove volley' name='remove_volley_$volley->id'>
        <br>
        <input type='submit' value='suspend user and remove content' name='suspend_user_".$volley->creator->id."'>
        </td>
        </tr>
        </table>
        <br><br>
        <table border=1 cellpadding=10>
        <tr>
        <th>Username</th>
        <th>Image</th>
        <th>Remove Pic</th>
        <th>Suspend User</th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $volley->challengers as $challenger ){
            $img = $challenger->img.'Small_160x160.jpg';
            $hashedImg = BIM_Utils::blowfishEncrypt( $challenger->img );
            echo "
            <tr>
            <td>
                $challenger->username
            </td>
            <td>
            	<img src='$img'><br>
        	</td>
            <td>
            	<input type='submit' name='remove_pic_{$challenger->id}_{$hashedImg}' value='remove pic'>
            </td>
            <td>
            	<input type='submit' name='suspend_user_$challenger->id' value='suspend user and remove content'>
            </td>
            </tr>
            ";
        }
        echo("
        </table>
        </form>
        </body>
        </html>
        ");
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
        	
        		var likes = 0;
        		el = $('#addLikes_' + volleyId );
        		if( el ){
        			likes = el.val();
        		}
                $.ajax({
                    url: '/admin/shoutout.php?volleyId=' + volleyId + '&addLikes=' + likes,
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
                <td>$volley->id - <input type='button' onClick='shoutout($volley->id);' value='shout out'>&nbsp;Add Likes: <input type='text' size='3' id='addLikes_$volley->id'></td>
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
                <td>$volley->id - <input type='button' onClick='shoutout($volley->id);' value='shout out'>&nbsp;Add Likes: <input type='text' size='3' id='addLikes_$volley->id'></td>
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
                <td>$volley->id - <input type='button' onClick='shoutout($volley->id);' value='shout out'>&nbsp;Add Likes: <input type='text' size='3' id='addLikes_$volley->id'></td>
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
        $image = preg_replace('/\.jpg/','', $image );
        $image = preg_replace('/Large_640x1136/','', $image );
        if( !preg_match('@defaultAvatar@',$image) ){
            $image = "{$image}Small_160x160.jpg";
        }
        $volleys = BIM_Model_Volley::getVolleys($user->id);
        $imgHTML = array();
        foreach( $volleys as $volley ){
            $userPics = $volley->getUserPics( $user->id );
            foreach( $userPics as $picUrl ){
                $picUrl .= "Large_640x1136.jpg";
                $imgHTML[] = "<img src='$picUrl'>";
            }
        }
        $imgHTML = join('<br>',$imgHTML);
        
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
Username: <input type='text' name='user[username]' size='50' value='$user->username'> ($user->username)
<br><br>
Email: <input type='text' name='user[email]' size='50' value='$user->email'> ($user->email)
<br><br>
Birthdate: <input id='age' type='text' name='user[age]' size='50' value='$user->age'> ( $user->age )
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
<hr>
$imgHTML
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
