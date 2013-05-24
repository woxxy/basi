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

	public function menu_del_giorno()
	{
		$this->data['method_title'] = 'Menu del giorno';
		$this->data['current'] = 'menu_del_giorno';

		if ($get = $this->input->get())
		{
			if (isset($get['nome_scuola']) && isset($get['circoscrizione_scuola']))
			{
				$sth = $this->_getConn()->prepare("
					SELECT p.nome, p.tipo
					FROM menu AS m
					JOIN piatto AS p ON m.piatto_id = p.id
					JOIN scuola AS s ON p.fornitore_partita_iva = s.fornitore_partita_iva
					WHERE m.data = '2013-04-02'
						AND s.nome = ?
						AND s.circoscrizione = ?
				");

				$sth->execute(array($get['nome_scuola'], $get['circoscrizione_scuola']));
				$data['piatti'] = $sth->fetchAll();

				if ($data['piatti'])
				{
					$data['scuola'] = $get['nome_scuola'];
					$data['giorno'] = '2013-04-02';
					$data['circoscrizione'] = $get['circoscrizione_scuola'];
					$this->data['body'] = $this->load->view('menu_del_giorno', $data, true);
					$this->load->view('default', $this->data);
					return;
				}

				$this->data['alert'] = 'La scuola richiesta non esiste.';
			}

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
					WHERE m.data = '2013-04-02'
						AND s.nome = ?
						AND s.circoscrizione = ?
				");

				$sth->execute(array($get['nome_scuola'], $get['circoscrizione_scuola']));
				$data['piatti'] = $sth->fetchAll();

				if ($data['piatti'])
				{
					$data['scuola'] = $get['nome_scuola'];
					$data['giorno'] = '2013-04-02';
					$data['circoscrizione'] = $get['circoscrizione_scuola'];
					$this->data['body'] = $this->load->view('menu_del_giorno', $data, true);
					$this->load->view('default', $this->data);
					return;
				}

				$this->data['alert'] = 'La scuola richiesta non esiste.';
			}

		}

		$data['fornitori'] = $this->_getConn()->query('SELECT * FROM scuola')->fetchAll();

		$this->data['body'] = $this->load->view('piatti_meno_offerti_query', $data, true);
		$this->load->view('default', $this->data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */