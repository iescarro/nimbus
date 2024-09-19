<h2>Edit user</h2>
<?= form_open('users/edit/' . $user->id) ?>		
	<p>
		Username<br>
		<?= form_input('username', $user->username, ''); ?>
	</p>
	<p>
		Password<br>
		<?= form_input('password', $user->password, ''); ?>
	</p>

	<p>
		<?= form_submit('submit', 'Update user') ?>
		or <?= anchor('users', 'cancel'); ?>
	</p>
<?= form_close() ?>

<?= form_open('users/delete/' . $user->id, array('onsubmit', 'return confirmDelete')) ?>
	<?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
	<button type='submit'>Delete</button>
<?= form_close() ?>