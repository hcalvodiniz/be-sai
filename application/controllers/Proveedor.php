<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Proveedor extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->model('proveedor_model', 'proveedor');
	}

	public function index() {
		$proveedores = $this->proveedor->all();
		$this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($proveedores));
	}

	public function show($id) {
		$proveedor = $this->proveedor->find($id);
		$this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => TRUE,
				'msg' => '',
				'data' => $proveedor
			]));
	}

	public function get_list() {
		$proveedores = $this->proveedor->all();
		$proveedores = $this->pluck($proveedores, 'razon_social', 'id');
		$this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => TRUE,
				'msg' => '',
				'data' => $proveedores
			]));
	}
}