<div class="container">
	<h2><?=$this->user->profile->username?></h2>
	<p>Full name: <?=$this->user->profile->full_name?></p>
	<p>Email: <?=$this->user->profile->email?></p>
	<p>Date registered: <?=date( 'l, jS \of F Y \a\t h:i\h', strtotime($this->user->profile->date_added))?></p>
	<p>Avatar: <img src="<?=$this->user->profile->avatar?>" alt="<?=$this->user->profile->username?>"></p>
	<p>Username: <?=$this->user->profile->username?></p>

	<p><a href="<?=URL?>user/edit">Edit profile</a> &nbsp; <a href="<?=URL?>user/logout">Logout</a></p>
</div>
