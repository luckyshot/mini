<?php

class User extends Controller
{

	public function index()
	{
		if ( $this->user->profile ){
			require APP . 'view/_templates/header.php';
			require APP . 'view/user/index.php';
			require APP . 'view/_templates/footer.php';
		}else{
			$login_url = $this->model->login();
			header("Location: " . $login_url );
		}
	}

	public function logout()
	{
		$this->model->logout();

		$redirect = URL_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	}

	public function callback(){
		$this->model->callback();

		// redirect to same page to remove url parameters
		$redirect = URL_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	}


	public function edit(){
		if ( $this->user->profile ){
			require APP . 'view/_templates/header.php';
			require APP . 'view/user/edit.php';
			require APP . 'view/_templates/footer.php';
		}else{
			header("Location: " . URL . "user");
		}
	}

	public function edit_action(){
		if ( $this->user->edit_user() ) {
			header("Location: " . URL . "user");
		}else{
			header("Location: " . URL . "user/edit");
		}
	}

}
