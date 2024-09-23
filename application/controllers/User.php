<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->diniz = $this->load->database('diniz', TRUE);
		$this->op = $this->load->database('operaciones', TRUE);
		$this->infra = $this->load->database('infra', TRUE);
		$this->load->database('default');
	}

	/**
	 * Funcion para validar sesion, se hace llamado a pn_sesion_con2.php. Que nos trae la informacion del usuario logeado, y hacemos validaciones de tipo de usuario y perfiles
	 * @return Array
	 **/
	public function index() {
		$data = json_decode(file_get_contents("php://input"), TRUE);

		// Ya que la peticion se va a realizar a un dominio seguro, se generan los parametros para crear el contexto de la peticion.
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

		// Creamos el contexto y hacemos la peticion.
		$context = stream_context_create($options);
		$response = file_get_contents('https://diniz.com.mx/diniz/servicios/services/pn_sesion_con2.php', false, $context);
		// Obtenemos la respuesta de la peticion.
		$response = json_decode($response, TRUE);
		$data = $response[0]['usuarios'];
		$empl = $data['noempl'];
		if($empl == 'x'){
			return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => FALSE,
				'data' => null,
				'response' => $response
			]));
		}
		// Comenzamos validaciones.... Validamos si el usuario pertenece al grupo de Administradores, Distritales, Gerentes o Técnicos
		$result = $this->db->query("SELECT * FROM usuario_perfil WHERE noempl = '$empl'");
		$data['isAdmin'] = ($result->num_rows() > 0) ? ($result->result_array()[0]['perfil_id'] == 1) : FALSE;
		$data['isDistrital'] = ($result->num_rows() > 0) ? ($result->result_array()[0]['perfil_id'] == 2) : FALSE;
		$data['isGerente'] = ($result->num_rows() > 0) ? ($result->result_array()[0]['perfil_id'] == 3) : FALSE;
		$data['isTecnico'] = ($result->num_rows() > 0) ? ($result->result_array()[0]['perfil_id'] == 4) : FALSE;

		$cef = [];
		// Obtenemos la lista de CEFs que tiene asignados al usuario.
		$result = $this->diniz->query("SELECT ul.no_empl, l.local as cef FROM diniz.usuario_locales ul LEFT JOIN diniz.locales l ON ul.localid = l.id WHERE no_empl = '$empl'");
		foreach ($result->result_array() as $v) {
			$cef[] = "'".$v['cef']."'";
		}
		$data['cefs'] = implode(',', $cef);

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