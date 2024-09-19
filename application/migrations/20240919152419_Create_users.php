<?php
class Migration_Create_users extends CI_Migration {
	function up() {
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 5,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'username' => array(
				'type' => 'varchar',
				'null' => TRUE,
			),
			'password' => array(
				'type' => 'varchar',
				'null' => TRUE,
			),

		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('users');
	}

	function down() {
		$this->dbforge->drop_table('users');
	}
}