<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_model extends CI_Model {

	public function __construct() {
		$this->table = 'tickets';
		$this->load->database('default');
		$this->diniz = $this->load->database('diniz', TRUE);
	}

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

	public function get_tickets($locales = '') {
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

	public function get_photos($id) {
		$this->db->from('evidencia');
		$this->db->where(['ticket_id' => $id]);
		$result = $this->db->get();
		return $this->collection($result);
	}

	public function deleteFiles($ticket_id) {
		$this->db->delete('evidencia', ['ticket_id' => $ticket_id]);
	}

	public function recordFiles($data) {
		$this->db->insert('evidencia', $data);
	}
}