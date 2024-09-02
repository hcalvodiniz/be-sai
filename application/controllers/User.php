<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->diniz = $this->load->database('diniz', TRUE);
		$this->op = $this->load->database('operaciones', TRUE);
		$this->infra = $this->load->database('infra', TRUE);
	}

	public function index() {
		$data = json_decode(file_get_contents("php://input"), TRUE);

		$options = [
			'http' => [
				'method' => 'POST',
				'header' => 'Content-Type: application/json',
				'content' => json_encode($data)
			],
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false
			]
		];

		$context = stream_context_create($options);
		$response = file_get_contents('https://diniz.com.mx/diniz/servicios/services/pn_sesion_con2.php', false, $context);
		$response = json_decode($response, TRUE);
		$data = $response[0]['usuarios'];
		$empl = $data['noempl'];
		$result = $this->op->query("SELECT * FROM cef_distrital WHERE gerente = '$empl'");
		$data['isDistrital'] = ($result->num_rows() > 0);
		$cef = [];
		if($empl == 'x'){
			return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => FALSE,
				'data' => null
			]));
		}
		if (!$data['isDistrital']) {
			$result = $this->diniz->query("SELECT ul.no_empl, l.local as cef FROM diniz.usuario_locales ul LEFT JOIN diniz.locales l ON ul.localid = l.id WHERE no_empl = '$empl'");
		}
		foreach ($result->result_array() as $v) {
			$cef[] = $v['cef'];
		}
		$data['cefs'] = implode(',', $cef);
		$result = $this->infra->query("SELECT * FROM usuario_perfil WHERE noempl = '$empl'");
		$data['isAdmin'] = ($result->num_rows() > 0) ? ($result->result_array()[0]['perfilid'] == 1) : FALSE;

		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => TRUE,
				'data' => $data
			]));
	}
}