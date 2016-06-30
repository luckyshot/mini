<?php

class Application
{
	/** @var null The controller */
	private $url_controller = null;

	/** @var null The method (of the above controller), often also named "action" */
	private $url_action = null;

	/** @var array URL parameters */
	private $url_params = array();

	/**
	* "Start" the application:
	* Analyze the URL elements and calls the according controller/method or the fallback
	*/
	public function __construct()
	{
		// Start PHP Session
		if (session_status() == PHP_SESSION_NONE) { session_start(); }

		// CRSF Protection
		$this->crsf();

		// create array with URL parts in $url
		$this->splitUrl();

		// check for controller: no controller given ? then load start-page
		if ( !$this->url_controller ) {

			require APP . 'controller/home.php';
			$page = new Home();
			$page->index();

		} elseif (file_exists(APP . 'controller/' . $this->url_controller . '.php')) {
			// here we did check for controller: does such a controller exist ?

			// if so, then load this file and create this controller
			// example: if controller would be "car", then this line would translate into: $this->car = new car();
			require APP . 'controller/' . $this->url_controller . '.php';
			$this->url_controller = new $this->url_controller();

			// check for method: does such a method exist in the controller ?
			if (method_exists($this->url_controller, $this->url_action)) {

				if (!empty($this->url_params)) {
					// Call the method and pass arguments to it
					call_user_func_array(array($this->url_controller, $this->url_action), $this->url_params);
				} else {
					// If no parameters are given, just call the method without parameters, like $this->home->method();
					$this->url_controller->{$this->url_action}();
				}

			} else {
				if (strlen($this->url_action) == 0) {
					// no action defined: call the default index() method of a selected controller
					$this->url_controller->index();
				}
				else {
					header('location: ' . URL . 'error');
				}
			}
		} else {
			header('location: ' . URL . 'error');
		}
	}

	/**
	* Get and split the URL
	*/
	private function splitUrl()
	{
		if ( isset($_GET['url']) ) {

			// split URL
			$url = trim($_GET['url'], '/');
			$url = filter_var($url, FILTER_SANITIZE_URL);
			$url = explode('/', $url);

			// Put URL parts into according properties
			// By the way, the syntax here is just a short form of if/else, called "Ternary Operators"
			// @see http://davidwalsh.name/php-shorthand-if-else-ternary-operators
			$this->url_controller = isset($url[0]) ? $url[0] : null;
			$this->url_action = isset($url[1]) ? $url[1] : null;

			// Remove controller and action from the split URL
			unset($url[0], $url[1]);

			// Rebase array keys and store the URL params
			$this->url_params = array_values($url);

			// for debugging. uncomment this if you have problems with the URL
			//echo 'Controller: ' . $this->url_controller . '<br>';
			//echo 'Action: ' . $this->url_action . '<br>';
			//echo 'Parameters: ' . print_r($this->url_params, true) . '<br>';
		}
	}

	/**
	* Cross-Request Site Forgery
	* Protection against this kind of vulnerability
	*/
	protected function crsf(){
		$current_token = isset($_SESSION['crsf_token']) ? $_SESSION['crsf_token'] : false;
		$_SESSION['crsf_token'] = $this->random_str();
		if (
			!in_array($_SERVER['REQUEST_METHOD'], ['GET','OPTIONS']) AND
			(!isset($_POST['crsf_token']) OR !$current_token OR $_POST['crsf_token'] !== $current_token )
		) {
			header("Location: " . URL );
			die();
		}
	}



	/**
	* Random String
	* Generate a random string based on length and pool of characters
	*/
	protected function random_str($length = 64, $pool = '_-0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
		$str = '';
		$max = mb_strlen($pool, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) {
			$str .= $pool[ mt_rand(0, $max) ];
		}
		return $str;
	}
}
