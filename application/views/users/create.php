<h2>Create user</h2>
<?= form_open('users/create'); ?>
	<p>
		Username<br>
		<?= form_input('username', '', ''); ?>
	</p>
	<p>
		Password<br>
		<?= form_input('password', '', ''); ?>
	</p>

	<p>
		<?= form_submit('submit', 'Save user'); ?>
		or <?= anchor('users', 'cancel'); ?>
	</p>
<?= form_close(); ?>