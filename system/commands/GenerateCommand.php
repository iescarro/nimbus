<?php

namespace CodeIgniter\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

class GenerateCommand extends Command
{
	protected static $defaultName = 'generate:scaffold';

	protected function configure()
	{
		$this
			->setName('generate:scaffold')
			->setDescription('')
			->addArgument('component', InputArgument::REQUIRED, 'The component to run (e.g., scaffold)')
			->addArgument('fields', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Fields to scaffold (e.g., title:string content:text)');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$component = $input->getArgument('component');
		$fields = $input->getArgument('fields');

		// if ($component !== 'scaffold') {
		// 	$output->writeln('<error>Unknown command. Only "scaffold" is supported.</error>');
		// 	return Command::INVALID;
		// }

		// $parsedFields = [];
		// foreach ($fields as $field) {
		// 	[$name, $type] = explode(':', $field);
		// 	$parsedFields[$name] = $type;
		// }

		// $output->writeln('Generating scaffold with the following fields:');
		// foreach ($parsedFields as $name => $type) {
		// 	$output->writeln("- $name ($type)");
		// }

		$generator = new Generator($component, $fields);
		$generator->scaffold();

		return Command::SUCCESS;
	}
}

class Generator
{
	private $component;
	private $fields;

	function __construct($component, $fields)
	{
		$this->component = $component;
		foreach ($fields as $field) {
			[$name, $type] = explode(':', $field);
			$this->fields[$name] = $type;
		}
	}

	function scaffold()
	{
		$this->generate_model();
		$this->generate_helper();
		$this->generate_migration();
		$this->generate_controller();
		$this->generate_views();
	}

	function generate_model()
	{
		$dir = 'application/models';
		$class = ucwords($this->component) . '_model';
		$var = '$' . lcfirst($this->component);
		$table = lcfirst(pluralize($this->component));
		$filename = $dir . '/' . $class  . '.php';
		$content = "<?php
class {class} extends CI_Model {
	function __construct() {
		\$this->load->database();
	}

	function save({var}) {
		\$this->db->insert('{table}', {var});
		return \$this->db->insert_id();
	}

	function read(\$id) {
		return \$this->db->get_where('{table}', ['id' => \$id])->row();
	}

	function find_all() {
		return \$this->db->get('{table}')->result();
	}

	function update({var}, \$id) {
		\$this->db->update('{table}', {var}, ['id' => \$id]);
	}

	function delete(\$id) {
    \$this->db->delete('{table}', ['id' => \$id]);
  }
}";
		$content = str_replace(
			['{var}', '{table}', '{class}'],
			[$var, $table, $class],
			$content
		);
		file_put_contents($filename, $content);
	}

	function generate_helper()
	{
		$dir = 'application/helpers';
		$component = lcfirst($this->component);
		$helper = $component . '_helper';
		$var = '$' . lcfirst($component);
		$table = lcfirst(pluralize($component));
		$filename = $dir . '/' . $helper  . '.php';
		$content = "<?php
function {component}_form() {
	\$obj = &get_instance();
	return [
{fields}
	];
}";
		$fields = '';
		foreach ($this->fields as $column => $type) {
			$header = ucfirst($column);
			$fields .= "		'{$column}' => \$obj->input->post('{$column}'),\n";
		}
		$content = str_replace(
			['{var}', '{table}', '{component}', '{fields}'],
			[$var, $table, $component, $fields],
			$content
		);
		file_put_contents($filename, $content);
	}

	function generate_migration()
	{
		$dir = 'application/migrations';
		$table = lcfirst(pluralize($this->component));
		$class = 'Create_' . $table;
		$var = '$' . lcfirst($this->component);
		$filename = $dir . '/' . date('YmdHis')  . '_' . $class . '.php';
		$content = "<?php
class Migration_{class} extends CI_Migration {
	function up() {
		\$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 5,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
{columns}
		));
		\$this->dbforge->add_key('id', TRUE);
		\$this->dbforge->create_table('{table}');
	}

	function down() {
		\$this->dbforge->drop_table('{table}');
	}
}";
		$columns = '';

		foreach ($this->fields as $column => $type) {
			$columns .= "			'$column' => array(
				'type' => '$type',
				'null' => TRUE,
			),\n";
		}
		$content = str_replace(
			['{table}', '{class}', '{columns}'],
			[$table, $class, $columns],
			$content
		);
		file_put_contents($filename, $content);
	}

	function generate_controller()
	{
		$dir = 'application/controllers';
		$class = ucwords(pluralize($this->component));
		$component = lcfirst($this->component);
		$var = '$' . $component;
		$table = pluralize($component);
		$model = $component . '_model';
		$var_model = '$' . $model;
		$filename = $dir . '/' . $class  . '.php';
		$content = "<?php

class {class} extends CI_Controller {
	var {var_model};

	function __construct() {
		parent::__construct();
		\$this->load->helper(['html', 'url', 'form', '{component}']);
		\$this->load->library('form_validation');
		\$this->load->model('{model}');
	}

	function index() {
		\$data['{table}'] = \$this->{model}->find_all();
		\$this->load->view('{table}/index', \$data);
	}

	function create() {
		if (\$this->input->post()) {
			{var} = {component}_form();
			\$this->{model}->save({var});
      redirect('{table}');
		}
		\$this->load->view('{table}/create');
	}

	function edit(\$id) {
		if (\$this->input->post()) {
			{var} = {component}_form();
			\$this->{model}->update({var}, \$id);
      redirect('{table}');
		}
		\$data['{component}'] = \$this->{model}->read(\$id);
		\$this->load->view('{table}/edit', \$data);
	}

	function delete(\$id) {
		\$this->{model}->delete(\$id);
		redirect('{table}');
	}
}";
		$content = str_replace(
			['{var}', '{table}', '{model}', '{class}', '{var_model}', '{component}'],
			[$var, $table, $model, $class, $var_model, $component],
			$content
		);
		file_put_contents($filename, $content);
	}

	function generate_views()
	{
		$component = lcfirst($this->component);
		$var = '$' . $component;
		$vars = '$' . pluralize($component);
		$models = ucfirst(pluralize($component));
		$model = pluralize($component);
		$table = pluralize($component);
		$dir = 'application/views/' . pluralize($component);
		if (!is_dir($dir)) {
			if (mkdir($dir, 0755, true)) {
			}
		}

		// Index
		$filename = $dir . '/index.php';
		$content = "<h2>{models}</h2>
<p>
	<?= anchor('{table}/create', 'Create {model}') ?>
</p>
<table>
	<tr>
{headers}
		<th></th>
	</tr>
	<?php foreach ({vars} as {var}): ?>
		<tr>
{columns}
			<td>
				<?= anchor('{table}/edit/' . {var}->id, 'Edit'); ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>";
		$headers = '';
		$columns = '';
		foreach ($this->fields as $column => $type) {
			$header = ucfirst($column);
			$headers .= "		<th>{$header}</t>\n";
			$columns .= "			<td><?= {$var}->{$column} ?></td>\n";
		}
		$content = str_replace(
			['{var}', '{vars}', '{models}', '{model}', '{headers}', '{columns}', '{table}'],
			[$var, $vars, $models, $model, $headers, $columns, $table],
			$content
		);
		file_put_contents($filename, $content);

		// Create
		$filename = $dir . '/create.php';
		$content = "<h2>Create {component}</h2>
<?= form_open('{table}/create'); ?>
{fields}
	<p>
		<?= form_submit('submit', 'Save {component}'); ?>
		or <?= anchor('{table}', 'cancel'); ?>
	</p>
<?= form_close(); ?>";
		$fields = '';
		foreach ($this->fields as $column => $type) {
			$header = ucfirst($column);
			$fields .= "	<p>
		{$header}<br>
		<?= form_input('$column', '', ''); ?>
	</p>\n";
		}
		$content = str_replace(
			['{component}', '{vars}', '{models}', '{model}', '{fields}', '{table}'],
			[$component, $vars, $models, $model, $fields, $table],
			$content
		);
		file_put_contents($filename, $content);

		// Edit
		$filename = $dir . '/edit.php';
		$content = "<h2>Edit {component}</h2>
<?= form_open('{table}/edit/' . {var}->id) ?>		
{fields}
	<p>
		<?= form_submit('submit', 'Update {component}') ?>
		or <?= anchor('{table}', 'cancel'); ?>
	</p>
<?= form_close() ?>

<?= form_open('{table}/delete/' . {var}->id, array('onsubmit', 'return confirmDelete')) ?>
	<?php echo form_hidden(\$this->security->get_csrf_token_name(), \$this->security->get_csrf_hash()); ?>
	<button type='submit'>Delete</button>
<?= form_close() ?>";
		$fields = '';
		foreach ($this->fields as $column => $type) {
			$header = ucfirst($column);
			$fields .= "	<p>
		{$header}<br>
		<?= form_input('$column', {$var}->{$column}, ''); ?>
	</p>\n";
		}
		$content = str_replace(
			['{var}', '{vars}', '{models}', '{model}', '{fields}', '{columns}', '{table}', '{component}'],
			[$var, $vars, $models, $model, $fields, $columns, $table, $component],
			$content
		);
		file_put_contents($filename, $content);
	}
}

function pluralize($word)
{
	$plural = [
		'/(quiz)$/i' => "$1zes",
		'/^(ox)$/i' => "$1en",
		'/([m|l])ouse$/i' => "$1ice",
		'/(matr|vert|ind)(ix|ex)$/i' => "$1ices",
		'/(x|ch|ss|sh)$/i' => "$1es",
		'/([^aeiouy]|qu)y$/i' => "$1ies",
		'/(hive)$/i' => "$1s",
		'/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
		'/(shea|lea|loa|thie)f$/i' => "$1ves",
		'/sis$/i' => "ses",
		'/([ti])um$/i' => "$1a",
		'/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
		'/(bu)s$/i' => "$1ses",
		'/(alias)$/i' => "$1es",
		'/(octop)us$/i' => "$1i",
		'/(ax|test)is$/i' => "$1es",
		'/(us)$/i' => "$1es",
		'/s$/i' => "s",
		'/$/' => "s"
	];

	$irregular = [
		'move' => 'moves',
		'foot' => 'feet',
		'goose' => 'geese',
		'sex' => 'sexes',
		'child' => 'children',
		'man' => 'men',
		'tooth' => 'teeth',
		'person' => 'people'
	];

	$uncountable = [
		'sheep',
		'fish',
		'deer',
		'series',
		'species',
		'money',
		'rice',
		'information',
		'equipment'
	];

	if (in_array(strtolower($word), $uncountable)) {
		return $word;
	}

	foreach ($irregular as $pattern => $result) {
		$pattern = '/' . $pattern . '$/i';

		if (preg_match($pattern, $word)) {
			return preg_replace($pattern, $result, $word);
		}
	}

	foreach ($plural as $pattern => $result) {
		if (preg_match($pattern, $word)) {
			return preg_replace($pattern, $result, $word);
		}
	}

	return $word;
}
