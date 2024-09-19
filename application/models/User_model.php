<?php
class User_model extends CI_Model {
	function __construct() {
		$this->load->database();
	}

	function save($user) {
		$this->db->insert('users', $user);
		return $this->db->insert_id();
	}

	function read($id) {
		return $this->db->get_where('users', ['id' => $id])->row();
	}

	function find_all() {
		return $this->db->get('users')->result();
	}

	function update($user, $id) {
		$this->db->update('users', $user, ['id' => $id]);
	}

	function delete($id) {
    $this->db->delete('users', ['id' => $id]);
  }
}