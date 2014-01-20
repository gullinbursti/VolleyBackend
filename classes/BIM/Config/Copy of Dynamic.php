<?php 

class BIM_Config_Dynamic{

    public static function session(){
        return (object) array(
            'cookie' => (object) array(
                'name' => "bim_session",
                'expires' => 86400 * 365,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'http_only' => false,
            ),
            'use' => true,
        );
    }
    
    public static function app(){
        return (object) array(
            'release_id' => 'sc0001',
        	'team_volley_id' => 2394,
            'base_path' => '/.*?/',
            'sticky_volleys' => array(),
            'auto_subscribes' => array(2394),
            'super_users' => array(''),
            'celebrities' => array(2394),
        );
    }
    
    public static function growthEmailInvites(){
        return (object) array(
        	'to_email' => 'shane@shanehill.com',
        	'to_name' => '',
        	'from_email' => 'invite@selfieclub.com',
        	'from_name' => 'Selfieclub',
        	'subject' => 'Selfieclub App - Your friends are on Selfieclub (iOS app)',
        );
    }
    
    public static function warningEmail(){
        return (object) array(
            'emailData' => (object) array(
            	'to_email' => 'shane@shanehill.com',
            	'from_email' => 'apps@builtinmenlo.com',
            	'from_name' => 'Built In Menlo',
            	'subject' => 'Warning From Botlandia!!',
            	'text' => '',
            ),

            'smtp' =>  self::smtp()
        );
    }
    
    public static function smtp(){
        return (object) array(
            'host' => 'smtp.mandrillapp.com',
            'port' => 587,
            'username' => 'apps@builtinmenlo.com',
            'password' => 'JW-zqg_yVLs7suuVBwG_xw'
        );
    }
    
    public static function queueFuncs(){
        return array(
            'BIM_Controller_Votes' => array(
                'upvoteChallenge' => array( 'queue' => false ),
            )
        );
    }
    
    public static function staticFuncs(){
        return array(
            'BIM_Controller_Discover' => array(
                'getTopChallengesByVotes' => array( 
                    'redirect' => false,
                	'url' => "http://54.243.163.24/getTopChallengesByVotes.js",
                	"path" => '/var/www/discover.getassembly.com/getTopChallengesByVotes.js.gz'
                ),
            ),
        	'BIM_Controller_Votes' => array(
                'getChallengesByDate' => array( 
                    'redirect' => false,
                	'url' => "http://54.243.163.24/getChallengesByDate.js",
                	"path" => '/var/www/discover.getassembly.com/getChallengesByDate.js.gz'
                ),
                'getChallengesByActivity' => array( 
                    'redirect' => false,
                	'url' => "http://54.243.163.24/getChallengesByActivity.js",
                	"path" => '/var/www/discover.getassembly.com/getChallengesByActivity.js.gz'
                ),
            )
        );
    }
    
    public static function instagram(){
        return (object) array(
            'link_for_bio' => 'http://getvolleyapp.com/b/beer',
            'api' => (object) array(
                'client_id' => '63a3a9e66f22406799e904ccb91c3ab4',
                'client_secret' => 'e09ed527c6cc43c897c80e59d7e9c137',
                'access_token_url' => 'https://api.instagram.com/oauth/access_token',
                'redirect_url' => "http://54.243.163.24/instagram_oauth.php"
        
            ),
            'harvestSelfies' => (object) array(
                'secsInPast' => 3600 * 24,
                'maxItems' => 10000,
                'tags' => array(
                    'selfie', 'me', 'self', 'selfpic', 'bff', 'myface', 'rateme', 'duckface'
                )
            )
        );
    }
    
    public static function twilio(){
        return (object) array(
            'api' => (object) array(
                'accountSid' => 'ACb76dc4d9482a77306bc7170a47f2ea47',
                'authToken' => '00015969db460ffe0f0bd5b3df60972a',
                'number' => '2394313268'
            ),
        );
    }
    
    public static function tumblr(){
        return (object) array(
            'api' => (object) array(
                'consumerKey' => '9Y4GwOX4xaIo7l4UW4QI5ltnUlA5wyWlQ1WsH6g0Tsigmmzn3Z',
                'consumerSecret' => '4Osw8hwwRkdyvHHtWxdFPzsNfYFKJKEbaOOasqpmSUkcoiQm2Q',
            ),
            'harvestSelfies' => (object) array(
                'secsInPast' => 3600 * 24,
                'maxItems' => 10000,
                'tags' => BIM_Config::authenticTags()
            ),
            'urls' => (object) array(
                'login' => 'https://www.tumblr.com/login',
                'oauth' => (object) array(
                	'callback' => 'http://54.243.163.24/tumblr_oauth.php',
                	'authorize' => 'http://www.tumblr.com/oauth/authorize',
                	'access_token' => 'http://www.tumblr.com/oauth/access_token',
                	'request_token' => 'http://www.tumblr.com/oauth/request_token',
            ))
        );
    }
    
    public static function gearman(){
        return (object) array(
            'servers' => array(  
                array(
                    'host' => '10.210.49.252',
                    'port' => 4730
                ),
            ),
        );
    }
    
    public static function memcached(){
        return (object) array(
            'servers' => array(  
                (object) array(
                    'host' => '127.0.0.1',
                    'port' => 11211
                ),
            ),
        );
    }
    
    public static function cache(){
        return (object) array(
            'memcached' => (object) array(
                'servers' => array(  
                    (object) array(
                        'host' => '127.0.0.1',
                        'port' => 11211
                    ),
                ),
            )
        );
    }
    
    public static function db(){
        return (object) array(
        	'locatorClassName' => 'BIM_Data_Locator_Simple',
        	'locatorClassPath' => 'BIM/Data/Locator/Simple.php',
        	'nodes' => array(
        		(object) array(
        			'writer' => (object) array(
        				'host' => '127.0.0.1',
        				'user' => 'root',
        				'pass' => '',
        				'dbname' => 'queue'
        			),
        			'reader' => (object) array(
        				'host' => '127.0.0.1',
        				'user' => 'root',
        				'pass' => '',
        				'dbname' => 'queue'
        			),
        		),
        	),
        );
    }
    
    public static function urbanAirship(){
        return (object) array(
            'api' => (object) array(
                //'pass_key' => "qJAZs8c4RLquTcWKuL-gug:mbNYNOkaQ7CZJDypDsyjlQ", // dev
                'pass_key' => "dPvBDEnwSOaK0sFZaVWrmg:O1J_EKgwR4OLzdtC6oObEA", // app_key:app_master_secret
                'push_url' => 'https://go.urbanairship.com/api/push/',
            ),
        );
    }
    
    public static function elasticSearch(){
        return (object) array(
            'api_root' => 'http://127.0.0.1:9200'
        );
    }
    
    public static function sms(){
        return (object) array(
            'code_pattern' => '@\b(c1.+?1c)\b@i',
            'secret' => 'jkfglakjsdghlksdjghl',
            'useHashing' => true,

            'blowfish' => (object) array(
                'b64iv' => 'hDfslH7tj3M=',
                'key' => 'KJkljP9898kljbm675865blkjghoiubdrsw3ye4jifgnRDVER8JND997',
            )
        );
    }
    
    public static function aws(){
        return (object) array(
            'access_key' => 'AKIAJVS6Y36AQCMRWLQQ',
            'secret_key' => '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'
        );
    }
    
	public static function proxies(){
        return (object) array(
                    'useProxies' => false,
                    'proxies' => array(
            			'209.164.89.38:21320',
            			'198.204.248.43:21260',
            			'209.164.76.35:21264',
            			'192.161.48.195:21325',
            			'50.118.142.153:21277',
            			'216.172.144.53:21246',
            			'192.161.164.222:21307',
            			'74.211.96.165:21318',
            			'192.64.33.35:21306',
            			'50.117.15.254:21322',
            			'209.164.89.36:21237',
            			'198.204.248.54:21267',
            			'209.164.76.43:21231',
            			'192.161.48.160:21267',
            			'50.118.142.170:21326',
            			'216.172.144.193:21263',
            			'192.161.164.128:21230',
            			'74.211.96.43:21262',
            			'192.64.33.11:21243',
            			'50.117.15.170:21264',
            			'209.164.89.32:21309',
            			'198.204.248.46:21271',
            			'209.164.76.31:21290',
            			'192.161.48.215:21309',
            			'50.118.142.120:21248',
            			'216.172.144.106:21296',
            			'192.161.164.133:21237',
            			'74.211.96.188:21291',
            			'192.64.33.26:21307',
            			'50.117.15.29:21272',
            			'209.164.89.43:21273',
            			'198.204.248.55:21326',
            			'209.164.76.45:21314',
            			'192.161.48.218:21247',
            			'50.118.142.194:21247',
            			'216.172.144.8:21289',
            			'192.161.164.55:21271',
            			'74.211.96.151:21316',
            			'192.64.33.46:21310',
            			'50.117.15.149:21268',
            			'209.164.89.41:21320',
            			'198.204.248.45:21301',
            			'209.164.76.33:21272',
            			'192.161.48.231:21299',
            			'50.118.142.80:21267',
            			'216.172.144.3:21294',
            			'192.161.164.156:21249',
            			'74.211.96.93:21297',
            			'192.64.33.72:21284',
            			'50.117.15.40:21303',
            			'209.164.89.42:21304',
            			'198.204.248.52:21330',
            			'209.164.76.30:21253',
            			'192.161.48.192:21325',
            			'50.118.142.229:21302',
            			'216.172.144.172:21288',
            			'192.161.164.217:21320',
            			'74.211.96.86:21295',
            			'192.64.33.66:21247',
            			'50.117.15.203:21311',
            			'209.164.89.31:21289',
            			'198.204.248.36:21255',
            			'209.164.76.41:21309',
            			'192.161.48.247:21254',
            			'50.118.142.79:21307',
            			'216.172.144.217:21261',
            			'192.161.164.218:21309',
            			'74.211.96.241:21232',
            			'192.64.33.70:21326',
            			'50.117.15.214:21239',
            			'209.164.89.45:21327',
            			'198.204.248.49:21251',
            			'209.164.76.36:21263',
            			'192.161.48.219:21308',
            			'50.118.142.57:21279',
            			'216.172.144.84:21300',
            			'192.161.164.90:21240',
            			'74.211.96.36:21277',
            			'192.64.33.56:21323',
            			'50.117.15.242:21284',
            			'209.164.89.30:21280',
            			'198.204.248.42:21310',
            			'209.164.76.37:21257',
            			'192.161.48.200:21296',
            			'50.118.142.200:21235',
            			'216.172.144.122:21237',
            			'192.161.164.33:21270',
            			'74.211.96.144:21238',
            			'192.64.33.14:21236',
            			'50.117.15.205:21322',
            			'209.164.89.44:21232',
            			'198.204.248.44:21265',
            			'209.164.76.32:21279',
            			'192.161.48.230:21277',
            			'50.118.142.94:21275',
            			'216.172.144.77:21304',
            			'192.161.164.16:21266',
            			'74.211.96.83:21300',
            			'192.64.33.108:21236',
            			'50.117.15.252:21305',
                    ),
		);
	}
}
