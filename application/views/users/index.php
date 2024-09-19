<h2>Users</h2>
<p>
	<?= anchor('users/create', 'Create users') ?>
</p>
<table>
	<tr>
		<th>Username</t>
		<th>Password</t>

		<th></th>
	</tr>
	<?php foreach ($users as $user): ?>
		<tr>
			<td><?= $user->username ?></td>
			<td><?= $user->password ?></td>

			<td>
				<?= anchor('users/edit/' . $user->id, 'Edit'); ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>