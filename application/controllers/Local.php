<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Local extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->model('local_model', 'local');
	}

	public function index() {
		$locales = $this->local->all();
		$this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($locales));
	}

	public function show($id) {
		$local = $this->local->find($id);
		$this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => TRUE,
				'msg' => '',
				'data' => $local
			]));
	}
	public function get_locales() {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		$locales = ($data['isAdmin']) ? $this->local->get("id, concat(codigo, ' - ', nombre) as nombre") : $this->local->get_locales($data['locales']);
		$locales = $this->pluck($locales, 'nombre', 'id');
		$this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => TRUE,
				'msg' => '',
				'data' => $locales
			]));
	}
}