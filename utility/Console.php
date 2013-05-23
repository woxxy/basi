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

	protected $reddito = [
		0 => [15 => 0, 30 => 0],
		1 => [15 => 15, 30 => 25],
		2 => [15 => 25, 30 => 45],
		3 => [15 => 30, 30 => 55],
	];

	/**
	 * Configure the command line interface arguments
	 */
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
				'menu',
				null, // 30
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella dei menu'
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

	/**
	 * Manage the command line request
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
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

		if($input->getOption('menu') !== null)
		{
			$this->fakeMenu($input->getOption('menu'));
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
	 * Returns a PDO connection and creates it in case it's not already generated
	 * Uses the connection data found in config.php to connect
	 *
	 * @return \PDO
	 * @throws \Exception|\PDOException
	 */
	protected function getConnection()
	{
		if ($this->conn == null)
		{
			$conn_data = include __DIR__.'/../config.php';

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

	/**
	 * Drops all the tables and domains from the schema
	 */
	protected function dropSchema()
	{
		$conn = $this->getConnection();
		$tables = [
			'ricarica',
			'presenza',
			'persona_ingrediente',
			'persona',
			'scuola',
			'piatto_ingrediente',
			'menu',
			'piatto',
			'ingrediente',
			'fornitore',
		];

		foreach ($tables as $table)
		{
			$conn->query('DROP TABLE IF EXISTS '.$table);
		}

		$conn->query('DROP DOMAIN IF EXISTS PARTITA_IVA');
		$conn->query('DROP DOMAIN IF EXISTS CODICE_FISCALE');
		$conn->query('DROP TYPE IF EXISTS tipo_piatto');
	}

	/**
	 * Creates all the tables and domains for the schema
	 */
	protected function createSchema()
	{
		$conn = $this->getConnection();

		$conn->query('
			CREATE DOMAIN PARTITA_IVA AS NUMERIC(15,0)
		');
		$conn->query('
			CREATE DOMAIN CODICE_FISCALE AS VARCHAR(16)
		');
		$conn->query("
			CREATE TYPE tipo_piatto AS ENUM ('primo', 'secondo', 'contorno')
		");

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
				tipo tipo_piatto NOT NULL,
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
			CREATE TABLE menu (
				piatto_id INTEGER NOT NULL,
				data DATE NOT NULL,

				PRIMARY KEY (piatto_id, data),

				FOREIGN KEY (piatto_id) REFERENCES piatto(id)
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
				classe_reddito INTEGER,

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

		$conn->query('
			CREATE TABLE ricarica (
				id SERIAL,
				cf CODICE_FISCALE NOT NULL,
				importo FLOAT NOT NULL,
				pasti INTEGER NOT NULL,
				data DATE NOT NULL,

				PRIMARY KEY (id),

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
			(nome, tipo, fornitore_partita_iva)
			VALUES (?, ?, ?)
		');

		$sth_ingrediente = $conn->prepare('
			INSERT INTO piatto_ingrediente
			(piatto_id, ingrediente_id, quantita)
			VALUES (?, ?, ?)
		');

		$tipi_piatto = ['primo', 'secondo', 'contorno'];

		for ($i = 0; $i < $cycles; $i++)
		{
			$sth->execute([
				implode(' ', $faker->words(rand(1, 3))),
				$tipi_piatto[array_rand($tipi_piatto)],
				$partita_iva_arr[array_rand($partita_iva_arr)]
			]);

			$last_piatto_id = $conn->lastInsertId('piatto_id_seq');
			$used_arr = [];
			for ($j = 0; $j < rand(1, 5); $j++)
			{
				$ingrediente_id = $ingrediente_arr[array_rand($ingrediente_arr)];
				while (in_array($ingrediente_id, $used_arr))
				{
					$ingrediente_id = $ingrediente_arr[array_rand($ingrediente_arr)];
				}

				$sth_ingrediente->execute([
					$last_piatto_id,
					$ingrediente_id,
					rand(2, 1000) // quantità
				]);

				$used_arr[] = $ingrediente_id;
			}
		}
		$conn->commit();
	}

	protected function fakeMenu($days = 30)
	{
		$conn = $this->getConnection();

		$faker = \Faker\Factory::create('it_IT');

		$conn->beginTransaction();

		$partita_iva_arr = $conn->query('
			SELECT partita_iva
			FROM fornitore
		')->fetchAll(\PDO::FETCH_COLUMN, 0);

		$sth =$this->getConnection()->prepare('
			INSERT INTO menu
			(piatto_id, data)
			VALUES (?, ?)
		');

		foreach ($partita_iva_arr as $partita_iva)
		{
			for ($j = 0; $j < 30; $j++)
			{
				// se è weekend
				if (date('N', strtotime('2013-04-'.$j)) >= 6)
				{
					continue;
				}

				foreach (['primo', 'secondo', 'contorno'] as $tipo)
				{
					$piatto_id_arr = $conn->query("
						SELECT id
						FROM piatto
						WHERE tipo = '".$tipo."' AND fornitore_partita_iva = ".$partita_iva."
					")->fetchAll(\PDO::FETCH_COLUMN, 0);

					$key = array_rand($piatto_id_arr);
					$piatto = $piatto_id_arr[$key];
					unset($piatto_id_arr[$key]);
					$alternativa = $piatto_id_arr[array_rand($piatto_id_arr)];

					foreach ([$piatto, $alternativa] as $piatto_id)
					{
						$sth->execute([
							$piatto_id,
							'2013-04-'.$j
						]);
					}
				}
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
			(cf, nome, cognome, indirizzo, citta, telefono, scuola_id, classe_reddito)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)
		');

		$sth_presenza = $conn->prepare('
			INSERT INTO presenza
			(cf, data)
			VALUES (?, ?)
		');

		$sth_ricarica = $conn->prepare('
			INSERT INTO ricarica
			(cf, importo, pasti, data)
			VALUES (?, ?, ?, ?)
		');

		for ($i = 0; $i < $cycles; $i++)
		{
			$cf = $faker->bothify('??????##?##?###?');
			$classe_reddito = rand(0,3);

			$sth->execute([
				$cf,
				$faker->firstName,
				$faker->lastName,
				$faker->streetAddress,
				rand(0, 10) === 0 ? $faker->city : 'Padova',
				$faker->phoneNumber,
				$scuola_id_arr[array_rand($scuola_id_arr)],
				$classe_reddito,
			]);

			// simulate a month
			for ($j = 1; $j < 30; $j++)
			{
				rand(0, 10) !== 0 ? $sth_presenza->execute([$cf, '2013-04-'.$j]) : false;

				if ($classe_reddito !== 0 && rand(0, 15) === 0)
				{
					$pasti = rand(0,1) !== 0 ? 30 : 15;
					$sth_ricarica->execute([
						$cf,
						$this->reddito[$classe_reddito][$pasti],
						$pasti,
						'2013-04-'.$j
					]);
				}
			}
		}

		$conn->commit();
	}
}