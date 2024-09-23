<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_model extends CI_Model {

	public function __construct() {
		$this->table = 'tickets';
		$this->load->database('default');
		$this->diniz = $this->load->database('diniz', TRUE);
	}

	/**
	 * Funcion que trae todos los tickets y devuelve los registros en un arreglo
	 * @return Array
	 **/
	public function all() {
		$sql = "SELECT t.id, t.proveedor_id, pr.razon_social as proveedor, ch.nombre as categoria, c.nombre as subcategoria, l.nombre as local, l.codigo, t.local_id, p.id as color_id, p.nombre as prioridad, p.color, t.fecha_hora, t.fecha_hora_cierre, IF(t.estatus_id = 1, 'Abierto', 'Cerrado') as status, t.autorizado, t.cancelado, t.noempl FROM tickets t 
			LEFT JOIN locales l ON t.local_id = l.id
			LEFT JOIN prioridades p ON t.prioridad_id = p.id
			LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
			LEFT JOIN categorias c ON t.categoria_id = c.id
        	LEFT JOIN categorias ch ON c.padre_id = ch.id
			ORDER BY t.fecha_hora DESC";
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Funcion que trae todos los tickets con estatus cancelado y devuelve los registros en un arreglo.
	 * @return Array
	 **/
	public function all_cancelados() {
		$sql = "SELECT t.id, t.proveedor_id, pr.razon_social as proveedor, ch.nombre as categoria, c.nombre as subcategoria, l.nombre as local, l.codigo, t.local_id, p.id as color_id, p.nombre as prioridad, p.color, t.fecha_hora, t.fecha_hora_cierre, IF(t.estatus_id = 1, 'Abierto', 'Cerrado') as status, t.autorizado, t.cancelado, t.noempl FROM tickets t 
			LEFT JOIN locales l ON t.local_id = l.id
			LEFT JOIN prioridades p ON t.prioridad_id = p.id
			LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
			LEFT JOIN categorias c ON t.categoria_id = c.id
        	LEFT JOIN categorias ch ON c.padre_id = ch.id
			WHERE t.cancelado = 1";
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Funcion que devuelve los datos de un ticket de acuerdo a un id proporcionado.
	 * @param integer | numeric string
	 * @return Object
	 **/
	public function find($id) {
		$sql = "SELECT t.id, pr.razon_social as proveedor, ch.nombre as categoria, c.nombre as subcategoria, l.nombre as local, l.codigo, p.id as color_id, p.nombre as prioridad, p.color, t.fecha_hora, t.fecha_hora_cierre, t.estatus_id as status, t.comentario, t.comentario_cierre, t.cancelado, t.empl_cancela FROM tickets t 
			LEFT JOIN locales l ON t.local_id = l.id
			LEFT JOIN prioridades p ON t.prioridad_id = p.id
			LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
			LEFT JOIN categorias c ON t.categoria_id = c.id
        	LEFT JOIN categorias ch ON c.padre_id = ch.id
			WHERE t.id = '$id'";
		$result = $this->db->query($sql);
		$model = (object)$result->result_array()[0];
		if(!is_null($model->empl_cancela)){
			$user = $this->diniz->query("SELECT * FROM gd.usuarios WHERE noempl = '$model->empl_cancela'");
			$result = (object)$user->result_array()[0];
			$model->empleado = $result->nombre." ".$result->ap_paterno." ".$result->ap_materno;
		} else {
			$model->empleado = '';
		}
		return $model;
	}

	/**
	 * Funcion que devuelve un arreglo de tickets basados en parametros que vienen determinados en el parametro "filters"
	 * @param $filters Array | $locales Array
	 * @return Array
	 **/
	public function filter_data($filters = [], $locales = []) {
		$this->db->select("t.id, pr.razon_social as proveedor, ch.nombre as categoria, c.nombre as subcategoria, l.nombre as local, l.codigo, p.id as color_id, p.nombre as prioridad, p.color, t.fecha_hora, t.fecha_hora_cierre, IF(t.estatus_id = 1, 'Abierto', 'Cerrado') as status, t.autorizado, t.cancelado, t.noempl");
		$this->db->from('tickets t');
		$this->db->join('locales l', 't.local_id = l.id', 'left');
		$this->db->join('prioridades p', 't.prioridad_id = p.id', 'left');
		$this->db->join('proveedores pr', 't.proveedor_id = pr.id', 'left');
		$this->db->join('categorias c', 't.categoria_id = c.id', 'left');
		$this->db->join('categorias ch', 'c.padre_id = ch.id', 'left');
		if(count($locales) > 0){
			$this->db->where_in('l.codigo', $locales);
		}
		foreach ($filters as $key => $value) {
			if ($key !== 'fecha_inicio' && $key !== 'fecha_fin' && $key !== 'export') {
				$this->db->where($key, $value);
			} else {
				if ($key == 'fecha_inicio') {
					$this->db->where("fecha_hora >=", $value." 00:00:00");
				}
				if ($key == 'fecha_fin') {
					$this->db->where("fecha_hora <=", $value." 23:59:59");
				}
			}
		}
		$this->db->order_by('t.fecha_hora','DESC');
		$result = $this->db->get();
		return $result->result_array();
	}

	/**
	 * Funcion que devuelve los tickets basado en el string de la variable "$locales"
	 * @param $locales String
	 * @return Array
	 **/
	public function get_tickets($locales = '') {
		$locales = str_replace("'", "", $locales);
		$this->db->select("t.id, pr.razon_social as proveedor, ch.nombre as categoria, c.nombre as subcategoria, l.nombre as local, l.codigo, p.id as color_id, p.nombre as prioridad, p.color, t.fecha_hora, t.fecha_hora_cierre, IF(t.estatus_id = 1, 'Abierto', 'Cerrado') as status, t.autorizado, t.cancelado, t.noempl");
		$this->db->from('tickets t');
		$this->db->join('locales l', 't.local_id = l.id', 'left');
		$this->db->join('prioridades p', 't.prioridad_id = p.id', 'left');
		$this->db->join('proveedores pr', 't.proveedor_id = pr.id', 'left');
		$this->db->join('categorias c', 't.categoria_id = c.id', 'left');
		$this->db->join('categorias ch', 'c.padre_id = ch.id', 'left');
		$this->db->where_in('l.codigo', explode(',', $locales));
		$this->db->order_by('t.fecha_hora','DESC');
		$result = $this->db->get();
		return $result->result_array();
	}

	/**
	 * Funcion que devuelve los tickets cancelados basado en el string de la variable "$locales"
	 * @param $locales String
	 * @return Array
	 **/
	public function get_tickets_cancelados($locales = '') {
		$this->db->select("t.id, pr.razon_social as proveedor, ch.nombre as categoria, c.nombre as subcategoria, l.nombre as local, l.codigo, p.id as color_id, p.nombre as prioridad, p.color, t.fecha_hora, t.fecha_hora_cierre, IF(t.estatus_id = 1, 'Abierto', 'Cerrado') as status, t.autorizado, t.cancelado, t.noempl");
		$this->db->from('tickets t');
		$this->db->join('locales l', 't.local_id = l.id', 'left');
		$this->db->join('prioridades p', 't.prioridad_id = p.id', 'left');
		$this->db->join('proveedores pr', 't.proveedor_id = pr.id', 'left');
		$this->db->join('categorias c', 't.categoria_id = c.id', 'left');
		$this->db->join('categorias ch', 'c.padre_id = ch.id', 'left');
		$this->db->where('t.cancelado', 1);
		$this->db->where_in('l.codigo', explode(',', $locales));
		$result = $this->db->get();
		return $result->result_array();
	}

	/**
	 * Funcion que obtiene las evidencias con las rutas de los archivos.
	 * @param $id integer
	 * @return Array
	 **/
	public function get_photos($id) {
		$this->db->from('evidencia');
		$this->db->where(['ticket_id' => $id]);
		$result = $this->db->get();
		return $this->collection($result);
	}

	/**
	 * Funcion que elimina el registro de la tabla evidencia
	 * NOTA: esta funcion ya no esta en uso.
	 * @param $ticket_id integer
	 * @return boolean
	 **/
	public function deleteFiles($ticket_id) {
		$this->db->delete('evidencia', ['ticket_id' => $ticket_id]);
	}

	/**
	 * Funcion que registra las rutas y nombres de archivos de evidencia
	 * @param Array
	 * @return booleans
	 **/
	public function recordFiles($data) {
		$this->db->insert('evidencia', $data);
	}
}