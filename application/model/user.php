<?php

class UserModel
{
	public $profile = false;

	/**
	* @param object $db A PDO database connection
	*/
	function __construct($db)
	{
		try {
			$this->db = $db;
		} catch (PDOException $e) {
			exit('Database connection could not be established.');
		}

		require_once(__DIR__ . '/../libs/twitter-login/twitteroauth/OAuth.php');
		require_once(__DIR__ . '/../libs/twitter-login/twitteroauth/twitteroauth.php');

		$this->load_user();
	}



	public function login()
	{
		// create a new twitter connection object
		$twitterOAuth = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET );
		// get the token from connection object
		$request_token = $twitterOAuth->getRequestToken( OAUTH_CALLBACK );
		// echo "<pre>";var_dump( $request_token );echo "</pre>";die();
		// if request_token exists then get the token and secret and store in the session
		if ( $request_token ){
			$token = $request_token['oauth_token'];
			$_SESSION['request_token'] = $token ;
			$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];
			// get the login url from getauthorizeurl method
			return $twitterOAuth->getAuthorizeURL( $token );
		}
		return false;
	}

	public function logout()
	{
		session_unset();
		setcookie( SESSION_TOKEN_NAME, null, -1, '/', URL_DOMAIN );
		unset( $_COOKIE[ SESSION_TOKEN_NAME ] );
	}

	public function callback(){
		// create a new twitter connection object with request token
		$connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['request_token'], $_SESSION['request_token_secret'] );
		// get the access token from getAccesToken method
		$access_token = $connection->getAccessToken( $_REQUEST['oauth_verifier'] );

		if ( $access_token ){
			// create another connection object with access token
			$connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret'] );
			// set the parameters array with attributes include_entities false
			$params = array(
				'include_entities'=>'false',
				// NOTE: Uncomment the following line to get the user's email address 
				//       directly from Twitter. You will need to ask for the 
				//       "Request email addresses from users" special permission here: 
				//       https://support.twitter.com/forms/platform
				//       Then follow these instructions:
				//       https://dev.twitter.com/rest/reference/get/account/verify_credentials
				// 'include_email' => 'true',
			);
			// get the data
			$data = $connection->get( 'account/verify_credentials', $params );
			if ( $data ){
				$this->create_user( $data, $access_token );
				return $data;
			}
		}
		return false;
	}

	protected function create_user( $data, $access_token )
	{
		$query = $this->db->prepare("SELECT id FROM user WHERE username = :username LIMIT 1;");
		$query->execute([':username' => $data->screen_name]);
		$user = $query->fetch();

		if ( $user )
		{
			$this->save_token( $data->screen_name );
			return false;
		}

		$query = $this->db->prepare("INSERT INTO user (id, username, full_name, email, avatar, session_token, oauth_token, oauth_token_secret, date_added)
		VALUES (NULL, :username, :full_name, :email, :profile_image_url, :session_token, :oauth_token, :oauth_token_secret, :date_added);");
		$query->execute([
			':username' => $data->screen_name,
			':full_name' => $data->name,
			':email' => @$data->email, // if you haven't enabled email addresses from Twitter the '@' will make it fail silently
			':profile_image_url' => $data->profile_image_url,
			':session_token' => $this->random_str(),
			':oauth_token' => $access_token['oauth_token'],
			':oauth_token_secret' => $access_token['oauth_token_secret'],
			':date_added' => date('Y-m-d H:i:s', time()),
		]);

		$this->save_token( $data->screen_name );
	}


	public function save_token( $username ){

		$query = $this->db->prepare("SELECT session_token FROM user WHERE username = :username LIMIT 1;");
		$query->execute([':username' => $username]);
		$user = $query->fetch();

		setcookie( SESSION_TOKEN_NAME, $user->session_token, time() + SESSION_TOKEN_DURATION, '/', URL_DOMAIN );
		$_COOKIE[ SESSION_TOKEN_NAME ] = $user->session_token;
		return true;
	}


	public function load_user()
	{

		if ( $this->profile )
		{
			return $this->profile;
		}

		if ( !isset($_COOKIE[ SESSION_TOKEN_NAME ]))
		{
			return false;
		}

		$query = $this->db->prepare("SELECT * FROM user WHERE session_token = :session_token LIMIT 1;");
		$query->execute([':session_token' => $_COOKIE[ SESSION_TOKEN_NAME ]]);
		$user = $query->fetch();
		if ( $user )
		{
			$this->profile = $user;
			return $user;
		}
		return false;
	}



	public function edit_user(){
		// is registered
		if ( !$this->profile ) return false;

		// Validation
		if ( !isset($_POST['email']) OR !filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL ) ) return false;

		$query = $this->db->prepare("UPDATE user SET email = :email WHERE id = :id;");
		$result = $query->execute([
			':id' => $this->profile->id,
			':email' => $_POST['email'],
		]);

		return $result;
	}






	// TODO: This method is duplicated in application.php, could we avoid duplicate code?
	protected function random_str($length = 64, $keyspace = '_-0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
		$str = '';
		$max = mb_strlen($keyspace, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) {
			$str .= $keyspace[ mt_rand(0, $max) ];
		}
		return $str;
	}


}
