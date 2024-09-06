<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->model('ticket_model', 'ticket');
	}

	/**
	 * Funcion que retorna un arreglo de los recursos del modelo ticket, el numero de recursos cambia dependiendo del tipo de usuario
	 * @return Array
	 **/
	public function index() {
		$post = json_decode(file_get_contents("php://input"), TRUE);
		$data = ($post['isAdmin']) ? $this->ticket->all() : $this->ticket->get_tickets($post['locales']);
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($data));
	}

	/**
	 * Funcion que retorna un objeto donde se encuentra todos los datos y propiedades del ticket
	 * @return Object
	 **/
	public function show($id) {
		$this->load->helper('url');
		$data = $this->ticket->find($id);
		$photos = $this->ticket->get_photos($id);
		foreach ($photos as $k => $v) {
			if($v->cerrado == 0) {
				$data->evidencia = base_url($v->ruta.$v->nombre);
			} else {
				$data->evidencia_cerrado = base_url($v->ruta.$v->nombre);
			}
		}
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($data));
	}

	/**
	 * Funcion que guarda las propiedades y recursos del modelo Ticket, hace uso de funciones de la libreria "Form Validation"
	 * @return Array
	 **/
	public function store() {
		$this->load->library('form_validation');

		$data = $this->input->post();
		unset($data['categoria_parent']);
		$data['autorizado'] = ($data['autorizado'] == 'true') ? TRUE : FALSE;

		$this->form_validation->set_rules('local_id', 'Local','required');
		$this->form_validation->set_rules('prioridad_id','Prioridad','required');
		$this->form_validation->set_rules('comentario', 'Comentario','required');
		$this->form_validation->set_rules('proveedor_id', 'Proveedor', 'required');
		if(empty($_FILES['files']['name'])){
			$this->form_validation->set_rules('files', 'Evidencia','required');
		}

		$this->form_validation->set_message('required','El Campo {field} es requerido');
		

		if ($this->form_validation->run() === FALSE) {
			return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					"success" => FALSE,
					"msg" => validation_errors(),
					"data" => $_FILES
				]));
		}
		$data['estatus_id'] = TRUE;
		$data['fecha_hora'] = date("Y-m-d H:i:s");

		$filename = $this->doUpload('ticket', $_FILES['files']);

		$model = $this->ticket->insert($data);
		$this->ticket->recordFiles([
			'ticket_id' => $model->id,
			'nombre' => $filename,
			'ruta' => 'uploads/evidencia/',
			'cerrado' => FALSE
		]);

		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => TRUE,
				'msg' => 'Se han dado de alta tu ticket',
				'data' => $data
			]));

	}

	/**
	 * Funcion para cambiar el estado del Ticket de abierto a cerrado
	 * @return Array
	 **/
	public function close() {
		$this->load->library('form_validation');

		$this->form_validation->set_rules('comentario_cierre','Comentario', 'required');
		if(empty($_FILES['files']['name'])) {
			$this->form_validation->set_rules('files','Evidencia','required');
		}

		$this->form_validation->set_message('required', 'El Campo {field} es requerido!');

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
		$id = $data['ticket_id'];
		unset($data['ticket_id']);
		$data['estatus_id'] = FALSE;
		$data['fecha_hora_cierre'] = date("Y-m-d H:i:s");

		$filename = $this->doUpload('ticket', $_FILES['files']);

		$model = $this->ticket->update($data, $id);
		$this->ticket->recordFiles([
			'ticket_id' => $model->id,
			'nombre' => $filename,
			'ruta' => 'uploads/evidencia/',
			'cerrado' => TRUE
		]);

		return $this->output
				->set_header("Access-Control-Allow-Origin: *")
				->set_header("Access-Control-Allow-Headers: *")
				->set_content_type("application/json")
				->set_output(json_encode([
					'success' => TRUE,
					'msg' => 'Se ha cerrado correctamente el ticket!',
					'data' => $model
				]));
	}

	/**
	 * Funcion que cambia el estado de cancelado del ticket.
	 * @return Array
	 **/
	public function delete($id) {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		$data['cancelado'] = 1;
		$data['estatus_id'] = 0;
		$data['fecha_hora_cierre'] = date("Y-m-d H:i:s");
		$data['comentario_cierre'] = 'Cancelado';
		$this->ticket->update($data, $id);
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_header("Access-Control-Allow-Methods: DELETE")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => TRUE,
				'msg' => 'Se ha cancelado correctamente el ticket!',
				'data' => null
			]));
	}

	/**
	 * Funcion que cambia el estado del ticket a autorizado.
	 * @return Array
	 **/
	public function autoriza() {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		$id = $data['ticket_id'];
		$data['autorizado'] = 1;
		unset($data['ticket_id']);
		$model = $this->ticket->update($data, $id);
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode([
				'success' => TRUE,
				'msg' => 'Se ha autorizado correctamente el Ticket',
				'data' => $model
			]));
	}

	/**
	 * Funcion que retorna un arreglo con los recursos del modelo de Ticket con el estatus de cancelado
	 * @return
	 **/
	public function cancelados() {
		$post = json_decode(file_get_contents("php://input"), TRUE);
		$data = ($post['isAdmin']) ? $this->ticket->all_cancelados() : $this->ticket->get_tickets_cancelados($post['locales']);
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($data));
	}

	/**
	 * Funcion que retorna un arreglo de los recursos del modelo Ticket filtrado en base filtro mandados via POST
	 * @return Array
	 **/
	public function filter_data() {
		$data = $this->input->post();
		$locales = array_filter(explode(',', str_replace("'","",$data['locales'])));
		unset($data['locales']);
		unset($data['categoria']);
		$data['estatus_id'] = ($data['estatus_id'] == 'false') ? 0 : 1;
		$data['cancelado'] = ($data['cancelado'] == 'false') ? 0 : 1;
		$data = array_filter($data, function($val){
			return !($val == '' || $val == 'undefined' || $val == 0);
		});
		$model = $this->ticket->filter_data($data, $locales);
		if(isset($data['export'])){
			return $this->CreateExcel($model);
		}
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($model));
	}

	/**
	 * Funcion que realiza la carga de la imagen y tambien hace un proceso de reducir la calidad de la imagen para reducir el peso de la imagen, respetando las medidas de la imagen original.
	 * @return String
	 **/
	protected function doUpload($title, $files) {
		$filename = $files['tmp_name'];

		list($width, $height) = getimagesize($filename);

		$image_p = imagecreatetruecolor($width, $height);
		if ($files['type'] == 'image/jpeg') {
			$image = imagecreatefromjpeg($filename);
		} else if($files['type'] == 'image/png') {
			$image = imagecreatefrompng($filename);
		}

		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width, $height);

		if ($files['type'] == 'image/jpeg') {
			$title = $title."_".uniqid().".jpg";
			imagejpeg($image_p, "./uploads/evidencia/$title", 40);
		} else if($files['type'] == 'image/png') {
			$title = $title."_".uniqid().".png";
			imagepng($image_p, "./uploads/evidencia/$title", 5, PNG_NO_FILTER);
		}

		return $title;
	}

	/**
	 * Funcion que genera un archivo MS Excel con base al arreglo que se pasa como parametro.
	 * @param Array
	 * @return Blob | Download
	 * 
	 **/
	protected function CreateExcel($data = []) {
		$tmpName = "files/export.xlsx";
		$excelReader = PHPExcel_IOFactory::createReaderForFile($tmpName);
		$objPHPExcel = $excelReader->load($tmpName);

		// Set document props
		$objPHPExcel->getProperties()->setCreator("Francisco Javier Garduño Juan")
			->setLastModifiedBy("Francisco Javier Garduño Juan")
			->setTitle("SAI Export")
			->setSubject("SAI Export")
			->setDescription("SAI Export")
			->setKeywords("phpexcel office codeigniter php sai")
			->setCategory("export");

		// Create a first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Local');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Categoria');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Proveedor');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Prioridad');
		$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Fecha y Hora');
		$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Estatus');
		$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Cancelado');

		// Set auto size
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

		$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray([
			'fill' => [
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => ['argb' => PHPExcel_Style_Color::COLOR_DARKBLUE]
			],
			'font' => [
				'bold' => true,
				'color' => ['argb' => PHPExcel_Style_Color::COLOR_WHITE]
			]
		]);

		// Set data
		for ($i=0; $i < count($data); $i++) { 
			$objPHPExcel->getActiveSheet()->setCellValue('A'.($i + 2), $data[$i]['codigo'].' - '.$data[$i]['local'])
				->setCellValue('B'.($i + 2), $data[$i]['categoria'].' - '.$data[$i]['subcategoria'])
				->setCellValue('C'.($i + 2), $data[$i]['proveedor'])
				->setCellValue('D'.($i + 2), $data[$i]['prioridad'])
				->setCellValue('E'.($i + 2), $data[$i]['fecha_hora'])
				->setCellValue('F'.($i + 2), $data[$i]['status'])
				->setCellValue('G'.($i + 2), ($data[$i]['cancelado'] == '1') ? 'Cancelado' : 'Activo');
			$objPHPExcel->getActiveSheet()->getStyle('D'.($i + 2))->applyFromArray([
				'fill' => [
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => ['argb' => $data[$i]['color']]
				]
			]);
		}

		$filename = 'export.xlsx';
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		ob_end_clean();
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: *");
		header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename='.$filename);
        $objWriter->save('php://output');
	}
}