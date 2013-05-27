<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Basi extends CI_Controller {

	/**
	 * View data
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * Connection to database
	 *
	 * @var \PDO
	 */
	protected $conn = null;

	public function __construct()
	{
		parent::__construct();
		$this->data['controller_title'] = 'Mensa Scolastica';
	}

	/**
	 * Returns a PDO connection and creates it in case it's not already generated
	 * Uses the connection data found in config.php to connect
	 *
	 * @return \PDO
	 * @throws \Exception|\PDOException
	 */
	protected function _getConn()
	{
		if ($this->conn == null)
		{
			$conn_data = include FCPATH.'/config.php';

			try
			{
				$this->conn = new PDO(
					'pgsql:dbname='.$conn_data['database'].';host='.$conn_data['hostname'].';port='.$conn_data['port'].';',
					$conn_data['username'],
					$conn_data['password']
				);

				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch (PDOException $e)
			{
				throw $e;
			}
		}

		return $this->conn;
	}

	public function index()
	{
		$this->data['method_title'] = 'Home';
		$this->data['current'] = 'home';

		$this->data['body'] = $this->load->view('home', null, true);
		$this->load->view('default', $this->data);
	}

	public function generale()
	{
		$this->data['method_title'] = 'Pannello riassuntivo';
		$this->data['current'] = 'generale';

		$tabella = array();
		$tabella['count_clienti'] = array('Totale clienti',
			$this->_getConn()->query('SELECT COUNT(*) FROM cliente')->fetchColumn(0));
		$tabella['count_insegnanti'] = array('Totale insegnanti',
			$this->_getConn()->query('SELECT COUNT(*) FROM cliente WHERE classe_reddito IS NULL')->fetchColumn(0));
		$tabella['count_studenti'] = array('Totale studenti',
			$this->_getConn()->query('SELECT COUNT(*) FROM cliente WHERE classe_reddito IS NOT NULL')->fetchColumn(0));
		$tabella['count_paganti'] = array('Studenti paganti',
			$this->_getConn()->query('SELECT COUNT(*) FROM cliente WHERE classe_reddito > 0')->fetchColumn(0));

		$tabella['count_studenti_presenti'] = array('Studenti presenti', $this->_getConn()->query('
			SELECT COUNT(*)
			FROM presenza AS p
			NATURAL JOIN cliente AS c
			WHERE classe_reddito IS NOT NULL
				AND p.data = \'2013-04-22\'
		')->fetchColumn(0));

		$tabella['count_insegnanti_presenti'] = array('Insegnanti presenti', $this->_getConn()->query('
			SELECT COUNT(*)
			FROM presenza AS p
			NATURAL JOIN cliente AS c
			WHERE classe_reddito IS NULL
				AND p.data = \'2013-04-22\'
		')->fetchColumn(0));

		$tabella['media_studenti_per_scuola'] = array('Media studenti per scuola', floor($this->_getConn()->query('
			SELECT AVG(count)
			FROM
			(
				SELECT COUNT(*) as count
				FROM cliente AS c
				JOIN scuola AS s ON s.id = c.scuola_id
				WHERE classe_reddito IS NOT NULL
				GROUP BY s.id
			) AS x
		')->fetchColumn(0)));

		$tabella['media_insegnanti_per_scuola'] = array('Media insegnanti per scuola', floor($this->_getConn()->query('
			SELECT AVG(count)
			FROM
			(
				SELECT COUNT(*) as count
				FROM cliente AS c
				JOIN scuola AS s ON s.id = c.scuola_id
				WHERE classe_reddito IS NULL
				GROUP BY s.id
			) AS x
		')->fetchColumn(0)));

		$tabella['count_fornitori'] = array('Totale fornitori',
			$this->_getConn()->query('SELECT COUNT(*) FROM fornitore')->fetchColumn(0));

		$data['tabella'] = $tabella;
		$this->data['body'] = '<h2>Tabella riassuntiva</h2><br>'.$this->load->view('doppia_colonna', $data, true);
		$this->load->view('default', $this->data);
	}

	public function menu_del_giorno()
	{
		$this->data['method_title'] = 'Menu del giorno';
		$this->data['current'] = 'menu_del_giorno';

		if ($get = $this->input->get())
		{
			if (isset($get['nome_scuola']) && isset($get['circoscrizione_scuola']) && ctype_digit($get['circoscrizione_scuola']))
			{
				$sth = $this->_getConn()->prepare("
					SELECT p.nome, p.portata
					FROM menu AS m
					JOIN piatto AS p ON m.piatto_id = p.id
					JOIN scuola AS s ON p.fornitore_partita_iva = s.fornitore_partita_iva
					WHERE m.data = '2013-04-22'
						AND s.nome = ?
						AND s.circoscrizione = ?
				");

				$sth->execute(array($get['nome_scuola'], $get['circoscrizione_scuola']));
				$data['piatti'] = $sth->fetchAll();

				if ($data['piatti'])
				{
					$data['scuola'] = $get['nome_scuola'];
					$data['giorno'] = '2013-04-22';
					$data['circoscrizione'] = $get['circoscrizione_scuola'];
					$this->data['body'] = $this->load->view('menu_del_giorno', $data, true);
					$this->load->view('default', $this->data);
					return;
				}
			}

			$this->data['alert'] = 'La scuola richiesta non esiste.';
		}

		$data['scuole'] = $this->_getConn()->query('SELECT * FROM scuola')->fetchAll();

		$this->data['body'] = $this->load->view('menu_del_giorno_query', $data, true);
		$this->load->view('default', $this->data);
	}

	public function piatti_meno_offerti()
	{
		$this->data['method_title'] = 'Piatti meno offerti';
		$this->data['current'] = 'piatti_meno_offerti';

		if ($get = $this->input->get())
		{
			if (isset($get['nome_scuola']) && isset($get['circoscrizione_scuola']))
			{
				$sth = $this->_getConn()->prepare("
					SELECT p.nome AS piatto_nome, i.nome AS ingrediente_nome
					FROM piatto AS m
					WHERE m.data = '2013-04-22'
						AND s.nome = ?
						AND s.circoscrizione = ?
				");

				$sth->execute(array($get['nome_scuola'], $get['circoscrizione_scuola']));
				$data['piatti'] = $sth->fetchAll();

				if ($data['piatti'])
				{
					$data['scuola'] = $get['nome_scuola'];
					$data['giorno'] = '2013-04-22';
					$data['circoscrizione'] = $get['circoscrizione_scuola'];
					$this->data['body'] = $this->load->view('menu_del_giorno', $data, true);
					$this->load->view('default', $this->data);
					return;
				}
			}

			$this->data['alert'] = 'La scuola richiesta non esiste.';
		}

		$data['fornitori'] = $this->_getConn()->query('SELECT * FROM scuola')->fetchAll();

		$this->data['body'] = $this->load->view('piatti_meno_offerti_query', $data, true);
		$this->load->view('default', $this->data);
	}

	public function clienti_in_negativo()
	{
		$this->data['method_title'] = 'Clienti in Negativo';
		$this->data['current'] = 'clienti_in_negativo';

		$data['result'] = $this->_getConn()->query('
			SELECT *
			FROM cliente
			WHERE classe_reddito > 0
				AND pasti < presenze
			ORDER BY (pasti - presenze)
			LIMIT 300
		')->fetchAll();

		$this->data['body'] = $this->load->view('clienti_in_negativo', $data, true);
		$this->load->view('default', $this->data);
	}

	public function clienti_per_fornitore()
	{
		$this->data['method_title'] = 'Clienti Presenti per Fornitore';
		$this->data['current'] = 'clienti_per_fornitore';

		if ($get = $this->input->get())
		{
			if (isset($get['partita_iva']) && ctype_digit($get['partita_iva']))
			{
				$sth_fornitore = $this->_getConn()->prepare('SELECT * FROM fornitore WHERE partita_iva = ?');
				$sth_fornitore->execute(array($get['partita_iva']));
				if ($sth_fornitore->rowCount() > 0)
				{
					$data['fornitore'] = $sth_fornitore->fetch();

					$sth = $this->_getConn()->prepare('
						SELECT COUNT(*)
						FROM fornitore AS f
						JOIN scuola AS s ON f.partita_iva = s.fornitore_partita_iva
						JOIN cliente AS c ON s.id = c.scuola_id
						NATURAL JOIN presenza AS pr
						WHERE f.partita_iva = ?
							AND pr.data = ?
					');

					$sth_allergici = $this->_getConn()->prepare('
						SELECT COUNT(DISTINCT(a.cf))
						FROM fornitore AS f
						JOIN scuola AS s ON f.partita_iva = s.fornitore_partita_iva
						JOIN cliente AS c ON s.id = c.scuola_id
						NATURAL JOIN presenza AS pr
						NATURAL JOIN allergico AS a
						WHERE f.partita_iva = ?
							AND pr.data = ?
					');

					$sth->execute(array($get['partita_iva'], '2013-04-22'));
					$sth_allergici->execute(array($get['partita_iva'], '2013-04-22'));
					$data['result'] = $sth->fetchAll();
					$data['result_allergici'] = $sth_allergici->fetchAll();

					$this->data['body'] = $this->load->view('clienti_per_fornitore', $data, true);
					$this->load->view('default', $this->data);
					return;
				}
			}

			$this->data['alert'] = 'Il fornitore specificato non esiste';
		}

		$data['fornitori'] = $this->_getConn()->query('SELECT * FROM fornitore')->fetchAll();

		$this->data['body'] = $this->load->view('fornitori', $data, true);
		$this->load->view('default', $this->data);
	}

	public function piatti_offerti_frequentemente()
	{
		$this->data['method_title'] = 'Piatti offerti frequentemente';
		$this->data['current'] = 'piatti_offerti_frequentemente';

		if ($get = $this->input->get())
		{
			if (isset($get['partita_iva']) && ctype_digit($get['partita_iva']))
			{
				$sth_fornitore = $this->_getConn()->prepare('SELECT * FROM fornitore WHERE partita_iva = ?');
				$sth_fornitore->execute(array($get['partita_iva']));
				if ($sth_fornitore->rowCount() > 0)
				{
					$data['fornitore'] = $sth_fornitore->fetch();

					$sth = $this->_getConn()->prepare('
						SELECT p.nome, COUNT(*)
						FROM menu AS m
						JOIN piatto AS p ON m.piatto_id = p.id
						WHERE m.fornitore_partita_iva = ?
						GROUP BY piatto_id, p.nome
						ORDER BY COUNT(*) DESC
					');

					$sth->execute(array($get['partita_iva']));
					$data['result'] = $sth->fetchAll();
					$this->data['body'] = $this->load->view('piatti_offerti_frequentemente', $data, true);
					$this->load->view('default', $this->data);
					return;
				}
			}

			$this->data['alert'] = 'Il fornitore specificato non esiste';
		}

		$data['fornitori'] = $this->_getConn()->query('SELECT * FROM fornitore')->fetchAll();

		$this->data['body'] = $this->load->view('fornitori_piatti', $data, true);
		$this->load->view('default', $this->data);
	}
}