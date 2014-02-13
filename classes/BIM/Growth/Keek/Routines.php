<?php

class BIM_Growth_Keek_Routines extends BIM_Growth_Keek{
    
    protected $persona = null;
    protected $oauth = null;
    protected $oauth_data = null;
    protected static $personaNames = null;
    
    public static function doLikesAndFollows(){
        $routines = new self( 'getvolleyapp' );
        $routines->browseTags();
    }
    
    public function __construct( $persona ){
        $this->persona = new BIM_Growth_Persona( $persona );
        
        $this->instagramConf = BIM_Config::instagram();
        $clientId = $this->instagramConf->api->client_id;
        $clientSecret = $this->instagramConf->api->client_secret;
        
        //$this->oauth = new OAuth($conskey,$conssec);
        //$this->oauth->enableDebug();
    }
    
    /**
     *  pk	505068195439511399_25025320
		t	9432
     */
    public function like( $id, $name = '' ){
        $url = 'http://web.stagram.com/do_like/';
        $params = array(
            'pk' => $id,
            't' => mt_rand(5000, 10000)
        );
        
        $headers = array(
        	'Origin: http://web.stagram.com',
			'X-Requested-With: XMLHttpRequest',
            'Content-Type: application/x-www-form-urlencoded',
        );
        
        if( $name ){
			$headers[] = "Referer: http://web.stagram.com/n/$name/";
        } else {
			$headers[] = "Referer: http://web.stagram.com/";
        }

        $response = $this->post( $url, $params, false, $headers );
        $response = json_decode( $response );
        print_r( $response );
        if( empty( $response->status ) || $response->status != 'OK' ){
            $msg = "cannot like photo using id : $id with persona: ".$this->persona->instagram->username;
            echo "$msg\n";
            $this->sendWarningEmail( $msg );
        }
    }
    
    /**
     * http://web.stagram.com/do_follow/
     * 
     * 
       request 
       
           pk	25025320
    	   t	5742
	   
	   response:
            {
                "status": "OK",
                "message": "follows"
            }	   
	   
     */
    /**
     * 
     * @param int $id - id of the target user for the follow
     * @param string $name - the name of the target user for the follow
     */
    public function follow( $id, $name = '' ){
        $time = time();
        $url = "http://web.stagram.com/do_follow/?$time";
        $params = array(
            'pk' => $id,
            't' => mt_rand(0, 10000)
        );
        $headers = array(
        	'Origin: http://web.stagram.com',
			'X-Requested-With: XMLHttpRequest',
            'Content-Type: application/x-www-form-urlencoded',
        );
        
        if( $name ){
			$headers[] = "Referer: http://web.stagram.com/n/$name/";
        } else {
			$headers[] = "Referer: http://web.stagram.com/";
        }

        $response = $this->post( $url, $params, false, $headers );
        $response = json_decode( $response );
        print_r( array($this->persona->name, $name, $response) );
        if( empty( $response->status ) || $response->status != 'OK' ){
            $msg = "cannot follow user $name using id : $id with persona: ".$this->persona->name;
            $msg = "$msg ".print_r( $response, 1 );
            error_log($msg);
            $this->sendWarningEmail( $msg );
        }
    }
    
    /**
     * This routine does the following for each persona
     * 
     * login
     * hit feed
     * generate a random number between 5 - 20
     * 
     * get between 10 - 20 images
     * 
     * 		get each image on feed pages
     * 		check to see if we have liked the image
     * 		if not, then generate a random number to see if we will like it
     * 
     * if we exhaust our feed and still have not collected 20 images, 
     * then we search using a random popular tag and browse that until 
     * we found all of our images to like.  if we do not find all of them
     * then we go to the next popular tag and 
     * repeat until we have found all of the images we want to like
     * 
     * once we have found them all
     * 
     * or alternatively, we can simply space out the likes that we get over the next week
     * 
     * 
     * h1 - 6(6) workers - 6 likes - nh3,5,7,9,11
     * h2 - 6(12) workers - 6 likes - nh4,6,8,10,12
     * h3 - 6(18) workers - 12 likes - nh5,7,9,11
     * h4 - 6(24) workers - 12 likes - nh6,8,10,12
     * h5 - 6(30) workers - 18 likes - nh7,9,11,13
     * h6 - 7(36) workers - 18 likes - nh8,10,12,14
     * h - 7(42) workers - 24 likes - nh8,10,12,14
     * h - 7(48) workers - 24 likes - nh8,10,12,14
     * h - 7(54) workers - 30 likes - nh8,10,12,14
     * h - 7(60) workers - 30 likes - nh8,10,12,14
     * h - 7(66) workers - 36 likes - nh8,10,12,14
     * h12 - 7(72) workers - 36 likes - nh8,10,12,14
     * 
     *
     * page that lists the selfies from Keek hundreds at a time
     * 
     * text
     * select an IG account
     * 
     * top of page will be a set of fields that can be set as  a shoutout
     * then the list can be browsed and shoutouts sent
     * 
     * text field for message
     * dropdown which account
     * text field for the number of shoutouts
     * filter for which users to target (min followers max followers, min following, max following)
     * 
     * 
     * 
     * 
     */
    // generate the job times over the next 2 weeks
    /*
     * foreach persona
     * 
     * foreach each site
     * 
     * generate a random time between now and x seconds from now
     * 
     * then resolve the time to the closest minute and use that for job time
     * 
     * then create the job
     * 
     * the job will be to like the web property id in the job
     */
    public static function queueFollowJobs(){
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
	    $sql = "select name from growth.persona where network = 'instagram'";
		$stmt = $dao->prepareAndExecute( $sql );
		$personaNames = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
		
        $accountNames = array(
        	'rateselfie',
        	'shoutoutselfiez',
            'letsvolley',
            'volleyapp',
            'teamvolleyapp',
            'cutetumblrselfies',
            'weheartitselfies',
            'theselfiecontest',
            'shoutoutselfiecontest',
            'famousselfies',
        );
        $minutes = 1440 * 10;
        $time = time();
        
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('UTC') );
        foreach( $accountNames as $accountName ){
            foreach( $personaNames as $name ){
                $whichMinute = mt_rand( 1, $minutes );
                $seconds = $whichMinute * 60;
                $targetDate = $time + $seconds;
                $date->setTimestamp($targetDate);
                $targetDate = $date->format('Y-m-d H:i:s');
                echo "$name, $accountName - $targetDate\n";
                self::queueHouseFollow($name, $accountName, $targetDate);
            }
        }
    }
    
    public static function queueBlastJobs(){
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
	    $sql = "select name from growth.persona where network = 'instagram'";
		$stmt = $dao->prepareAndExecute( $sql );
		$personaNames = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
		
        $usersToContactPerPersona = 20;
        $daysToRun = 2;
        
        $minutes = 1440 * $daysToRun;
        $time = time();
        
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('UTC') );
        foreach( $personaNames as $name ){
            for($n = 0; $n < $usersToContactPerPersona; $n++ ){
                $whichMinute = mt_rand( 1, $minutes );
                $seconds = $whichMinute * 60;
                $targetDate = $time + $seconds;
                $date->setTimestamp($targetDate);
                $targetDate = $date->format('Y-m-d H:i:s');
                echo "$name - $targetDate\n";
                self::queueBlastJob($name, $targetDate);
            }
        }
    }
    
    public static function queueBlastJob($personaId, $targetDate = null, $disabled = 0 ){
        if( !$targetDate ){
            $targetDate = new DateTime();
            $targetDate->setTimezone(new DateTimeZone('UTC') );
            $targetDate = $targetDate->format( 'Y-m-d H:i:s' );
        }
        $job = (object) array(
            'nextRunTime' => $targetDate,
            'class' => 'BIM_Jobs_Growth',
            'method' => 'doBlastJob',
            'name' => 'do_blast_job',
            'params' => array(
                'persona_id' => $personaId
            ),
            'is_temp' => true,
            'disabled' => $disabled
        );
        
        $j = new BIM_Jobs_Gearman();
        $j->createJbb($job);
    }
    
    public static function queueHouseFollow($personaId, $accountName, $targetDate = null ){
        if( !$targetDate ){
            $targetDate = new DateTime();
            $targetDate = $targetDate->format( 'Y-m-d H:i:s' );
        }
        $job = (object) array(
            'nextRunTime' => $targetDate,
            'class' => 'BIM_Jobs_Growth',
            'method' => 'doHouseFollow',
            'name' => 'do_house_follow',
            'params' => array(
                'house_account_id' => $accountName,
                'persona_id' => $personaId
            ),
            'is_temp' => true,
            'disabled' => 1
        );
        
        $j = new BIM_Jobs_Gearman();
        $j->createJbb($job);
    }
    
    /**
     * generate a random tag
     * generate a unique image
     * post the unique image with the tag in the caption
     * find a random user from one of our tags
     * drop a comment with the tag
     */
    public static function doBlastJob( $personaId, $user = null, $comment = null ){
        $me = new self( $personaId );
        if( $me->handleLogin() ){
            $tag = self::getUniqueTag();
            
            //$caption = self::getRandomCaption( $tag );
            //$pic = self::getUniqueMedia();
            //$me->postMedia( $pic, $caption );
            if( !$comment ){
                $comment = BIM_Utils::getRandomComment( $tag );
            }
            
            if( !$user ){
                $user = self::getRandomUser();
                error_log("got random user: ".$user->username);
            }
            self::sleep( 2, "before follow" );
            $me->follow($user->id, $user->name);
            $me->commentOnLatestPhoto( $user->url, $comment, true );
            // $me->likeXPhotos( $user->url, mt_rand(2,5) );
        }
        if( $user ){
            self::releaseUser($user->id);
        }
    }

    public function likeXPhotos( $pageUrl, $total ){
        $photoIds = $this->getXPhotos($pageUrl, $total);
        foreach( $photoIds as $photoId ){
            $this->like( $photoId );
        }
    }
    // type="image" name="comment__166595034299642639_37459491"
    public function getXPhotos( $pageUrl, $total ){
        if( $total <= 0 ){
            $total = 1;
        }
        $ids = array();
        $ptrn = '/type="image" name="comment__(.+?)"/';
        $attempts = 0;
        while( $pageUrl && $attempts++ < 10 && count( $ids ) < $total ){
            $response = $this->get( $pageUrl );
            preg_match_all($ptrn, $response, $matches);
            if( isset( $matches[1] ) ){
                array_splice( $ids, count( $ids ),  0, $matches[1] );
            }
            if( count( $ids ) < $total ){
                // we try and get an earlier page
                $pageUrl = self::getEarlierUrl($response);
                if( $pageUrl ){
                    $sleep = 1;
                    echo "sleeping for $sleep seconds before getting more pics from $pageUrl in function ".__FUNCTION__."\n";
                    sleep( $sleep );
                }
            }
        }
        if( count( $ids ) > $total ){
            array_splice( $ids, $total );
        }
        return $ids;
    }
    
    public static function getRandomUser(){
        $randomUser = null;
        $attempts = 0;
        while( !$randomUser && $attempts++ < 100 ){
            mt_srand(abs(crc32(uniqid())) + ( (int) getmypid() ) );
            $time = time() - mt_rand(0, 86400 * 60);
            $selfieUrl = "http://web.stagram.com/tag/selfie/?npk=$time";
            
            echo "getting $selfieUrl\n";
            
            $g = new BIM_Growth_Keek();
            $response = $g->get( $selfieUrl );
            $users = self::getUsersFromText($response);
            foreach( $users as $user ){
                if( self::canDoPing( $user->id ) ){
                    $randomUser = $user;
                    break;
                }
            }
            if( !$randomUser ){
                $sleep = 5;
                error_log("sleeping for $sleep seconds") ;
                sleep( $sleep );
            }
        }
        
        return $randomUser;
    }
    
    // returns a unique 6 letter string
    public static function getUniqueTag(){
        $tags = array(
            '#vvoyac', '#qlhmgm', '#dsyvqp', '#sudzba', '#blymew', '#jgkkgj', '#qwxset', '#chqprg', '#evjtof', '#siooaq', '#jxqfbn', '#eyrmfj', '#cydipe', '#djicwv', '#hmhbjj', '#rbiwbs', '#xdoyjn', '#kmxiln', '#wosbuv', '#orgfvz',            
            '#dehmap', '#lnemjz', '#ycivwy', '#aljjpf', '#rdarko', '#bwvqdl', '#vxxeuw', '#xvpito', '#kxsiwj', '#nqoquo', '#iluawy', '#hfjpza', '#yutlmb', '#ksczet', '#nwvpsl', '#sffqdv', '#npbsqq', '#lwlemd', '#fcbaiz',
            '#podhxs', '#yhzzvg', '#iprjba', '#xebanj', '#cnsjsj', '#hovsiu', '#eltnvq', '#sttjpi', '#ihospm', '#zfewbq', '#hbopfz', '#jvfcnt', '#nhezal', '#ryemma', '#jfpdgg', '#jnlqki', '#rrlrje', '#iidqoo', '#arximt', '#pedxcg',
            '#vntrwm', '#nfquox', '#rvnnfo', '#xkspvf', '#pxdczg', '#hpzgfi', '#emwgau', '#ajbukt', '#oommga', '#remibq', '#bevvei', '#iryqhw', '#qdrcbz', '#niseqc', '#wcmpqo', '#nvdvuo', '#deudll', '#cdssri', '#lfvpnf', '#zxnybm',
            '#vmvqis', '#ynwlba', '#sswpjw', '#xtkpdw', '#qnmkpc', '#wrdbgi', '#pbtvwr', '#fkvryv', '#psuskk', '#ojaaky', '#vfqkcp', '#bgtfgw', '#dackgb', '#rgxcdn', '#vwnqim', '#yienen', '#tgowef', '#izfqmx', '#wikaip', '#vhprln',
            '#icaull', '#znntuc', '#vfmnmf', '#lvccck', '#lphxwu', '#oclqbj', '#xhnxan', '#vncvcq', '#jwstqb', '#kynngp', '#wtojww', '#nnceiz', '#rjfqvf', '#balyhf', '#taxuda', '#tmwamt', '#xsxfeq', '#bjugnt', '#loqzzp', '#amhkqr',
            '#kqbswf', '#neayno', '#hjhqdd', '#gyoftt', '#vebfrn', '#hxfjzx', '#ahhten', '#tlderz', '#svzvon', '#srwyhr', '#bxguyg', '#jxojrn', '#llnxyy', '#ukjxxk', '#asiynx', '#mvdlte', '#ynerqc', '#eqrlcy', '#korzmr', '#aonses',
            '#oqwhzj', '#lycpuk', '#uimftg', '#vfdowj', '#vcnwqh', '#dxqnfk', '#eqkckv', '#aacmtz', '#fsteuw', '#srnxxp', '#jmnbvp', '#jagtgh', '#cpxhzh', '#djeqbz', '#mukdqr', '#edlxwd', '#unjtsf', '#fceljo', '#lkqfnf', '#reyjto', 
            '#gertyn', '#aypdvw', '#twjmjt', '#tfclys', '#gaprvb', '#lnrklh', '#drbrjc', '#yywwop', '#mnyzaj', '#nyicrp', '#zffyqt', '#kgaifg', '#jtuukr', '#zalpag', '#wmfizb', '#oqaojv', '#znrnfa', '#vjcmdj', '#kqpeld', '#vxjepg',
            '#qorzdg', '#ribmxi', '#lnpzbc', '#osvosa', '#hvmkhl', '#yneoxz', '#txfjnv', '#gugcrp', '#xvtqmm', '#fjqepq', '#asiogi', '#wcwduf', '#yhpitw', '#feboka', '#leajpj', '#wjizyh', '#tgztvl', '#ygsurf', '#llhtcq', '#tmymxu',
            '#qrzqac', '#dlqknf', '#tilquv', '#dummst', '#luarnl', '#rnkzsw', '#nozohv', '#hshrnu', '#rihccp', '#sryhki', '#gywetf', '#pkeriz', '#evapwz', '#telfih', '#anlybb', '#etorfo', '#acxkdv', '#skfbdi', '#lsvvls', '#npuhja',
            '#tipvrg', '#lnhoje', '#ljgyqv', '#fcoqzf', '#bjiuvk', '#xudelo', '#vptuiu', '#eksbyr', '#aikzuf', '#sjjlxe', '#fhbpqw', '#zbpdzc', '#ffhflw', '#gwnfxx', '#adctou', '#ltemcf', '#cwjzve', '#iiqqzm', '#jiaqli', '#zvgwrc',
            '#mqvnip', '#gexjlm', '#cyuroo', '#jxytvh', '#nnyeal', '#pzkfxs', '#lhqpll', '#cxseqd', '#wnqlzw', '#bnteqe', '#xmmbhx', '#eqlcoy', '#ojlzek', '#reywbk', '#kwwfho', '#ecfgvs', '#ruxotq', '#cwmijt', '#ghmbuv', '#choznu',
            '#cvpifj', '#ffnahr', '#zyygen', '#umbhme', '#csvkyy', '#mdpaot', '#lkmkyv', '#kynfpa', '#cnzwzs', '#kolnbe', '#yaohqu', '#ojyfdy', '#whyscq', '#ynpips', '#skrsjv', '#xexgbq', '#fcczoz', '#gonnfn', '#lpxpol', '#tmkjji',
            '#ehhqkc', '#opysmu', '#hedjwe', '#uoizgk', '#vbvrmc', '#hkcsjv', '#dexqaa', '#uhqbzo', '#pdspdm', '#uttbbo', '#gkoipq', '#dzfjhn', '#bzsotq', '#qtanjl', '#nozitq', '#qmuvnn', '#pnhtqb', '#xycvqo', '#ngimwg', '#bpbelv',
            '#cfhkzt', '#mwbule', '#gzqpfp', '#eyccqz', '#irtbns', '#wxqlnq', '#dyztoh', '#ojllpk', '#gufaak', '#lpjlfc', '#ufhsbq', '#gylvvs', '#ijgiym', '#waorrp', '#fwecjj', '#alpwlp', '#qosnbg', '#milfuu', '#akuist', '#iwbwdf',
        	'#wvemyx', '#updnwx', '#risjzn', '#cyogyr', '#dveofd', '#bacjpe', '#aapund', '#azlatz', '#wnqbdr', '#dsolym', '#vovhyz', '#iueovp', '#mpymdy', '#zwvxkb', '#qrtjrq', '#fgndux', '#vsnndd', '#uyeyqw', '#sgfbcx', '#nllugf',
            '#zxndgf', '#ewlsoc', '#wtaqyu', '#utomea', '#fokrqp', '#rervna', '#qbrokv', '#jypupf', '#cqzkng', '#wkqvaj', '#ofkwpe', '#bhknwv', '#sdlrjm', '#ewrpka', '#tvtcob', '#gkjccr', '#tugdug', '#hhylke', '#grzofi', '#itbbsc',
            '#xmdwny', '#moeduc', '#cxaccz', '#pzneyl', '#npuglp', '#cjggtc', '#ynfzas', '#btsvbu', '#klmhzw', '#tcrkxk', '#yshjoj', '#gmxuar', '#phzpob', '#jlkikf', '#mzlzbc', '#nmkrxl', '#vrrduu', '#oidtfx', '#mjjddz', '#dpuihz',
        );
        
        /*
        $tags = array(
            '#wvemyx', '#updnwx', '#risjzn', '#cyogyr', '#dveofd', '#bacjpe', '#aapund', '#azlatz', '#wnqbdr', '#dsolym', '#vovhyz', '#iueovp', '#mpymdy', '#zwvxkb', '#qrtjrq', '#fgndux', '#vsnndd', '#uyeyqw', '#sgfbcx', '#nllugf',
            '#russsy', '#yclzom', '#uvihtf', '#mnpbwa', '#dcflft', '#nwtcsy', '#ezgmfn', '#oatxih', '#qtbwuf', '#lwornn', '#gviwwy', '#evjziu', '#pgrxiy', '#vtwhjd', '#wqlqrv', '#zlbxnr', '#eminxf', '#hevlch', '#gieouu', '#ttrmio',
            '#xncbzq', '#sigroh', '#qsajew', '#qqyqax', '#fzvywf', '#gfjjjn', '#gwcinc', '#cxvlyr', '#mwllrs', '#mfrdxc', '#rffzid', '#rwzzej', '#jvrmbk', '#xqjnkz', '#iuucft', '#ryvyqu', '#sowbxs', '#egaofl', '#gfaysn', '#vtfolw',
            '#xjuhmz', '#xogpou', '#fyvgov', '#bytyvk', '#rxesab', '#lvahkw', '#hmuzrb', '#wcxpgn', '#kyqrre', '#dkjrpn', '#ndvnyf', '#bwbaxk', '#xwozlw', '#wwqukr', '#zchuys', '#kgfltb', '#rnoyiu', '#rbirom', '#awywsz', '#ifhdry',
            '#ausckm', '#qzlqog', '#ezsjnh', '#rpjids', '#ziqfwm', '#frwnsu', '#dpibhj', '#gfqmpb', '#klpvth', '#igfehb', '#robtvh', '#zpwgay', '#gtjdfr', '#yrezzg', '#qszqhn', '#ashevs', '#olqlzs', '#qonyoc', '#ngrqzt', '#gtarfi',
            '#cfhkzt', '#mwbule', '#gzqpfp', '#eyccqz', '#irtbns', '#wxqlnq', '#dyztoh', '#ojllpk', '#gufaak', '#lpjlfc', '#ufhsbq', '#gylvvs', '#ijgiym', '#waorrp', '#fwecjj', '#alpwlp', '#qosnbg', '#milfuu', '#akuist', '#iwbwdf',
            '#tbvhxk', '#kdvofe', '#zifdyp', '#atmemf', '#epwehy', '#yodvwn', '#wghepy', '#aqakjn', '#fnoyff', '#qdfcmx', '#ewgyxr', '#ttgasz', '#pzqidk', '#kkgugd', '#kflxku', '#tkcumh', '#hvmpbp', '#ehxnxs', '#tyqwwh', '#vpdamd',
            '#ogdzjz', '#cfskpg', '#arkybv', '#grnhvn', '#halqjy', '#mkhmrv', '#rarqdi', '#uozxzg', '#ljovla', '#elwuxc', '#fbgmcb', '#mirgtg', '#wolwtp', '#nwgpeh', '#vlfjyd', '#onybnx', '#garuwo', '#rxwocq', '#glaevc', '#pmfaxo',
            '#ykeijs', '#fsazgu', '#fdkxeb', '#gdpflv', '#ksfpez', '#cibsjw', '#wjhlpm', '#ozxngv', '#itnoib', '#covota', '#lhjsgv', '#yhplya', '#qmlzdl', '#gqlpab', '#uwufre', '#lyfnoj', '#kimfba', '#dqmcth', '#ckjotk', '#revwix',
            '#nuxqmi', '#eydysr', '#ajziqu', '#xzvvuj', '#qfzwvw', '#hglrsy', '#rxgjmg', '#vqluov', '#hvxgfj', '#vuilpw', '#tpxhdj', '#efssbs', '#vfzpda', '#twnwqu', '#qicmgy', '#pvibui', '#uxrhlg', '#qraqlt', '#qknnuv', '#mdoyzm',
            '#gucsyx', '#omhyxv', '#tkunna', '#rhfjba', '#asztlr', '#inlqbk', '#kbwkzd', '#mlgvph', '#diwaor', '#nxgukt', '#uyckbh', '#oozhkr', '#edqxij', '#jywaul', '#jyqqsu', '#nfvamh', '#ztxxvx', '#qhplgc', '#wifpwq', '#slpbyc',
            '#cmzymc', '#lnswgh', '#urbdee', '#nckjhs', '#pkiykr', '#amvzqw', '#liyfje', '#rwqsmn', '#erflvs', '#dqgesh', '#mauqwt', '#todrnr', '#zdtmby', '#ysrjji', '#pyaosm', '#tojonl', '#xnkbnn', '#cofpqa', '#cnbjvm', '#zwggbp',
            '#jebpux', '#jdhwks', '#xxujcj', '#iszkfa', '#aiyuus', '#vqsmaa', '#htsbog', '#zbcbls', '#hpvnqf', '#jcxstg', '#jljrjc', '#fqgtid', '#tobfpt', '#pocyzf', '#tbncqu', '#uftypp', '#qkcbsh', '#ynpazz', '#ycynqy', '#ymicrq',
            '#uiiicr', '#zhjedw', '#sknolc', '#kwgpth', '#hpyfwc', '#kwlamg', '#fmsmna', '#cnsxkl', '#eaoypn', '#trdblt', '#xqsleh', '#xnfycw', '#ckoujm', '#rntdzh', '#cxfgle', '#bdijen', '#hhlced', '#joksgx', '#bmrduo', '#qsntdc',
            '#nbmzyr', '#vfztmt', '#jpzhgb', '#azxlhe', '#zpfrqc', '#rraqlw', '#psexoo', '#anjzdr', '#zhjwzf', '#nmfnhc', '#qzgjoh', '#ocnhuq', '#qehbds', '#tqhxpa', '#svfiuv', '#gqxxyf', '#bkpobp', '#vspmga', '#oralla', '#nzzwry',
            '#arbpny', '#llgnyd', '#zqmgws', '#tjpyam', '#bzcroi', '#iiiytg', '#orabcl', '#cdlcfo', '#xrotjo', '#dkmizj', '#ekahuj', '#oyagbi', '#atkyhz', '#epcszz', '#lweuvu', '#mupjdh', '#jqcbvr', '#viwtig', '#fnupox', '#mfmsps',
            '#aefvtb', '#eyfqhc', '#huxtgl', '#wzdkaq', '#lptbiz', '#jagxij', '#itnrbn', '#zfcqjh', '#sdhuui', '#gpuslw', '#brobqd', '#zelmcf', '#bxtaly', '#thmkje', '#irwscg', '#inizim', '#azvwqe', '#eyszrf', '#ffpebg', '#zcieuz',
        );
        */

        /*
        $tags = array(
            '#jjfqee', '#pxwjll', '#fjcqfy', '#glxlly', '#lovhnw', '#nndnrx', '#dnjzgo', '#hrddfg', '#vdziff', '#lajvkz', '#etgpec', '#cuacsw', '#pbofls', '#ixmmgi', '#wrpsyr', '#qoeaqu', '#uxymvp', '#inojao', '#nokmpj', '#sumhcd',
            '#xnljdm', '#zythwr', '#nmxypw', '#hgnegk', '#lkyteh', '#xjkbgq', '#bgrjox', '#fzioaq', '#nstcec', '#ibwufz', '#stxoxo', '#etspnt', '#npllzk', '#bukxcd', '#ogfspc', '#vlrfrt', '#hniwdy', '#oiefqo', '#yaugcx', '#joihpt',
            '#ilyusj','#nmeupx','#fbwwji','#orzcvh','#oefnbz','#rgeond','#uqccvz','#tenyox','#sqhrwn','#kuhacr','#vnrehz','#tjsmxh','#irhnbv','#tuirqw','#qjsxmu','#evswmi','#ynrtqo','#muezqu','#brgjku','#fvxxli',
            '#hkeaak','#nyysul','#yruizi','#klmeym','#avpltz','#qhiaxt','#scmuee','#ezukkr','#hhdpfc','#dvsdvj','#lnpspu','#yazcgd','#sgezfq','#atxkun','#peuewb','#fgjsoh','#dhekcc','#tqtljb','#caahjg','#wyfwuq',
            '#xbcgby','#rnzgwt','#jnkqty','#sfdsbp','#xbetzc','#mewbkx','#fjqeyp','#wzwkps','#nzdjky','#sqambv','#dyrpoz','#dnatmu','#cswjet','#nbqier','#uswtjl','#lpzgag','#mabjlp','#pbrcxn','#cliwqy','#uakixz',
            '#duwafr','#dkssqd','#jirosn','#feduzp','#phaqch','#chzrtr','#dxjnid','#balcdz','#sckooi','#yojogc','#tgkpyz','#nuxxei','#glcunt','#dfnzbc','#ovifyk','#pgdanv','#shxpna','#qnqpdz','#sidkaq','#avghlx',
            '#gtrrbg','#jgmbkm','#kaowcf','#squsgn','#yuszcm','#tlieuv','#sotecr','#aflqxf','#xtgstx','#fxphgs','#gooefu','#xbhspv','#uwrtcg','#fqnbbe','#wqpzar','#fmlywp','#cdyelf','#evxvgu','#zgfcnx','#unhrmu',
            '#qrnsew', '#pwltrq', '#juumud', '#ccsbzq', '#towwed', '#pyxzja', '#repzcq', '#rduwjw', '#bhvmae', '#vfaqxx', '#caxnel', '#qrtmht', '#qirqva', '#faewyy', '#axskdy', '#gumgog', '#bltjoq', '#pspqdo', '#oxfxzg', '#lfhjbj',
            '#ffiwfl', '#viydoj', '#dkhdfq', '#ecjhcs', '#bdvkah', '#msqrev', '#twkxae', '#nivfmw', '#qmkvus', '#poxkoh', '#jlhehl', '#ssnahl', '#srsvdl', '#yuhkuf', '#igdrgd', '#iygdoo', '#ncigzd', '#fwzpcp', '#masjye', '#igmowt',
            '#strsby', '#zfeuef', '#izlbue', '#qvrukw', '#fvkanb', '#jegzrs', '#fdwpcs', '#coxjms', '#wnrlfw', '#vsttpd', '#qdbvag', '#tnhaeh', '#sbvxsv', '#bbgcjp', '#fhtswp', '#bbvcpf', '#snvymv', '#maozqd', '#jgdlql', '#cvtvcx',
            '#mbdoaj','#qqeypw','#azxtnh','#tpgvli','#vhgqzd','#olhowy','#vrqcvf','#farcjq','#rakqzz','#drgspd','#jsukwu','#omsdmt','#qoygeq','#yzoiuy','#btxfus','#lxzvon','#wfxoxx','#vdthsv','#eurjny','#yszojr',
            '#kzkpat', '#igafkw', '#jpasmp', '#zrjtvi', '#sqrdwz', '#pmavmp', '#lxdyma', '#tnptmn', '#kgqupu', '#ldelyl', '#oupfys', '#fzqiqk', '#qxkypt', '#lgsgdl', '#mmdgzm', '#dqnxnl', '#tnaqpa', '#jocyld', '#oxwdpq', '#plvfqb',
            '#srwilv', '#ldwxen', '#dddyyl', '#flqrvx', '#mukyeq', '#pixkcj', '#cvhyuw', '#jmwndb', '#aurpvh', '#mydbpf', '#onoctw', '#qwunfx', '#igtlnh', '#ubjfir', '#jfchsw', '#xonxln', '#mlyqqz', '#adchsv', '#hfouum', '#bnyvkf',
            '#hrlohr', '#spyxhd', '#plqcyo', '#kxkqqt', '#vggvnj', '#ysjgan', '#omcrqp', '#chbymv', '#exsdzx', '#jijsgs', '#ucqssf', '#jffttl', '#rkczmg', '#alzgam', '#qrozgx', '#ehcnjy', '#yixabf', '#nzogbh', '#knvnxt', '#mgrqhr',
            '#sqotqr', '#oeoxvi', '#cwvnbw', '#vtwwyr', '#hiejth', '#cqgtou', '#rdfzun', '#vqooky', '#iegjru', '#hstdcr', '#oksoje', '#heqrxq', '#wdxpfh', '#tfnmny', '#ggktmx', '#kcpbzq', '#dlgxmt', '#yqpkea', '#usctju', '#yelgcn',
            '#mpqfdr', '#kqgajz', '#kvjbdx', '#xnzkev', '#ybmind', '#mgzksp', '#jyplzt', '#tppumr', '#eyzztj', '#stffdi', '#ixwltj', '#jpdlup', '#zickhc', '#avzoqr', '#zcuket', '#eboxzl', '#qelatu', '#dliovq', '#zgtmsu', '#fplupn',
            '#cvpevp', '#qtctly', '#euytmq', '#mnibpj', '#djiydy', '#mwbrlo', '#gfyqhg', '#zbwdcv', '#qfstiy', '#ynqvyo', '#izcdki', '#eknsoj', '#fijyde', '#xhembv', '#ggvyhg', '#ouyvdb', '#czgzxf', '#pnwatn', '#lvdgor', '#jozufj',
            '#czsulc', '#pxcwqt', '#hcitcg', '#snwxmg', '#qjtrjo', '#apzkmm', '#wfexdx', '#gqevjk', '#qcmgty', '#jitigd', '#eqtvqt', '#wthqtk', '#pjowaj', '#apxwcf', '#ytqhit', '#eoutqx', '#kmzryj', '#kijtcy', '#lahpdr', '#ovrxdq',
            '#vffikm', '#srqvfe', '#elfrnl', '#hhdrpv', '#lyeskd', '#ibxpyv', '#mhnmmq', '#gtoawz', '#rgtaiw', '#ngbyac', '#quvbqv', '#afdlpj', '#kxjkdt', '#sfmykc', '#zgofvr', '#ugyukk', '#llcumd', '#ehtqjk', '#kxhtnb', '#pyzcsx',
            '#tcrrrm', '#vybnae', '#xemyox', '#xefrfe', '#emekjb', '#dwbzlo', '#kizfnt', '#phjbgc', '#qvuvpx', '#vajmda', '#czvccr', '#mxevsh', '#rlfelb', '#zqmlry', '#cjdjib', '#lvhlto', '#ynbvxm', '#azbbmh', '#bpdfga', '#ismqak',
            '#dazygi', '#upjswm', '#gcqtwo', '#budppp', '#sinndd', '#kiclyk', '#autfza', '#htbcmx', '#tgkmyl', '#yyspym', '#febogu', '#vhzxoo', '#dibpgy', '#lzenvs', '#ayhrql', '#nonatv', '#pobsvx', '#mivrwk', '#mysmcg', '#batqvk',
            '#oiuzdw', '#btxiyy', '#swnskn', '#ubhejm', '#udwuox', '#jeurnr', '#npxzvl', '#jvdnfm', '#ziojdc', '#kmovec', '#sggsrd', '#sosmpp', '#gdgwmh', '#vtbxsi', '#cswdix', '#aayqpv', '#ledpuh', '#uvsunn', '#zeaiqn', '#yqovyh',
            '#nrpqow', '#srazhb', '#gygoqn', '#xdxtdr', '#iseoum', '#yjckok', '#nxygvv', '#rhrbnu', '#momovr', '#ftfnou', '#ivbhhb', '#xlvgtn', '#jjfcxv', '#omcwzl', '#bkqodr', '#akgsjp', '#fxbltd', '#jfolwm', '#aniijv', '#mynrrr',
            '#pqdsna', '#hlwdff', '#zpdrvj', '#fzurce', '#orkxcy', '#wiaroj', '#toplbp', '#kqfudw', '#ebancd', '#jemtdl', '#cncaff', '#vcyudv', '#yfzrat', '#ibstyx', '#jeeosd', '#cxlwec', '#kwlkfz', '#coaphp', '#glyulm', '#qnkuxo',
            '#mpeksh', '#mxjzsw', '#gginbz', '#jvfuwb', '#zaoysj', '#ykseqb', '#cqfwqm', '#yndqou', '#mjnupk', '#hggwxa', '#sykhki', '#ybeovo', '#aerran', '#nmpibu', '#pxhuwf', '#rkuwky', '#codjjv', '#jyhhqi', '#bcbisb', '#jqupqe',
            '#lejjre', '#fssbws', '#xbvvtm', '#ygencl', '#laqmvw', '#udfevy', '#pothoq', '#dkbdtp', '#zsspef', '#poksif', '#skbibp', '#rsbwgk', '#qwaolg', '#qhlkxd', '#rnntap', '#pmnkwq', '#heklai', '#xgjkbl', '#sollvd', '#ynsrft',
            '#rutdxt', '#rozcvo', '#grtxcq', '#pjtihp', '#jconfs', '#yocqel', '#dmswtk', '#fsznsd', '#qjygwn', '#tbegpq', '#lfoyoh', '#xftkoo', '#rvcads', '#cqsekv', '#podmen', '#hnoxyi', '#ufjzyh', '#shzdhf', '#snfwbi', '#rsjasb',
            '#wjxuol', '#xjqnlk', '#uuqpis', '#iclqet', '#escenf', '#dqrism', '#xsmgbt', '#fcrlnr', '#qcjnrh', '#zmgfmb', '#mbgkll', '#uvndly', '#etqcdd', '#guluss', '#xezdig', '#fcsabv', '#eoylug', '#apxrvw', '#bineco', '#dyylgu',
            '#ypaxos', '#qftqgq', '#gdclmc', '#cmqmmj', '#dfqxft', '#xobygk', '#suocmm', '#fhbpxq', '#rjsoiq', '#qqtfkk', '#wnbeym', '#tbogpq', '#sfsxms', '#jzvemb', '#qgbguz', '#ugeygk', '#aprvkk', '#oqpnlc', '#ojoill', '#ynbouu',
            '#asbdef', '#fpcnpa', '#hckvtr', '#braxhd', '#mhlelt', '#yywmkr', '#hsiydt', '#lmrfbl', '#gkvaat', '#gctuwg', '#busgrr', '#putmxy', '#flcqaz', '#mjpyue', '#tyysno', '#ofvbwf', '#oxkcfk', '#dojshu', '#hkbwki', '#rgxwew',
            '#ulgtdd', '#zricte', '#snfqmq', '#jjgpel', '#disjfn', '#bpcxgv', '#yxqzmg', '#bxflwx', '#mjgiwv', '#kbnbqy', '#wlkwdj', '#tobpuy', '#wzqhou', '#lpcrkl', '#feupga', '#dvqcqu', '#ocxiuk', '#ezyfra', '#pwpyms', '#wsfzyd',
            '#gvtfhp', '#hrgiyx', '#pqojcr', '#zozoiq', '#dnhmhi', '#eslkeh', '#xfoegc', '#khdbof', '#aceoqo', '#vmrjeb', '#kfmnbn', '#voweza', '#olncrp', '#lqmrhl', '#jovagl', '#mexvqf', '#tiyjuk', '#xlkxum', '#mqqeam', '#sdreoj',
            '#utdnut', '#rerqqd', '#xuavyc', '#rkpzes', '#bxjeya', '#mqurwi', '#jratqp', '#mbvwqe', '#hbrilv', '#pvpfhd', '#unkvhq', '#robqmf', '#wnklqt', '#yxihvd', '#rnokvk', '#dshyvk', '#vumbvp', '#fpzkoe', '#abnpha', '#sodyei',
            '#aqprng', '#yopbuk', '#uhyjeu', '#hryssz', '#flcvdk', '#ryrtjd', '#mnknjp', '#ksddug', '#cjfwqy', '#uarwkc', '#tyhlxb', '#uluygm', '#pganfv', '#dxjfoy', '#nciyvx', '#yffnjm', '#ckcbqo', '#jmmbtj', '#rkxhgn', '#fgwecf',
            '#ymfemm', '#ventxs', '#vufgjz', '#gekrgg', '#zizhwn', '#ttfkpm', '#gwwpwg', '#oakeqk', '#hzbcnk', '#vthxus', '#gfbdwc', '#mzigmz', '#vubedc', '#nhkljg', '#ffifzr', '#siabdk', '#arbmxs', '#xgzixt', '#znkytx', '#bwfxcv',
            '#anyudl', '#fppsvk', '#mbgipu', '#qfsdtr', '#xqpqra', '#yqjglw', '#lvjqux', '#mssnzr', '#dgeuml', '#ikukof', '#oqwmrv', '#eewclo', '#ofruuz', '#cuahqm', '#buiguc', '#bhkdcv', '#ifokqj', '#nnmmlg', '#jbnowm', '#bwcccm',
            '#lesvfu', '#tesshs', '#mvrfxc', '#riorbf', '#eiwstu', '#lvzlew', '#cvxetw', '#fgtwkx', '#otaaww', '#xcacyp', '#caglya', '#uubvvo', '#aofhxq', '#wzlfti', '#tlmkym', '#bexxls', '#mrssuv', '#dnmact', '#ruyxxq',
        );
        */
        /*
        $tags = array(
            '#afglqr','#biktvw','#ahlnoy','#hjknvw','#fnoruv','#dfgklr','#depquw','#cdenrw','#afqrwz','#hijnuz','#fghlpr','#abdjvy','#chiovw','#iklstw','#lnoqwx','#abcmsv','#efmtwz','#fipqtu','#cfjoyz','#cilost',
            '#bhimpq','#abijmo','#afhmrt','#btvxyz','#dgkuvw','#cfhjky','#afgjuv','#gjknow','#biuwxy','#apsuwx','#jlnsuy','#fgsvxz','#eqtuxz','#alnqrz','#agpstu','#fjnovy','#amnptu','#fnoryz','#bcempq','#aeijtx',
            '#fhkqtz','#copqrx','#acmqvw','#bgmuvw','#aekouv','#ilnprw','#adikoq','#cdkoqs','#mnpswy','#egjlqw','#bcehsz','#bnpvwy','#afimox','#akoqtz','#ghklmt','#behsvz','#fjkmrw','#adegwx','#bjkmpq',#efjsuy
            '#kmnouz','#agpqtv','#jklnrx','#eilrsx','#kmnpvx','#ilnsvw','#jkmstx','#amnuwz','#cdmtvw','#abmstv','#gmnrwx','#ikvwyz','#dfgjuv','#chjmsx','#hmtvwz','#aekpqt','#hmnrxy','#bgsuxy','#abcnpz',#bnopsz
            '#loquvx','#egjotv','#efknqy','#djkstv','#acinqw','#cfoxyz','#cdjqsw','#fjoqsz','#cekrsy','#gmopuv','#aegkmr','#aklqvw','#ceghln','#bdfkrs','#cfhqst','#cegquz','#bcegjy','#fhikpt','#fotuvw',#ehklpv
            '#aijmsv','#dflmwz','#fhijrw','#dfkmuy','#bgioqw','#dhlsvw','#bdfikv','#ehkqtw','#nqrwxz','#aeijlv','#aemnty','#efknqr','#abhouw','#ekstvx','#bdfjvx','#dhktux','#abnsuv','#fmnqxy','#ejnorv',#fjosxy
            '#selfiesunday','#selfieclub','#aijmsv','#dflmwz','#fhijrw','#dfkmuy','#bgioqw','#dhlsvw','#bdfikv','#ehkqtw','#nqrwxz','#aeijlv','#aemnty','#efknqr','#abhouw','#ekstvx','#bdfjvx','#dhktux','#abnsuv','#fmnqxy','#ejnorv','#fjosxy',
            '#dilosw','#abgipx','#djoqrs','#cghrxy','#cfglsv','#afpsxy','#deghly','#chjmtz','#eglpuw','#agjruw','#dkstxy','#cdmqry','#cdjpqt','#ackntw','#bnorwy','#jklqwy','#cknoqv','#bdglmu','#agknuy','#bhnqrz',
            '#aghsvz','#ghnprw','#aglmns','#cfpsux','#ejkorz','#dlmpyz','#bmnopy','#akmqrz','#dfgmor','#knrwxz','#gksxyz','#fruwxy','#aceprv','#hkmory','#cfhimy','#jlmowy','#dgkptv','#cgikmz','#bmostw',#dgjoqx
            '#selfieclub','#selfiesunday','#aijmst','#ablptu','#chilmr','#bfilqz','#deotuz','#almpqu','#denrsu','#adgknx','#bdfglx','#chklsy','#bhiqsy','#agpuxz','#bdgikx','#bhjlvw','#bfoprx','#behqty','#bfhilz','#bmpvxz','#hjntvw',#bfimyz
            '#beijlr','#aclqwx','#lnuwxy','#akmnpu','#afhjmz','#adpsyz','#dhqrsz','#acrtxz','#dnptwx','#ijklot','#fjmnuy','#aijmvz','#ikmrwy','#abopsv','#eghipv','#cdjlmn','#bclosu','#ijlmqs','#cfinrz','#begjty',
            '#fjnopq','#bdnsxy','#hmrtvy','#dhjkqz','#bfhkmq','#hjmvwy','#adjmow','#cekoru','#adfirx','#bdfhix','#cgquwz','#giknuy','#jnqrwz','#fgpqrt','#afgmwy','#aejkms','#oprwxz','#akptuy','#cdgkrv',#cdilsw
            '#dknpqu','#abcjwx','#bcdlpq','#dhiswy','#afhuvy','#cdeist','#afksvx','#fjlqtv','#apsxyz','#bcehls','#dijpuz','#ckpstv','#bcdory','#acgjls','#efilwy','#bfgtuw','#djmryz','#djmtwy','#agikqu',#bdeiow
            '#bhiosx','#bdgioz','#cmprwx','#bglmtw','#eirsux','#efmopv','#adjrsv','#bcklrv','#ehklvz','#bhopsw','#defquz','#dmpqxy','#agimot','#chmoqy','#jlnvxy','#denswy','#fnotuv','#ckqsxy','#kmorvy',#bchmrs
            '#gqrtuz','#afghlz','#bghipt','#cdfhis','#ijkqtx','#eknptu','#ablmnw','#acmotu','#cdehkp','#fhikly','#dgknqu','#aikrtu','#aenosx','#eklnpq','#jknouv','#bgkmnu','#bcdimq','#aijpvz','#chnqsx','#abhjly',
        );
        */
        
        /**
        $str = str_split('abcdefghijklmnopqrstuvwxyz');
        $tag = array_rand( $str, 6 );
        foreach( $tag as &$char ){
            $char = $str[$char];
        }
        $tag = join('',$tag);
        **/
        return $tags[ array_rand( $tags ) ];
    }
    
    public static function doHouseFollow( $personaId, $houseAccountname ){
        $me = new self( $personaId );
        if( $me->handleLogin() ){
            $url = "http://web.stagram.com/n/$houseAccountname/";
            $response = $me->get( $url );
            $ptrn = '@"follow_button_(\w+)"@';
            $matches = array();
            preg_match($ptrn, $response, $matches);
            if( !empty( $matches[1] ) ){
                $id = $matches[1];
                $me->follow( $id, $houseAccountname );
            }
        }
    }
    
    /*
    
    Request URL:https://instagram.com/oauth/authorize/?client_id=63a3a9e66f22406799e904ccb91c3ab4&redirect_uri=http://54.243.163.24/instagram_oauth.php&response_type=code
    Request Headersview source
    
    */// Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8 
    /*
    
    Content-Type:application/x-www-form-urlencoded
    Origin:https://instagram.com
    Referer:https://instagram.com/oauth/authorize/?client_id=63a3a9e66f22406799e904ccb91c3ab4&redirect_uri=http://54.243.163.24/instagram_oauth.php&response_type=code
    User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36
    
    Query String Parameters
    
    client_id:63a3a9e66f22406799e904ccb91c3ab4
    redirect_uri:http://54.243.163.24/instagram_oauth.php
    response_type:code

    Form Data
    
    csrfmiddlewaretoken:42215b2aa4eaa8988f87185008b4beac
    allow:Authorize
    
	 */
    public function loginAndAuthorizeApp( ){
        $this->purgeCookies();
        
        $response = $this->login();
        
        if( preg_match( '@Please enter a correct username and password@i', $response ) ){
            echo "invalid username / password: ",join(',', array( $this->persona->instagram->username, $this->persona->instagram->password ) ),"\n";
        } else if( preg_match( '@This account is inactive@i', $response ) ){
            echo "inactive account: ",join(',', array( $this->persona->instagram->username, $this->persona->instagram->password ) ),"\n";
        } else if( preg_match( '@Please complete the following CAPTCHA@i', $response ) ){
            echo "captcha'd persona ",join(',', array( $this->persona->instagram->username, $this->persona->instagram->password ) ),"\n";
        } else if( preg_match( '/Authorization Request/i', $response ) ){
            $response = $this->authorizeApp($response);
        } else {
            error_log( "cannot match the page" );
            // print_r( $response );
        }
    }
    
    public function authorizeApp( $authPageHtml ){
        $useProxy = $this->useProxy();
        $this->setUseProxy( false );
        $ptrn = '/<form.*?action="(.+?)"/';
        preg_match($ptrn, $authPageHtml, $matches);
        $formActionUrl = 'https://instagram.com'.$matches[1];
        
        $ptrn = '/name="csrfmiddlewaretoken" value="(.+?)"/';
        preg_match($ptrn, $authPageHtml, $matches);
        $csrfmiddlewaretoken = $matches[1];

        $responseType = 'code';
        
        $args = array(
            'csrfmiddlewaretoken' => $csrfmiddlewaretoken,
            'allow' => 'Authorize',
        );
        
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            "Referer: $formActionUrl",
            'Origin: https://instagram.com',
        );
        $response = $this->post( $formActionUrl, $args, false, $headers);
        $this->setUseProxy( $useProxy );
        print_r( array( $url, $args, $response)  ); exit;
        return $response;
    }
    
    public function login(){
        $proxySetting = $this->useProxy();
        $this->setUseProxy( false );
        $redirectUri = 'https://api.instagram.com/oauth/authorize/';
        $params = array(
            'client_id' => '9d836570317f4c18bca0db6d2ac38e29',
            'redirect_uri' => 'http://web.stagram.com/',
            'response_type' => 'code',
            'scope' => 'likes comments relationships',
        );
        
        $response = $this->get( $redirectUri, $params );
        
        // now we should have the login form
        // so we login and make sure we are logged in
        $ptrn = '/name="csrfmiddlewaretoken" value="(.+?)"/';
        preg_match($ptrn, $response, $matches);
        $csrfmiddlewaretoken = $matches[1];
        
        // <form method="POST" id="login-form" class="adjacent" action="/accounts/login/?next=/oauth/authorize/%3Fclient_id%3D63a3a9e66f22406799e904ccb91c3ab4%26redirect_uri%3Dhttp%3A//54.243.163.24/instagram_oauth.php%26response_type%3Dcode"
        $ptrn = '/<form .*? action="(.+?)"/';
        preg_match($ptrn, $response, $matches);
        $formActionUrl = 'https://instagram.com'.$matches[1];
        
        $args = array(
            'csrfmiddlewaretoken' => $csrfmiddlewaretoken,
            'username' => $this->persona->instagram->username,
            'password' => $this->persona->instagram->password
        );
        
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            "Referer: $formActionUrl",
            'Origin: https://instagram.com',
        );
        
        $response = $this->post( $formActionUrl, $args, false, $headers );
        $this->setUseProxy( $proxySetting );
        return $response;
    }
    
    /**
     * login and authorize the app
     * 
     * then get the tag array appropriate for this type of persona
     * 
     * we collect up to 5 posts for the tag
     * 
     * when collecting
     * 		we hit the tag page
     * 		get all of the ids from the page
     * 		and for each id check the db to see if we have commented on this before
     * 		we also check to see if we have commented on this user in the last week
     *		if either condition is true, we DO NOT comment
     *		then we put the id in an array
     *		as soon as we have 5 items or have gone throu 2 pages we return the array and comment on each item
     *
     * 
     * when we have the 5 items or less
     * we comment on each item and we sleep for 5 seconds
     * 
     * when we are dome with the tag we sleep for 7 minutes
     * 
     */
    
    public function browseTags(){
        $loggedIn = $this->handleLogin();
        if( $loggedIn ){
            $taggedIds = $this->getTaggedIds( );
            foreach( $taggedIds as $tag => $ids ){
                foreach( $ids as $id ){
                    //$message = $this->persona->getVolleyQuote( 'instagram' );
                    //$this->submitComment( $id, $message );
                    
                    if( mt_rand(1,100) <= 100 ){
                        echo "liking $id\n";
                        $this->like($id);
                    }
                    
                    if( mt_rand(1,100) <= 10 ){
                        list($photoId, $userId) = explode('_', $id );
                        echo "following $userId\n";
                        $this->follow( $userId );
                    }
                    
                    $sleep = $this->persona->getBrowseTagsCommentWait();
                    echo "submitted comment - sleeping for $sleep seconds\n";
                    sleep($sleep);
                }
                $sleep = $this->persona->getBrowseTagsTagWait();
                echo "completed tag $tag - sleeping for $sleep seconds\n";
                sleep($sleep);
            }
        }
    }
    
    /**
     * 
     * first we check to see if we are logged in
     * if we are not then we login
     * and check once more
     * 
     */
    public function handleLogin(){
        $loggedIn = true;
        $url = 'http://web.stagram.com/tag/lol';
        $response = $this->get( $url );
        if( !$this->isLoggedIn($response) ){
            $name = $this->persona->name;
            echo "user $name not logged in to Keek!  logging in!\n";
            $this->loginAndAuthorizeApp();
            $response = $this->get( $url );
            if( !$this->isLoggedIn($response) ){
                $msg = "something is wrong with logging in $name to Keek!  disabling the user!\n";
                echo $msg;
                $this->disablePersona( $msg );
                $loggedIn = false;
            }
        }
        return $loggedIn;
    }
    
    public function sendWarningEmail( $reason ){
        $c = BIM_Config::warningEmail();
        $e = new BIM_Email_Swift( $c->smtp );
        $c->emailData->text = $reason;
        $e->sendEmail( $c->emailData );
    }
    
    public static function popularTags(){
        return array(
            "love",
            "instagood",
            "me",
            "cute",
            "follow",
            "photooftheday",
            "like",
            "tbt",
            "followme",
            "girl",
            "tagsforlikes",
            "beautiful",
            "picoftheday",
            "happy",
            "instadaily",
            "summer",
            "igers",
            "fun",
            "smile",
            "bestoftheday",
            "instamood",
            "food",
            "swag",
            "instalike",
            "friends",
            "like4like",
            "fashion",
            "amazing",
            "tflers",
            "Keek",
            "iphoneonly",
            "selfie",
            "all_shots",
            "style",
            "tweegram",
            "lol",
            "instago",
            "l4l",
            "pretty",
            "follow4follow",
            "eyes",
            "sun",
            "nofilter",
            "my",
            "instacool",
            "hair",
            "nice",
            "life",
            "instafollow",
            "bored",
            "family",
            "cool",
            "instacollage",
            "likeforlike",
            "look",
            "iphonesia",
            "funny",
            "20likes",
            "sky",
            "hot",
            "colorful",
            "throwbackthursday",
            "statigram",
            "girls",
            "shoutout",
            "beach",
            "pink",
            "harrystyles",
            "instagramhub",
            "party",
            "night",
            "photo",
            "boyfriend",
            "f4f",
            "blue",
            "repost",
            "baby",
            "throwback",
            "makeup",
            "followforfollow",
            "niallhoran",
            "nature",
            "music",
            "art",
            "loveit",
            "instalove",
            "picstitch",
            "day",
            "igdaily",
            "beauty",
            "black",
            "shoes",
            "awesome",
            "followback",
            "home",
            "jj",
            "tired",
            "christmas",
            "instaphoto",
            "instapic",
        );        
    }
    
    public function getTaggedIds( ){
        $tags = self::popularTags();// $this->persona->getTags();
        $taggedIds = array();
        if($tags){
            //$tags = array_rand( $tags, 1 );
            $idsPerTag = 2;// $this->persona->idsPerTagInsta();
            foreach( $tags as $tag ){
                $ids = $this->getIdsForTag($tag, 2);
                $taggedIds[ $tag ] = array();
                foreach( $ids as $id ){
                    if( count( $taggedIds[ $tag ] ) < $idsPerTag && $this->canPing( $id ) ){
                        $taggedIds[ $tag ][] = $id;
                    }
                }
            }
        }
        // print_r( $taggedIds ); exit;
        return $taggedIds;
    }
    
    public function getIdsForTag( $tag, $iterations = 1 ){
        $ids = array();
        $pageUrl = "http://web.stagram.com/tag/$tag";
        for( $n = 0; $n < $iterations; $n++ ){
            $response = $this->get( $pageUrl );
            // here we ensure that we are logged in still
            // $this->handleLogin( $response );
            //print_r( $this->isLoggedIn($response ) ); exit;
            
            // type="image" name="comment__166595034299642639_37459491"
            $ptrn = '/type="image" name="comment__(.+?)"/';
            preg_match_all($ptrn, $response, $matches);
            if( isset( $matches[1] ) ){
                array_splice( $ids, count( $ids ),  0, $matches[1] );
            }
            
            $sleep = $this->persona->getTagIdWaitTime();
            echo "sleeping for $sleep seconds after fetching $pageUrl\n";
            sleep( $sleep );
        }
        $ids = array_unique( $ids );
        // print_r( array($ids, $tag) );exit;
        return $ids;
    }
    
    public static function releaseUser( $userId ){
        $dao = new BIM_DAO_Mysql_Growth_Keek( BIM_Config::db() );
        $dao->releaseUser($userId);
    }
    
    public static function canDoPing( $id ){
        $canPing = false;
        $data = explode( '_', $id );
        if( $data ){
            print_r( $data );
            $userId = empty( $data[1] ) ? $data[0] : $data[1];
            if( $userId ){
                $dao = new BIM_DAO_Mysql_Growth_Keek( BIM_Config::db() );
                $timeSpan = 86400 * 7;
                $currentTime = time();
                // the getlastContact call will insert the 
                // user id so we do not end up contacting the same user twice
                $lastContact = $dao->getLastContact( $userId );
                if( ($currentTime - $lastContact) >= $timeSpan ){
                    if( $dao->retainUser($userId) ){
                        $canPing = true;
                    }
                }
            }
        }
        return $canPing;
    }
    
    public function canPing( $id ){
        return self::canDoPing($id);
    }
    
    public function isLoggedIn( $html ){
        $ptrn = '@LOG OUT</a>@';
        return preg_match($ptrn, $html);
    }
    
    public function submitComment( $id, $message ){
        $params = array(
            'message' => $message,
            'messageid' => $id,
            't'=> mt_rand(5000, 10000)
        );
        print_r( $params );
        $response = $this->post( 'http://web.stagram.com/post_comment/', $params );
        $response = json_decode( $response );
        print_r( $response );
        if( isset($response->status) && $response->status == 'OK' ){
            $dao = new BIM_DAO_Mysql_Growth_Keek( BIM_Config::db() );
            list( $imageId, $userId ) = explode('_', $id, 2 );
            $dao->updateLastContact( $userId, time() );
            $dao->logSuccess($id, $message, $this->persona->instagram->name );
        } else {
            print_r( $response );
            $sleep = $this->persona->getLoginWaitTime();
            $sleep = 5;
            echo $this->persona->name." no longer logged in! trying login again after sleeping for $sleep seconds\n";
            sleep( $sleep );
            $this->handleLogin();
        }
    }
    
    /**
     *  update the users stats that we use to guage the effectiveness of our auto outreach
     *  
     *  we get the following for tumblr
     *  
     *  	total followers  getBlogFollowers
     *      total following getFollowedBlogs()
     *  	total likes getBlogLikes()
     *  	
     */
    public function updateUserStats(){
        
        if($this->handleLogin()){
    
            $name = $this->persona->name;
            $profileUrl = "http://web.stagram.com/n/$name/";
            $response = $this->get( $profileUrl );
    
            $following = 0;
            $followers = 0;
            $likes = 0;
    
            $ptrn = '/<\s*span.+?id="follower_count_\d+"\s*>(.*?)</im';
            preg_match( $ptrn, $response, $matches );
            if( isset( $matches[1] ) ){
                $followers = $matches[1];
            }
    
            $ptrn = '/<\s*span.+?id="following_count_\d+"\s*>(.*?)</im';
            preg_match( $ptrn, $response, $matches );
            if( isset( $matches[1] ) ){
                $following = $matches[1];
            }
    
            $userStats = (object) array(
                'name' => $this->persona->name,
                'followers' => $followers,
                'following' => $following,
                'likes' => $likes,
                'network' => 'Keek',
            );
    
            print_r( $userStats );
            
            $dao = new BIM_DAO_Mysql_Growth( BIM_Config::db() );
            $dao->updateUserStats( $userStats );
        }        
    }
    
	/**
	 * we receive the username and password of the insta user
	 * login as the user
	 * get a list of their friends
	 * then for each friend we get the latest photo
	 * and drop a volley comment
	 */
    public function instaInvite(){
        $this->handleLogin();
        $friends = $this->getFriends( 10 );
        foreach( $friends as $name => $url ){
            //if( $name != 'typeoh' ) continue;
            $url = trim( $url, '/' );
            $pageUrl = "http://web.stagram.com/$url";
            $this->commentOnLatestPhoto( $pageUrl );
        }
    }
    
    public function commentOnLatestPhoto( $pageUrl, $message = '', $like = false ){
        $response = $this->get( $pageUrl );
        
        // type="image" name="comment__166595034299642639_37459491"
        $ptrn = '/type="image" name="comment__(.+?)"/';
        preg_match($ptrn, $response, $matches);
        if( isset( $matches[1] ) ){
            $id = $matches[1];
            if( !$message ){
                $inviteText = BIM_Config::inviteMsgs();
                $message = $inviteText['instagram'];
            }
            $message = preg_replace('/\[\[USERNAME\]\]/', $this->persona->name, $message);
            $this->submitComment($id, $message);
            if( $like ){
                self::sleep( 2, "before liking" );
                $this->like($id);
            }
            //$sleep = 5;
            //echo "submitted comment to $pageUrl - sleeping for $sleep seconds\n";
            //sleep($sleep);
        }
    }
    
    
    /**
		<div class="firstinfo clearfix">.*?<strong><a href="(.*?)">(.*?)</a></strong>
    */
    public function getFriends( $iterations = 1 ){
        $feedUrl = 'feed/';
        
        $friendData = array();
        $n = 0;
        while( $n < $iterations && $feedUrl ){
            
            $feedUrl = "http://web.stagram.com/$feedUrl";
            echo "getting page $feedUrl\n";
            $page = $this->get($feedUrl);
            
            $ptrn = '@<div class="firstinfo clearfix">.*?<strong><a href="(.*?)">(.*?)</a></strong>@is';
            $matches = array();
            preg_match_all( $ptrn, $page, $matches);
            if( !empty($matches[2]) ){
                foreach( $matches[2] as $idx => $friendName ){
                    $friendData[ $friendName ] = $matches[1][$idx];
                }
            }
            
            // now we get the link for the next page of images
            $feedUrl = false; // set to false, so if we do not 
                              // find the url we will break out of the while loop
            $ptrn = '@<a href="(.*?)" rel="next">Earlier</a>@i';
            preg_match($ptrn, $page, $matches);
            if( !empty( $matches[1] ) ){
                $feedUrl = $matches[1];
                $sleep = 3;
                echo "sleeping for $sleep seconds before getting more usernames\n";
                sleep( $sleep );
            }
            $n++;
        }
        return $friendData;
    }
    
    
    public static function checkPersonas(){
        $dao = new BIM_DAO_Mysql_Persona( BIM_Config::db() );
        $data = $dao->getData( null, 'instagram' );
        foreach($data as $persona ){
            self::checkPersona( $persona );
            $sleep = 5;
            echo "checked $persona->username sleeping for $sleep seconds\n";
            sleep($sleep);
        }
    }
    
    public static function checkPersonasInFile( $filename = '' ){
        $fh = fopen($filename,'rb');
        while( $line = trim( fgets( $fh ) ) ){
            $data = explode(':', $line);
            if( $data ){
                $username = $data[0];
                $persona = (object) array( 'username' => $username);
                self::checkPersona( $persona );
                $sleep = 5;
                echo "checked $persona->username sleeping for $sleep seconds\n";
                sleep($sleep);
            }
        }
    }
    
    public static function loadPersonasInFile( $filename = '', $delim = ':' ){
        $fh = fopen($filename,'rb');
        while( $line = trim( fgets( $fh ) ) ){
            $data = explode($delim, $line);
            if( $data ){
                
                $personaData = (object) array(
                'name' => $data[0],
                'instagram' => (object) array(
                    	'username' => $data[0],
                        'password' => $data[1],
                    )
                );
                
                $persona = new BIM_Growth_Persona( $personaData );
                $r = new self( $persona );
                
                if( !$r->handleLogin() ){
                    echo "invalid account: ".$persona->instagram->username.",".$persona->instagram->password."\n";
                } else {
                    echo "valid account: ".$persona->instagram->username.",".$persona->instagram->password."\n";
                    self::loadPersona( $personaData );
                }
                
                $sleep = 5;
                echo "loaded $personaData->name sleeping for $sleep seconds\n";
                sleep($sleep);
            }
        }
    }
    
    public static function loadPersona( $personaData ){
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
         /*
INSERT INTO `persona` 
(`network`, `email`, `username`, `password`, `name`, `extra`, `enabled`, `type`)
VALUES
('instagram', '\'\'', 'Ariannaxoxoluver', 'teamvolleypassword', 'Ariannaxoxoluver', '{}', 1, 'authentic');
          * 
          */
        $sql = "
            INSERT INTO growth.`persona` 
            (`network`, `email`, `username`, `password`, `name`, `extra`, `enabled`, `type`)
            VALUES
            ('instagram', '', ?, ?, ?, '{}', 1, 'authentic');
        ";
        $params = array(
            $personaData->instagram->username,
            $personaData->instagram->password,
            $personaData->name        
        );
        $dao->prepareAndExecute( $sql, $params );
        error_log("loaded $personaData->name\n");
    }
    
    public static function checkPersona( $persona ){
        $persona = new BIM_Growth_Persona( $persona->username );
        $r = new self( $persona );
        
        if( !$r->handleLogin() ){
            $persona = null;
            echo "invalid account: ".$persona->instagram->username.",".$persona->instagram->password."\n";
        } else {
            echo "valid account: ".$persona->instagram->username.",".$persona->instagram->password."\n";
        }
        return $persona;
    }
    
    public static function enablePersonas( $file ){
        $fh = fopen( $file, 'rb' );
        while( $line = fgets( $fh ) ){
            echo "enabling $line\n";
            list($name,$password) = explode(',',$line);
            $persona = new BIM_Growth_Persona( $name );
            $r = new self( $persona );
            $r->enablePersona();
        }
    }
    
    public static function harvestTags( $tags ){
        $baseUrl = 'http://web.stagram.com/search';
        $g = new BIM_Growth_Keek();
        $tagsFound = array();
        foreach( $tags as $tag ){
            $url = "$baseUrl/$tag/";
            $response = '';
            $tries = 0;
            while( !$response && $tries < 10 ){
                $tries++;
                echo "getting $url\n";
                $response = $g->get( $url );
                if( !$response ){
                    $sleep = 1;
                    echo "sleeping for $sleep seconds\n";
                    sleep( $sleep );
                }
            }
            $matches = array();
            preg_match_all('@href="/tag/(.*?)/@i', $response, $matches);
            if( isset( $matches[1] ) ){
                array_splice( $tagsFound, count( $tagsFound), 0, $matches[1] );
            }
            print_r( $tagsFound );
        }
        
        $file = 'tags.out';
        file_put_contents( $file, '' );
        foreach( $tagsFound as $tag ){
            file_put_contents( $file, "$tag\n", FILE_APPEND );
        }
    }
    
    public static function findShoutOuts( $selfieUrl = null ){
        if( !$selfieUrl ){
            $selfieUrl = 'http://web.stagram.com//tag/selfie/';
        }
        
        $g = new BIM_Growth_Keek();
        $attempts = 0;
        while( $selfieUrl && $attempts < 3 ){
            //@unlink('/tmp/cookies_BIM_Growth_Keek.txt');
            echo "getting $selfieUrl\n";
            $response = $g->get( $selfieUrl );
            self::_findShoutOuts( $response );
            $oldSelfieUrl = $selfieUrl;
            $selfieUrl = self::getNextSelfieUrl($response);
            
            $msg = '';
            $sleep = 1;
            
            if( !$selfieUrl ){
                $attempts++;
                $selfieUrl = $oldSelfieUrl;
                $sleep = 30;
                $msg = " could not find selfie url at $selfieUrl";
            }
            
            echo "sleeping for $sleep seconds.  $msg\n";
            sleep( $sleep );
        }
    }
    
    public static function getNextSelfieUrl( $text ){
        $url = self::getEarlierUrl($text);
        if(!$url) {
            print_r( $text );
        }
        return $url;
    }
    
    public static function getEarlierUrl( $text ){
        $url = null;
        $ptrn = '@<a href="(.*?)" rel="next">Earlier</a>@i';
        preg_match($ptrn, $text, $matches);
        if( !empty( $matches[1] ) ){
            $url = 'http://web.stagram.com/'.$matches[1];
        }
        return $url;
    }
    
    public static function getUsersFromText( $text ){
        $ids = array();
        $ptrn = '@id="photo\d+_(\d+)".*?<strong><a href="/n/(.*?)/">.*?</a></strong>@is';
        preg_match_all($ptrn,$text,$ids);
        
        $selfies = array();
        $ptrn = '@<div class="photo relative">.*?<a href="(/p/.*?)"><img@is';
        preg_match_all( $ptrn, $text, $selfies );
        
        $users = array();
        foreach( $ids[1] as $idx => $id ){
            $name = $ids[2][$idx];
            $users[] = (object) array(
                'id' => $id,
                'name' => $name,
                'url' => "http://web.stagram.com/n/$name",
            	'selfie' => 'http://web.stagram.com'.$selfies[1][$idx]
            );
        }
        return $users;
    }
    
    public static function _findShoutOuts( $response ){
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $g = new BIM_Growth_Keek();
        echo "getting users\n";
        $users = self::getUsersFromText( $response );        
        
        foreach( $users as $idx => $user ){
            $profileUrl = "http://web.stagram.com/n/$user->name/";
            $response = $g->get( $profileUrl );
    
            $followers = 0;
            $ptrn = '/<\s*span.+?id="follower_count_\d+"\s*>(.*?)</im';
            preg_match( $ptrn, $response, $matches );
            if( isset( $matches[1] ) ){
                $followers = $matches[1];
            }
            
            $following = 0;
            $ptrn = '@<\s*span.+?id="following_count_\d+"\s*>(.*?)<@im';
            preg_match( $ptrn, $response, $matches );
            if( isset( $matches[1] ) ){
                $following = $matches[1];
            }
            
            $followRatio = -1;
            if( $followers ){
                $followRatio = ceil( ($following / $followers) * 100 );
            }
            // <span style="font-size:123.1%;" id="following_count_16659424">106</span>
            
            if( $followers >= 1000 && $followers <= 2000 && $followRatio < 50 && $followRatio > 0 ){
                echo "inserting $user->name with $followRatio - followers: $followers and following: $following \n";
                $matches = array();
                $sql = "
                	insert ignore into growth.ig_shoutouts
                	(name,followers,following,url,selfie,follow_ratio)
                	values (?,?,?,?,?,?)
                ";
                $params = array($user->name,$followers,$following, $profileUrl,$user->selfie,$followRatio);
                $dao->prepareAndExecute( $sql, $params );
            }
            
            $sleep = 1;
            echo "checked $user->name followers: $followers - following: $following - ratio: $followRatio - sleeping for $sleep second\n";
            sleep( $sleep );
        }
    }
    
    public static function findPromoters(){
        for( $n = 0; $n < 10; $n++ ){
            @unlink('/tmp/cookies_BIM_Growth_Keek.txt');
            self::_findPromoters();
            $sleep = 5;
            echo "sleeping for $sleep seconds\n";
            sleep( $sleep );
        }
    }
    
    public static function _findPromoters(){
        $baseUrl = 'http://web.stagram.com/popular/?'.uniqid();
        $g = new BIM_Growth_Keek();
        $response = $g->get( $baseUrl );
        
        $ids = array();
        $ptrn = '@id="photo\d+_(\d+)".*?<strong><a href="/n/(.*?)/">.*?</a></strong>@is';
        preg_match_all($ptrn,$response,$ids);
        
        $users = array();
        foreach( $ids[1] as $idx => $id ){
            $name = $ids[2][$idx];
            $users[] = (object) array(
                'id' => $id,
                'name' => $name
            );
        }
        
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        
        foreach( $users as $idx => $user ){
            $profileUrl = "http://web.stagram.com/n/$user->name/";
            $response = $g->get( $profileUrl );
    
            $followers = 0;
            $ptrn = '/<\s*span.+?id="follower_count_\d+"\s*>(.*?)</im';
            preg_match( $ptrn, $response, $matches );
            if( isset( $matches[1] ) ){
                $followers = $matches[1];
            }
            if( $followers >= 100000 ){
                echo "found $user->name with $followers followers\n";
                
                $matches = array();
                $ptrn = '@class="ui_tools".*?style="padding-top:5px;">(.*?)</p>@is';
                preg_match($ptrn, $response, $matches);
                if( !empty($matches[1] ) ){
                    $bio = strip_tags($matches[1]);
                    // print "$bio\n";
                    if( preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $bio, $matches ) ) {
                        echo $matches[0]."\n";
                        $email = $matches[0];
                        if( $email ){
                            $sql = "
                            	insert ignore into growth.ig_promoters
                            	(name,followers,url,email)
                            	values (?,?,?,?)
                            ";
                            $params = array($user->name,$followers,$profileUrl,$email);
                            $dao->prepareAndExecute( $sql, $params );
                        }
                    } else {
                        //echo "no email for $promoter->name\n";
                    }
                }
            }
        }
    }
    
    /**
     * get a canadian tag 
     * get users for that tag
     * get the bio
     * look for kik
     * if kik, save the id
     */
    
    public static function collectKikIdsCanada(){
        $g = new BIM_Growth();
        $tags = BIM_Growth_Tags_Canadian::getTags();
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        foreach( $tags as $tag ){
            $names = array();
            $pageUrl = "http://web.stagram.com/tag/$tag";
            for( $n = 0; $n < 2; $n++ ){
                $response = $g->get( $pageUrl );
                $ptrn = '@id="photo\d+_\d+".*?<strong><a href="/n/(.*?)/">.*?</a></strong>@is';
                preg_match_all($ptrn, $response, $matches);
                if( isset( $matches[1] ) ){
                    array_splice( $names, count( $names ),  0, $matches[1] );
                }
                $sleep = 1;
                echo "sleeping for $sleep seconds after fetching $pageUrl\n";
                sleep( $sleep );
            }
            $names = array_unique( $names );
            
            foreach( $names as $name ){
                $profileUrl = "http://web.stagram.com/n/$name/";
                $response = $g->get( $profileUrl );
                
                $matches = array();
                $ptrn = '@class="ui_tools".*?style="padding-top:5px;">(.*?)</p>@is';
                preg_match($ptrn, $response, $matches);
                if(!empty($matches[1])){
                    if( preg_match('@kik@i', $matches[1]) ){
                        $sql = "
                        	insert ignore into growth.ig_kik_canada
                        	(name,kik_id,url)
                        	values (?,?,?)
                        ";
                        $params = array($name,$matches[1],$profileUrl);
                        $dao->prepareAndExecute( $sql, $params );
                    } else if( preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $matches[1] ) ) {
                        $sql = "
                        	insert ignore into growth.ig_kik_canada
                        	(name,kik_id,url)
                        	values (?,?,?)
                        ";
                        $params = array($name,$matches[1],$profileUrl);
                        $dao->prepareAndExecute( $sql, $params );
                    } else {
                        echo "no kik for $name\n";
                    }
                }
                
                $sleep = 1;
                echo "completed name $name - sleeping for $sleep seconds\n";
                sleep($sleep);
            }
        }
    }
    
    /**
     * parse kik ids out of the db data we collectd from Keek
     */
    public static function parseKikIds(){
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $sql = "select kik_id from growth.ig_kik_canada";
        $stmt = $dao->prepareAndExecute( $sql );
        $kikStrings = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
        foreach( $kikStrings as $kikString ){
            $kikString = strip_tags($kikString);
            $ptrn = '@kik[\W\s]*([\w]+)@is';
            preg_match( $ptrn, $kikString, $matches );
            if( !empty( $matches[1] ) ){
                echo $matches[1]."\n";
            }
        }
    }
    
    /**
     * parse kik ids out of the db data we collectd from Keek
     */
    public static function parseEmails(){
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $ptrn = '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i';
        $sql = "select kik_id from growth.ig_kik_canada";
        $stmt = $dao->prepareAndExecute( $sql );
        $kikStrings = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
        foreach( $kikStrings as $kikString ){
            $kikString = strip_tags($kikString);
            preg_match( $ptrn, $kikString, $matches );
            if( !empty( $matches[1] ) ){
                echo $matches[1]."\n";
            }
        }
    }
    
    public static function collectKikIds(){
        $g = new BIM_Growth();
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $pageUrl = "http://web.stagram.com/keyword/kik/";
        while( $pageUrl ){
            $response = $g->get( $pageUrl );
            $ptrn = '@class="username">(.*?)</a>@';
            preg_match_all($ptrn, $response, $matches);
            if( isset( $matches[1] ) ){
                $names = $matches[1];
            }
            $names = array_unique( $names );
            
            $sleep = 1;
            echo "sleeping for $sleep seconds after fetching $pageUrl\n";
            sleep( $sleep );
            
            $ptrn = '@<a href="(.*?)" rel="next">@';
            preg_match($ptrn, $response, $matches);
            if( isset( $matches[1] ) ){
                $pageUrl = 'http://web.stagram.com'.$matches[1];
            } else {
                $pageUrl = null;
            }

            foreach( $names as $name ){
                $profileUrl = "http://web.stagram.com/n/$name/";
                $response = $g->get( $profileUrl );
                
                $matches = array();
                $ptrn = '@class="ui_tools".*?style="padding-top:5px;">(.*?)</p>@is';
                preg_match($ptrn, $response, $matches);
                if(!empty($matches[1])){
                    if( preg_match('@kik@i', $matches[1]) ){
                        echo "found kik for $name\n";
                        $sql = "
                        	insert ignore into growth.ig_kik
                        	(name,kik_id,url)
                        	values (?,?,?)
                        ";
                        $params = array($name,$matches[1],$profileUrl);
                        $dao->prepareAndExecute( $sql, $params );
                    } else if( preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $matches[1] ) ) {
                        echo "found email for $name\n";
                        $sql = "
                        	insert ignore into growth.ig_kik
                        	(name,kik_id,url)
                        	values (?,?,?)
                        ";
                        $params = array($name,$matches[1],$profileUrl);
                        $dao->prepareAndExecute( $sql, $params );
                    } else {
                        echo "no kik or email for $name\n";
                    }
                }
                
                $sleep = 1;
                echo "completed name $name - sleeping for $sleep seconds\n";
                sleep($sleep);
            }
        }
    }
    
    public static function getPromoterEmails(){
        $sql = "select * from growth.ig_promoters";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $stmt = $dao->prepareAndExecute($sql);
        $promoters = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        
        $g = new BIM_Growth();
        foreach( $promoters as $promoter ){
            $profileUrl = $promoter->url;
            // echo "getting $promoter->url\n";
            $response = $g->get( $profileUrl );
            
            $matches = array();
            $ptrn = '@class="ui_tools".*?style="padding-top:5px;">(.*?)</p>@is';
            preg_match($ptrn, $response, $matches);
            if( !empty($matches[1] ) ){
                $bio = strip_tags($matches[1]);
                // print "$bio\n";
                if( preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $bio, $matches ) ) {
                    echo $matches[0]."\n";
                    $sql = "update growth.ig_promoters set email = ? where url = ?";
                    $params = array( $matches[0], $promoter->url);
                    $dao->prepareAndExecute($sql,$params);
                } else {
                    echo "no email for $promoter->name\n";
                }
            }
            
            $sleep = 1;
            sleep($sleep);
        }
    }
    
    /**
     * we get the list of urls for the pics we want to monitor
     * foreach pic
     * 		we call the likers list
     * 		foreach liker
     * 			queue a job that will follow and like them
     * 			the job will randomly select a persona
     * 			the persona will then follow the user, like some stuff
     * 			then drop a comment on the user with a hashtag to an ad
     * 
     * 
     */
    
    public static function queueFollowAdLikers( $persona ){
        $pics = array(
			'600807021358813760_347907499',
            '600837194437381980_281104626',
            '600870594215298409_53232960',
            '600898470807201548_322235632',
            '600930843681716611_209278677',
            '600960409872439060_264120324'
        );
        // http://web.stagram.com/p/
        $me = new self( $persona );
        if( $me->handleLogin() ){
            $likeBaseUrl = 'http://web.stagram.com/get_likers/';
            $time = time();
            foreach( $pics as $picId ){
                $url = "{$likeBaseUrl}{$picId}";
                $likers = json_decode( $me->get( $url ) );
                if( $likers ){
                    print_r( array( "likers : ", count($likers->message) ) );
                    if( !empty( $likers->message ) ){
                        foreach( $likers->message as $liker ){
                            if( self::canDoPing($liker->id) ){
                                self::queueTargetedBlastJob( $liker );
                            }
                        }
                    }
                } else {
                    error_log( "$url seems to be missing." );
                }
                self::sleep(1,"queued ad likers");
            }
        }
    }
    
    public static function queueTargetedBlastJob( $liker ){
        $personaId = self::getRandomPersonaId();
        
        mt_srand();
        $seconds = mt_rand( 1, 86400 * 5 );
        $targetDate = time() + $seconds;
        
        $date = new DateTime();
        $date->setTimestamp($targetDate);
        $targetDate = $date->format('Y-m-d H:i:s');
        
        $target = (object) array(
            'id' => $liker->id,
            'name' => $liker->username,
            'url' => "http://web.stagram.com/n/$liker->username",
        	'selfie' => ''
        );
        
        $tag = self::getUniqueTag();
        $comment = BIM_Utils::getGeneralSelfieClubComment( $tag );
        
        $job = (object) array(
            'nextRunTime' => $targetDate,
            'class' => 'BIM_Jobs_Growth',
            'method' => 'doBlastJob',
            'name' => 'do_blast_job',
            'params' => array(
                'persona_id' => $personaId,
                'target' => $target,
                'comment' => $comment,
            ),
            'is_temp' => true,
            'disabled' => 0
        );
        
        $j = new BIM_Jobs_Gearman();
        error_log( "will follow, like and comment on $target->name commenting '$comment' with persona $personaId on $targetDate");
        $j->createJbb($job);
    }
    
    public static function getRandomPersonaId(){
        $id = null;
        if(empty(self::$personaNames)){
            $dao = new BIM_DAO_Mysql( BIM_Config::db() );
            $sql = "select name from growth.persona where network = 'instagram'";
    		$stmt = $dao->prepareAndExecute( $sql );
    		self::$personaNames = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
        }
        if( !empty(self::$personaNames) ){
            $id = self::$personaNames[ array_rand( self::$personaNames ) ];
        }
        return $id;
    }
}
