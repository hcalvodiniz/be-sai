<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Categoria extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('categoria_model','categoria');
	}

	public function index() {
		$data = $this->categoria->all();
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($data));
	}

	public function store() {
		$this->load->library('form_validation');
		$_POST = json_decode(file_get_contents("php://input"), TRUE);

		$this->form_validation->set_rules('nombre', 'Nombre', 'required|is_unique[categorias.nombre]');
		$this->form_validation->set_message('required', 'El Campo {field} es requerido');
		$this->form_validation->set_message('is_unique', 'El Campo {field} debe contener un valor unico!');

		if ($this->form_validation->run() === FALSE) {
			return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => FALSE,
					'msg' => validation_errors(),
					'data' => null
				]));
		}
		$data = $this->input->post();
		$model = $this->categoria->insert($data);
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => TRUE,
					'msg' => 'Se ha registrado la categoria correctamente!',
					'data' => $model
				]));
	}

	public function update() {
		$this->load->library('form_validation');
		$_POST = json_decode(file_get_contents("php://input"), TRUE);

		$this->form_validation->set_rules('nombre', 'Nombre', 'required');
		$this->form_validation->set_message('required', 'El Campo {field} es requerido');

		if ($this->form_validation->run() === FALSE) {
			return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => FALSE,
					'msg' => validation_errors(),
					'data' => null
				]));
		}

		$data = $this->input->post();
		$id = $data['id'];
		unset($data['id']);
		$model = $this->categoria->update($data, $id);
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => TRUE,
					'msg' => 'Se ha actualizado correctamente la categoria',
					'data' => $model
				]));

	}

	public function subcategoria() {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		$models = $this->categoria->where(['padre_id' => $data['categoria']]);
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode($models));
	}

	public function store_subcategoria() {
		$this->load->library("form_validation");
		$_POST = json_decode(file_get_contents("php://input"), TRUE);

		$this->form_validation->set_rules('nombre', 'Nombre', 'required|is_unique[categorias.nombre]');
		$this->form_validation->set_rules('padre_id', 'Categoria Padre', 'required|numeric');
		$this->form_validation->set_message('required', 'El Campo {field} es requerido!');
		$this->form_validation->set_message('is_unique', 'El valor del campo {field} debe ser único');
		$this->form_validation->set_message('numeric', 'El valor del campo {field} debe ser numerico!');

		if ($this->form_validation->run() == FALSE) {
			return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => FALSE,
					'msg' => validation_errors(),
					'data' => null
				]));
		}
		$data = $this->input->post();
		$model = $this->categoria->insert($data);
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => TRUE,
					'msg' => 'Se ha registrado correctamente la subcategoria',
					'data' => $model
				]));
	}

	public function edit_subcategoria() {
		$this->load->library('form_validation');
		$_POST = json_decode(file_get_contents("php://input"), TRUE);

		$this->form_validation->set_rules('nombre', 'Nombre', 'required');
		$this->form_validation->set_rules('padre_id', 'Categoria Padre', 'required|numeric');
		$this->form_validation->set_message('required', 'El Campo {field} es requerido!');
		$this->form_validation->set_message('numeric', 'El valor del campo {field} debe ser numerico!');

		if ($this->form_validation->run() == FALSE) {
			return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => FALSE,
					'msg' => validation_errors(),
					'data' => null
				]));
		}
		$data = $this->input->post();
		$id = $data['id'];
		unset($data['id']);
		$model = $this->categoria->update($data, $id);
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => TRUE,
					'msg' => 'Se ha actualizado correctamente la subcategoria',
					'data' => $model
				]));
	}

	public function get_categorias() {
		$data = $this->categoria->all();
		$data = $this->pluck($data, 'nombre', 'id');
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode($data));
	}

	public function get_subcategorias($id) {
		$data = $this->categoria->where(['padre_id' => $id]);
		$data = $this->pluck($data, 'nombre','id');
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode($data));
	}
}