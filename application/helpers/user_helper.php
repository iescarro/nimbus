<?php
function user_form() {
	$obj = &get_instance();
	return [
		'username' => $obj->input->post('username'),
		'password' => $obj->input->post('password'),

	];
}