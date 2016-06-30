<div class="container">
	<h2><?=$this->user->profile->username?></h2>
	<form action="<?=URL?>user/edit_action" method="post">
		<input type="hidden" name="crsf_token" value="<?=$_SERVER['crsf_token']?>">
		<p>
			<label for="email">Email</label>
			<input type="email" name="email" value="<?=$this->user->profile->email?>" placeholder="name@email.com">
		</p>
		<p><button type="submit">Save details</button></p>
	</form>
</div>
