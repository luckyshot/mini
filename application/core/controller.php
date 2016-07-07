<?php

class Controller
{
	/**
	* @var null Database Connection
	*/
	public $db = null;

	/**
	* @var null Model
	*/
	public $model = null;

	/**
	* Whenever controller is created, open a database connection too and load "the model".
	*/
	function __construct()
	{
		// Gobal Models
		$this->openDatabaseConnection();

		require APP . 'model/user.php';
		$this->user = new UserModel( $this->db );

		//getting the className of the Controller who extends controller class
		$class = get_called_class();

		//passing this classname as parameter to loadModel Function
		$this->loadModel( $class );
	}


	/**
	* Open the database connection with the credentials from application/config/config.php
	*/
	private function openDatabaseConnection()
	{
		// set the (optional) options of the PDO connection. in this case, we set the fetch mode to
		// "objects", which means all results will be objects, like this: $result->user_name !
		// For example, fetch mode FETCH_ASSOC would return results like this: $result["user_name] !
		// @see http://www.php.net/manual/en/pdostatement.fetch.php
		$options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

		// generate a database connection, using the PDO connector
		// @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
		$this->db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, $options);
	}


	/**
	* Loads the "model".
	* @return object model
	*/
	public function loadModel( $class = null )
	{
		(string)$modelFile = strtolower( $class );

		// If a Controller doesn't have a model, ignore it
		if ( !file_exists( APP . 'model/'.$modelFile.'.php' ) ) return;

		// If the Class is already loaded, ignore it
		if ( !class_exists($class.'Model') ){
			require APP . 'model/'.$modelFile.'.php';
		}

		$instanceClass = $class.'Model';
		// create new "model" (and pass the database connection)
		$this->model = new $instanceClass( $this->db );
	}
}
