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
		3 => [15 => 35, 30 => 65],
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
				'clienti',
				null, // 20000
				InputOption::VALUE_OPTIONAL,
				'Riempie la tabella database delle clienti'
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

		if($input->getOption('clienti') !== null)
		{
			$this->fakeclienti($input->getOption('clienti'));
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
			'versamento',
			'presenza',
			'cliente_ingrediente',
			'cliente',
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

		$conn->query('DROP DOMAIN IF EXISTS partita_iva');
		$conn->query('DROP DOMAIN IF EXISTS codice_fiscale');
		$conn->query('DROP TYPE IF EXISTS portata');
	}

	/**
	 * Creates all the tables and domains for the schema
	 */
	protected function createSchema()
	{
		$conn = $this->getConnection();

		$conn->query('
			CREATE DOMAIN partita_iva AS numeric(15,0)
		');
		$conn->query('
			CREATE DOMAIN codice_fiscale AS varchar(16)
		');
		$conn->query("
			CREATE TYPE portata AS enum ('primo', 'secondo', 'contorno')
		");

		$conn->query('
			CREATE TABLE fornitore (
				partita_iva partita_iva NOT NULL,
				nome varchar(256) NOT NULL,
				indirizzo varchar(128) NOT NULL,
				citta varchar(64) NOT NULL,
				telefono varchar(32) NOT NULL,
				email varchar(64) NOT NULL,

				PRIMARY KEY (partita_iva)
			);
		');

		$conn->query('
			CREATE TABLE piatto (
				id serial,
				nome varchar(128),
				portata portata NOT NULL,
				fornitore_partita_iva partita_iva NOT NULL,

				PRIMARY KEY (id),

				FOREIGN KEY (fornitore_partita_iva) REFERENCES fornitore(partita_iva)
					ON DELETE CASCADE
			)
		');

		$conn->query('
			CREATE TABLE ingrediente (
				id serial,
				nome varchar(128),

				PRIMARY KEY (id)
			)
		');

		$conn->query('
			CREATE TABLE piatto_ingrediente (
				piatto_id integer NOT NULL,
				ingrediente_id integer NOT NULL,
				quantita integer NOT NULL,


				PRIMARY KEY (piatto_id, ingrediente_id),

				FOREIGN KEY (piatto_id) REFERENCES piatto(id)
					ON DELETE CASCADE,
				FOREIGN KEY (ingrediente_id) REFERENCES ingrediente(id)
					ON DELETE CASCADE
			)
		');

		$conn->query('
			CREATE TABLE menu (
				piatto_id integer NOT NULL,
				fornitore_partita_iva partita_iva NOT NULL,
				data date NOT NULL,

				PRIMARY KEY (piatto_id, fornitore_partita_iva, data),

				FOREIGN KEY (piatto_id) REFERENCES piatto(id)
					ON DELETE CASCADE,
				FOREIGN KEY (fornitore_partita_iva) REFERENCES fornitore(partita_iva)
					ON DELETE CASCADE
			)
		');

		$conn->query('
			CREATE TABLE scuola (
				id serial,
				circoscrizione integer NOT NULL,
				nome varchar(128) NOT NULL,
				indirizzo varchar(128) NOT NULL,
				telefono varchar(32) NOT NULL,
				fornitore_partita_iva partita_iva NOT NULL,

				PRIMARY KEY (circoscrizione, nome),
				UNIQUE (id),

				FOREIGN KEY (fornitore_partita_iva) REFERENCES fornitore(partita_iva)
					ON DELETE RESTRICT
			)
		');

		$conn->query('
			CREATE TABLE cliente (
				cf codice_fiscale NOT NULL,
				nome varchar(32) NOT NULL,
				cognome varchar(32) NOT NULL,
				indirizzo varchar(128) NOT NULL,
				citta varchar(64) NOT NULL,
				telefono varchar(32) NOT NULL,
				scuola_id integer NOT NULL,
				classe_reddito integer,
				presenze integer NOT NULL,
				pasti integer NOT NULL,

				PRIMARY KEY (cf),

				FOREIGN KEY (scuola_id) REFERENCES scuola(id)
					ON DELETE RESTRICT
			)
		');

		$conn->query('
			CREATE TABLE cliente_ingrediente (
				cf codice_fiscale NOT NULL,
				ingrediente_id integer NOT NULL,

				PRIMARY KEY (cf, ingrediente_id),

				FOREIGN KEY (cf) REFERENCES cliente(cf)
					ON DELETE CASCADE,
				FOREIGN KEY (ingrediente_id) REFERENCES ingrediente(id)
					ON DELETE CASCADE
			)
		');

		$conn->query('
			CREATE TABLE presenza (
				cf codice_fiscale NOT NULL,
				data date NOT NULL,

				PRIMARY KEY (cf, data),

				FOREIGN KEY (cf) REFERENCES cliente(cf)
					ON DELETE CASCADE
			)
		');

		$conn->query('
			CREATE TABLE versamento (
				id serial,
				cf codice_fiscale NOT NULL,
				importo float NOT NULL,
				pasti integer NOT NULL,
				data date NOT NULL,

				PRIMARY KEY (id),

				FOREIGN KEY (cf) REFERENCES cliente(cf)
					ON DELETE CASCADE
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
			(nome, portata, fornitore_partita_iva)
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

		$conn->beginTransaction();

		$partita_iva_arr = $conn->query('
			SELECT partita_iva
			FROM fornitore
		')->fetchAll(\PDO::FETCH_COLUMN, 0);

		$sth =$this->getConnection()->prepare('
			INSERT INTO menu
			(piatto_id, fornitore_partita_iva, data)
			VALUES (?, ?, ?)
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
						WHERE portata = '".$tipo."' AND fornitore_partita_iva = ".$partita_iva."
					")->fetchAll(\PDO::FETCH_COLUMN, 0);

					$key = array_rand($piatto_id_arr);
					$piatto = $piatto_id_arr[$key];
					unset($piatto_id_arr[$key]);
					$alternativa = $piatto_id_arr[array_rand($piatto_id_arr)];

					foreach ([$piatto, $alternativa] as $piatto_id)
					{
						$sth->execute([
							$piatto_id,
							$partita_iva,
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

	protected function fakeclienti($cycles = 2000)
	{
		$conn = $this->getConnection();

		$faker = \Faker\Factory::create('it_IT');

		$conn->beginTransaction();

		$scuola_id_arr = $conn->query('
			SELECT id
			FROM scuola
		')->fetchAll(\PDO::FETCH_COLUMN, 0);

		$sth = $conn->prepare('
			INSERT INTO cliente
			(cf, nome, cognome, indirizzo, citta, telefono, scuola_id, classe_reddito, presenze, pasti)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
		');

		$sth_presenza = $conn->prepare('
			INSERT INTO presenza
			(cf, data)
			VALUES (?, ?)
		');

		$sth_versamento = $conn->prepare('
			INSERT INTO versamento
			(cf, importo, pasti, data)
			VALUES (?, ?, ?, ?)
		');

		$sth_update_presenza = $conn->prepare('
			UPDATE cliente
			SET presenze = presenze + 1
			WHERE cf = ?
		');

		$sth_update_pasto = $conn->prepare('
			UPDATE cliente
			SET pasti = pasti + ?
			WHERE cf = ?
		');

		for ($i = 0; $i < $cycles; $i++)
		{
			$cf = $faker->bothify('??????##?##?###?');
			$classe_reddito = rand(0,3);

			// make some teachers
			if (rand(0,20) === 0)
			{
				$classe_reddito = null;
			}

			$sth->execute([
				$cf,
				$faker->firstName,
				$faker->lastName,
				$faker->streetAddress,
				rand(0, 10) === 0 ? $faker->city : 'Padova',
				$faker->phoneNumber,
				$scuola_id_arr[array_rand($scuola_id_arr)],
				$classe_reddito,
				0,
				0
			]);

			// simulate a month
			for ($j = 1; $j < 30; $j++)
			{
				// one times on ten isn't present
				if (rand(0, 10) !== 0)
				{
					$sth_presenza->execute([$cf, '2013-04-'.$j]);
					$sth_update_presenza->execute([$cf]);
				}

				if ($classe_reddito !== 0 && rand(0, 10) === 0)
				{
					$pasti = rand(0,1) !== 0 ? 30 : 15;
					$sth_versamento->execute([
						$cf,
						$this->reddito[$classe_reddito][$pasti],
						$pasti,
						'2013-04-'.$j
					]);

					$sth_update_pasto->execute([$pasti, $cf]);
				}
			}
		}

		$conn->commit();
	}
}