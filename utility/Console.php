<?php

namespace Foolz\Basi;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends Command
{
	/**
	 * Connection to database
	 *
	 * @var \PDO
	 */
	protected $conn = null;

	protected function configure()
	{
		$this
			->setName('basi:fill')
			->setDescription('Riempie il database di basi di dati con contenuto di test generato casualmente')
			->addOption(
				'fornitori',
				null, // 7
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella dei fornitori'
			)
			->addOption(
				'scuole',
				null, // 50
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella database delle scuole'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if($input->getOption('fornitori') !== null)
		{
			$this->fakeFornitori($input->getOption('fornitori'));
		}
	}

	/**
	 *
	 * @return \PDO
	 * @throws \Exception|\PDOException
	 */
	protected function getConnection()
	{
		$conn_data = include __DIR__.'/../config.php';

		if ($this->conn == null)
		{
			try
			{
				$this->conn = new \PDO(
					'pgsql:dbname='.$conn_data['database'].';host='.$conn_data['hostname'].';port='.$conn_data['port'].';',
					$conn_data['username'],
					$conn_data['password']
				);
			}
			catch (\PDOException $e)
			{
				throw $e;
			}
		}

		return $this->conn;
	}

	protected function fakeFornitori($cycles = 7)
	{
		$conn = $this->getConnection();

		$faker = \Faker\Factory::create('it_IT');

		$conn->beginTransaction();
		$sth = $conn->prepare('
			INSERT INTO fornitore
			(partita_iva, nome, indirizzo)
			VALUES (?, ?, ?)
		');

		for ($i = 0; $i < $cycles; $i++)
		{
			$sth->execute([$faker->randomNumber(11), $faker->company, $faker->address]);
			var_dump($this->conn->errorInfo());
		}
		$conn->commit();
	}
}