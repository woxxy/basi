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
				'drop',
				null, // 7
				InputOption::VALUE_OPTIONAL,
				'Droppa lo schema'
			)
			->addOption(
				'create',
				null, // 7
				InputOption::VALUE_OPTIONAL,
				'Crea lo schema'
			)
			->addOption(
				'fornitori',
				null, // 7
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella dei fornitori'
			)
			->addOption(
				'ingredienti',
				null, // 7
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella degli ingredienti'
			)
			->addOption(
				'piatti',
				null, // 7
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella dei piatti'
			)
			->addOption(
				'scuole',
				null, // 50
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella database delle scuole'
			)
			->addOption(
				'persone',
				null, // 20000
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella database delle persone'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption('drop') !== null)
		{
			$this->dropSchema();
		}

		if ($input->getOption('create') !== null)
		{
			$this->createSchema();
		}

		if($input->getOption('fornitori') !== null)
		{
			$this->fakeFornitori($input->getOption('fornitori'));
		}

		if($input->getOption('ingredienti') !== null)
		{
			$this->fakeIngredienti($input->getOption('ingredienti'));
		}

		if($input->getOption('piatti') !== null)
		{
			$this->fakePiatti($input->getOption('piatti'));
		}

		if($input->getOption('scuole') !== null)
		{
			$this->fakeScuole($input->getOption('scuole'));
		}

		if($input->getOption('persone') !== null)
		{
			$this->fakePersone($input->getOption('persone'));
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

				$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			}
			catch (\PDOException $e)
			{
				throw $e;
			}
		}

		return $this->conn;
	}



	protected function dropSchema()
	{
		$conn = $this->getConnection();
		$tables = [
			'presenza',
			'persona',
			'scuola',
			'piatto_ingrediente',
			'piatto',
			'ingrediente',
			'fornitore',
			'menu',
		];

		foreach ($tables as $table)
		{
			$conn->query('DROP TABLE IF EXISTS '.$table);
		}

		$conn->query('DROP DOMAIN PARTITA_IVA');
		$conn->query('DROP DOMAIN CODICE_FISCALE');
	}

	protected function createSchema()
	{
		$conn = $this->getConnection();

		$conn->query('
			CREATE DOMAIN PARTITA_IVA AS NUMERIC(15,0)
		');
		$conn->query('
			CREATE DOMAIN CODICE_FISCALE AS VARCHAR(16)
		');

		$conn->query('
			CREATE TABLE fornitore (
				partita_iva PARTITA_IVA NOT NULL,
				nome VARCHAR(256) NOT NULL,
				indirizzo VARCHAR(128) NOT NULL,
				citta VARCHAR(64) NOT NULL,
				telefono VARCHAR(32) NOT NULL,
				email VARCHAR(64) NOT NULL,

				PRIMARY KEY (partita_iva)
			);
		');

		$conn->query('
			CREATE TABLE piatto (
				id SERIAL,
				nome VARCHAR(128),
				fornitore_partita_iva PARTITA_IVA NOT NULL,

				PRIMARY KEY (id),

				FOREIGN KEY (fornitore_partita_iva) REFERENCES fornitore(partita_iva)
			)
		');

		$conn->query('
			CREATE TABLE ingrediente (
				id SERIAL,
				nome VARCHAR(128),

				PRIMARY KEY (id)
			)
		');

		$conn->query('
			CREATE TABLE piatto_ingrediente (
				piatto_id INTEGER,
				ingrediente_id INTEGER,
				quantita INTEGER,


				PRIMARY KEY (piatto_id, ingrediente_id),

				FOREIGN KEY (piatto_id) REFERENCES piatto(id),
				FOREIGN KEY (ingrediente_id) REFERENCES ingrediente(id)
			)
		');

		$conn->query('
			CREATE TABLE scuola (
				id SERIAL,
				circoscrizione INTEGER NOT NULL,
				nome VARCHAR(128) NOT NULL,
				indirizzo VARCHAR(128) NOT NULL,
				telefono VARCHAR(32) NOT NULL,
				fornitore_partita_iva PARTITA_IVA NOT NULL,

				PRIMARY KEY (circoscrizione, nome),
				UNIQUE (id),

				FOREIGN KEY (fornitore_partita_iva) REFERENCES fornitore(partita_iva)
			)
		');

		$conn->query('
			CREATE TABLE persona (
				cf CODICE_FISCALE NOT NULL,
				nome VARCHAR(32) NOT NULL,
				cognome VARCHAR(32) NOT NULL,
				indirizzo VARCHAR(128) NOT NULL,
				citta VARCHAR(64) NOT NULL,
				telefono VARCHAR(32) NOT NULL,
				scuola_id INTEGER NOT NULL,

				PRIMARY KEY (cf),

				FOREIGN KEY (scuola_id) REFERENCES scuola(id)
			)
		');

		$conn->query('
			CREATE TABLE persona_ingrediente (
				cf CODICE_FISCALE NOT NULL,
				ingrediente_id INTEGER NOT NULL,

				PRIMARY KEY (cf, ingrediente_id),

				FOREIGN KEY (cf) REFERENCES persona(cf),
				FOREIGN KEY (ingrediente_id) REFERENCES ingrediente(id)
			)
		');

		$conn->query('
			CREATE TABLE presenza (
				cf CODICE_FISCALE NOT NULL,
				data DATE NOT NULL,

				PRIMARY KEY (cf, data),

				FOREIGN KEY (cf) REFERENCES persona(cf)
			)
		');
	}

	protected function fakeFornitori($cycles = 7)
	{
		$conn = $this->getConnection();

		$faker = \Faker\Factory::create('it_IT');

		$conn->beginTransaction();
		$sth = $conn->prepare('
			INSERT INTO fornitore
			(partita_iva, nome, indirizzo, citta, telefono, email)
			VALUES (?, ?, ?, ?, ?, ?)
		');

		for ($i = 0; $i < $cycles; $i++)
		{
			$sth->execute([
				$faker->randomNumber(11),
				$faker->company,
				$faker->streetAddress,
				$faker->city,
				$faker->phoneNumber,
				$faker->email
			]);
		}
		$conn->commit();
	}

	protected function fakeIngredienti($cycles = 1000)
	{
		$conn = $this->getConnection();

		$faker = \Faker\Factory::create('it_IT');

		$conn->beginTransaction();

		$sth = $conn->prepare('
			INSERT INTO ingrediente
			(nome)
			VALUES (?)
		');

		for ($i = 0; $i < $cycles; $i++)
		{
			$sth->execute([
				implode(' ', $faker->words(rand(1, 3))),
			]);
		}
		$conn->commit();
	}

	protected function fakePiatti($cycles = 200)
	{
		$conn = $this->getConnection();

		$faker = \Faker\Factory::create('it_IT');

		$conn->beginTransaction();

		$partita_iva_arr = $conn->query('
			SELECT partita_iva
			FROM fornitore
		')->fetchAll(\PDO::FETCH_COLUMN, 0);

		$ingrediente_arr = $conn->query('
			SELECT id
			FROM ingrediente
		')->fetchAll(\PDO::FETCH_COLUMN, 0);

		$sth = $conn->prepare('
			INSERT INTO piatto
			(nome, fornitore_partita_iva)
			VALUES (?, ?)
		');

		$sth_ingrediente = $conn->prepare('
			INSERT INTO piatto_ingrediente
			(piatto_id, ingrediente_id, quantita)
			VALUES (?, ?, ?)
		');

		for ($i = 0; $i < $cycles; $i++)
		{
			$sth->execute([
				implode(' ', $faker->words(rand(1, 3))),
				$partita_iva_arr[array_rand($partita_iva_arr)]
			]);

			$last_piatto_id = $conn->lastInsertId('piatto_id_seq');
			$used_arr = [];
			for ($j = 0; $j < rand(1, 5); $j++)
			{
				while (in_array($ingrediente_id = $ingrediente_arr[array_rand($ingrediente_arr)], $used_arr))
				{
					$used_arr[] = $ingrediente_id;
				}

				$sth_ingrediente->execute([
					$last_piatto_id,
					$ingrediente_id,
					rand(2, 1000)
				]);
			}
		}
		$conn->commit();
	}

	protected function fakeScuole($cycles = 10)
	{
		$conn = $this->getConnection();

		$faker = \Faker\Factory::create('it_IT');

		$conn->beginTransaction();

		$partita_iva_arr = $conn->query('
			SELECT partita_iva
			FROM fornitore
		')->fetchAll(\PDO::FETCH_COLUMN, 0);

		$sth = $conn->prepare('
			INSERT INTO scuola
			(circoscrizione, nome, indirizzo, telefono, fornitore_partita_iva)
			VALUES (?, ?, ?, ?, ?)
		');

		for ($i = 0; $i < $cycles; $i++)
		{
			$sth->execute([
				$faker->randomNumber(1, 10),
				$faker->firstName.' '.$faker->lastName,
				$faker->streetAddress,
				$faker->phoneNumber,
				$partita_iva_arr[array_rand($partita_iva_arr)]
			]);
		}

		$conn->commit();
	}

	protected function fakePersone($cycles = 2000)
	{
		$conn = $this->getConnection();

		$faker = \Faker\Factory::create('it_IT');

		$conn->beginTransaction();

		$scuola_id_arr = $conn->query('
			SELECT id
			FROM scuola
		')->fetchAll(\PDO::FETCH_COLUMN, 0);

		$sth = $conn->prepare('
			INSERT INTO persona
			(cf, nome, cognome, indirizzo, citta, telefono, scuola_id)
			VALUES (?, ?, ?, ?, ?, ?, ?)
		');

		$sth_presenza = $conn->prepare('
			INSERT INTO presenza
			(cf, data)
			VALUES (?, ?)
		');

		for ($i = 0; $i < $cycles; $i++)
		{
			$cf = $faker->bothify('??????##?##?###?');

			$sth->execute([
				$cf,
				$faker->firstName,
				$faker->lastName,
				$faker->streetAddress,
				rand(0, 10) === 0 ? $faker->city : 'Padova',
				$faker->phoneNumber,
				$scuola_id_arr[array_rand($scuola_id_arr)]
			]);

			for ($j = 1; $j < 30; $j++)
			{
				rand(0, 10) !== 0 ? $sth_presenza->execute([$cf, '2013-04-'.$j]) : false;
			}
		}

		$conn->commit();
	}
}