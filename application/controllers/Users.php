<?php

class Users extends CI_Controller {
	var $user_model;

	function __construct() {
		parent::__construct();
		$this->load->helper(['html', 'url', 'form', 'user']);
		$this->load->library('form_validation');
		$this->load->model('user_model');
	}

	function index() {
		$data['users'] = $this->user_model->find_all();
		$this->load->view('users/index', $data);
	}

	function create() {
		if ($this->input->post()) {
			$user = user_form();
			$this->user_model->save($user);
      redirect('users');
		}
		$this->load->view('users/create');
	}

	function edit($id) {
		if ($this->input->post()) {
			$user = user_form();
			$this->user_model->update($user, $id);
      redirect('users');
		}
		$data['user'] = $this->user_model->read($id);
		$this->load->view('users/edit', $data);
	}

	function delete($id) {
		$this->user_model->delete($id);
		redirect('users');
	}
}