<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Prioridad extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->model('prioridad_model', 'prioridad');
	}

	/**
	 * Funcion que retorna un arreglo de recursos del modelo Prioridad
	 * @return Array
	 **/
	public function index() {
		$data = $this->prioridad->all();
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode($data));
	}

	/**
	 * Funcion para guardar propiedades del modelo Prioridad, se hace uso de funciones de la libreria "Form Validation"
	 * @return Arreglo donde se encuentra el estado de la operación, un mensaje que puede contener los errores de la validación en caso de error
	 * y el retorno de las propiedades guardados de el modelo.
	 **/
	public function store() {
		$this->load->library('form_validation');

		$_POST = json_decode(file_get_contents("php://input"), TRUE);
		$_POST['color'] = str_replace("#", "", $_POST['color']);

		$this->form_validation->set_rules('nombre','Nombre', 'required');
		$this->form_validation->set_rules('color','Color','callback_color_check');
		$this->form_validation->set_message('required', 'El Campo {field} es requerido');

		if ($this->form_validation->run() == FALSE) {
			$response = [
				'success' => FALSE,
				'msg' => validation_errors(),
				'data' => null
			];

			return $this->output
						->set_header("Access-Control-Allow-Origin: *")
						->set_header("Access-Control-Allow-Headers: *")
						->set_content_type("application/json")
						->set_output(json_encode($response));
		}
		$data = $this->input->post();
		$model = $this->prioridad->insert($data);
		return $this->output
					->set_header("Access-Control-Allow-Origin: *")
					->set_header("Access-Control-Allow-Headers: *")
					->set_content_type("application/json")
					->set_output(json_encode([
						'success' => TRUE,
						'msg' => 'Se ha guardado exitosamente!',
						'data' => $model
					]));
	}

	/**
	 * Funcion que actualiza las propiedades del modelo Prioridad
	 * @return Arreglo donde se encuentra el estado de la operación, un mensaje que puede contener los errores de la validación en caso de error
	 * y el retorno de las propiedades guardados de el modelo.
	 **/
	public function update() {
		$this->load->library('form_validation');

		$_POST = json_decode(file_get_contents("php://input"), TRUE);
		$_POST['color'] = str_replace("#", "", $_POST['color']);

		$this->form_validation->set_rules('nombre','Nombre', 'required');
		$this->form_validation->set_rules('color','Color','callback_color_check');
		$this->form_validation->set_message('required', 'El Campo {field} es requerido');

		if ($this->form_validation->run() == FALSE) {
			$response = [
				'success' => FALSE,
				'msg' => validation_errors(),
				'data' => null
			];

			return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode($response));
		}
		$data = $this->input->post();
		$id = $data['id'];
		unset($data['id']);
		$model = $this->prioridad->update($data, $id);

		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => TRUE,
					'msg' => 'Se ha actualizado exitosamente!',
					'data' => $model
				]));
	}

	/**
	 * Funcion que elimina el recurso del modelo Prioridad | Hay una validacion para el metodo de solicitud, para saber que la manden con el metodo correcto
	 * @return Arreglo donde se encuentra el estado de la operación y un mensaje.
	 **/
	public function destroy($id) {
		if($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			$this->prioridad->delete($id);
			return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_header("Access-Control-Allow-Methods: DELETE")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => TRUE,
					'msg' => 'Se ha eliminado exitosamente!',
					'data' => null
				]));
		} else {
			show_error('Not allowed '.$_SERVER['REQUEST_METHOD'].' method', 400);
		}
	}

	/**
	 * Funcion que retorna un arreglo de los recursos del modelo Prioridad con un formato en particular para el front end
	 * @return Array
	 **/
	public function get_list() {
		$data = $this->prioridad->all();
		$data = $this->pluck($data, 'nombre', 'id', TRUE, 'color');
		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_header("Access-Control-Allow-Methods: DELETE")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => TRUE,
					'msg' => '',
					'data' => $data
				]));
	}

	/**
	 * Funcion para validar que el valor de la variable es un valor hexadecimal
	 * @return boolean FALSE | TRUE
	 **/
	public function color_check($value) {
		$this->load->library('form_validation');
		if(empty($value)) {
			$this->form_validation->set_message('color_check', 'El Campo {field} es requerido');
			return FALSE;
		}
		$this->form_validation->set_message('color_check', 'El Campo {field} no es valor válido!');
		return ctype_xdigit($value);
	}
}