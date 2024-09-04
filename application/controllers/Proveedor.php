<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Proveedor extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->model('proveedor_model', 'proveedor');
	}

	/**
	 * Funcion que retorna un arreglo de recursos del modelo Proveedor
	 * @return Array
	 **/
	public function index() {
		$proveedores = $this->proveedor->all();
		$this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($proveedores));
	}

	/**
	 * Funcion que retorna las propiedades de un recurso del modelo Proveedor
	 * @return Array
	 **/
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

	/**
	 * Funcion que retorna un arreglo de objetos con un formato particular para uso del Front End
	 * @return Array
	 **/
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