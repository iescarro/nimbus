<?php

namespace CodeIgniter\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{
	protected static $defaultName = 'serve';

	protected function configure()
	{
		$this
			->setName('serve')
			->setDescription('');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Starting PHP development server on localhost:9000...');
		exec('php -S localhost:9000 -t .', $outputLines, $returnCode);

		if ($returnCode === 0) {
			$output->writeln('<info>Server started successfully!</info>');
			return Command::SUCCESS;
		} else {
			$output->writeln('<error>Failed to start the server.</error>');
			return Command::FAILURE;
		}
	}
}
