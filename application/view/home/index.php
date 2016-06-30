<div class="container">
	<h2>You are in the View: application/view/home/index.php (everything in the box comes from this file)</h2>
	<p>In a real application this could be the homepage.</p>

	<?php if ( $this->user->profile ) { ?>
		<p>Hello <?=$this->user->profile->username?>! How are you? Check your profile <a href="<?php echo URL; ?>user">here</a>.</p>
	<?php }else{ ?>
		<p>Hello stranger! Want to register or log in? <a href="<?php echo URL; ?>user">Signup / Login</a>.</p>
	<?php } ?>

</div>
