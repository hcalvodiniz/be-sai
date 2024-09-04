<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Categoria extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('categoria_model','categoria');
	}

	/**
	 * Funcion que devuelve un arreglo de objetos que contiene el modelo Categoria
	 * Hace uso de funciones de salida para poder regresar la informacion en formato JSON
	 * @return Array of Objects
	**/
	public function index() {
		$data = $this->categoria->all();
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($data));
	}

	/**
	 * Funcion para validar y guardar el recurso que se va a salvar como parte del modelo de Categoria
	 * Hace uso de la libreria "Form Validation" para poder validar el contenido de cada campo.
	 * @return Arreglo donde se encuentra el estado de la operación, un mensaje que puede contener los errores de la validación en caso de error
	 * y el retorno de los datos guardados en el modelo.
	**/
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

	/**
	 * Funcion para actualizar el recurso del modelo, con validaciones de la libreria "Form Validation"
	 * @return Arreglo donde se encuentra el estado de la operación, un mensaje que puede contener los errores de la validación en caso de error
	 * y el retorno de los datos guardados en el modelo.
	 **/
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

	/**
	 * Funcion que retorna un arreglo de objetos, del recurso del modelo Subcategorias
	 * @return Arreglo de objetos
	**/
	public function subcategoria() {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		$models = $this->categoria->where(['padre_id' => $data['categoria']]);
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode($models));
	}

	/**
	 * Funcion que guarda recursos del modelo Subcategoria, tambien se usan funciones de las librerias de "Form Validation"
	 * @return Arreglo donde se encuentra el estado de la operación, un mensaje que puede contener los errores de la validación en caso de error
	 * y el retorno de los datos guardados en el modelo.
	 */
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

	/**
	 * Funcion que edita el recurso del modelo de subcategoria, tambien usa funciones de la libreria de "Form Validation"
	 * @return Arreglo donde se encuentra el estado de la operación, un mensaje que puede contener los errores de la validación en caso de error
	 * y el retorno de los datos guardados en el modelo.
	 */
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

	/**
	 * Funcion que retorna un arreglo de los recursos del modelo de Categorias con un formato en particular
	 * @return Arreglo de objetos que vienen en un formato particular para el uso en Front End 
	 **/
	public function get_categorias() {
		$data = $this->categoria->all();
		$data = $this->pluck($data, 'nombre', 'id');
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode($data));
	}

	/**
	 * Funcion que retorna un arreglo de los recursos del modelo de Subcategorias con un formato en particular
	 * @return Arreglo de objetos que vienen en un formato particular para el uso en Front End 
	 **/
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