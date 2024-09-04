<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reporte extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->database('default');
	}

	/**
	 * Funcion que retorna el resultado de una consulta dependiendo la solicitud.
	 * @return Array
	 **/
	public function reporte_data() {
		$post = $this->input->post();
		$where = '';
		if (!empty($post['fecha_inicio']) && !empty($post['fecha_final'])) {
			$where = "WHERE fecha_hora BETWEEN '".$post['fecha_inicio']." 00:00:00' AND '".$post['fecha_final']." 23:59:59'";
		}
		switch ($post['filtro']) {
			case 'prioridad':
				$query = "SELECT
					p.nombre as label,
					p.color as color,
					count(p.nombre) as count
				FROM tickets t
					LEFT JOIN locales l ON t.local_id = l.id
					LEFT JOIN prioridades p ON t.prioridad_id = p.id
					LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
					LEFT JOIN categorias c ON t.categoria_id = c.id
					LEFT JOIN categorias ch ON c.padre_id = ch.id
				$where
				GROUP BY p.nombre
				ORDER BY p.id";
				break;
			
			case 'proveedor':
				$query = "SELECT 
					pr.razon_social as label,
					count(pr.razon_social) as count
				FROM tickets t
					LEFT JOIN locales l ON t.local_id = l.id
					LEFT JOIN prioridades p ON t.prioridad_id = p.id
					LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
					LEFT JOIN categorias c ON t.categoria_id = c.id
					LEFT JOIN categorias ch ON c.padre_id = ch.id
				$where
				GROUP BY pr.razon_social";
				break;

			case 'local':
				$query = "SELECT 
					l.codigo as label,
					count(l.codigo) as count
				FROM tickets t
					LEFT JOIN locales l ON t.local_id = l.id
					LEFT JOIN prioridades p ON t.prioridad_id = p.id
					LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
					LEFT JOIN categorias c ON t.categoria_id = c.id
					LEFT JOIN categorias ch ON c.padre_id = ch.id
				$where
				GROUP BY l.codigo";
				break;
			case 'estatus':
				$query = "SELECT 
					IF(t.estatus_id = 1, 'Abierto', 'Cerrado') as label,
					count(t.estatus_id) as count
				FROM tickets t
					LEFT JOIN locales l ON t.local_id = l.id
					LEFT JOIN prioridades p ON t.prioridad_id = p.id
					LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
					LEFT JOIN categorias c ON t.categoria_id = c.id
					LEFT JOIN categorias ch ON c.padre_id = ch.id
				$where
				GROUP BY t.estatus_id";
				break;
			case 'categoria':
				$query = "SELECT 
					CONCAT(ch.nombre, ' - ', c.nombre) as label,
					count(t.categoria_id) as count
				FROM tickets t 
					LEFT JOIN locales l ON t.local_id = l.id
					LEFT JOIN prioridades p ON t.prioridad_id = p.id
					LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
					LEFT JOIN categorias c ON t.categoria_id = c.id
					LEFT JOIN categorias ch ON c.padre_id = ch.id
				$where
				GROUP BY t.categoria_id";
				break;
			case 'fecha':
				$query = "SELECT 
					date(t.fecha_hora) as label,
					count(t.categoria_id) as count
				FROM tickets t 
					LEFT JOIN locales l ON t.local_id = l.id
					LEFT JOIN prioridades p ON t.prioridad_id = p.id
					LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
					LEFT JOIN categorias c ON t.categoria_id = c.id
					LEFT JOIN categorias ch ON c.padre_id = ch.id
				$where
				GROUP BY date(t.fecha_hora)";
				break;
			case 'cancelado':
				$query ="SELECT 
					if(t.cancelado, 'Cancelado', 'Activo') as label,
					count(t.cancelado) as count
				FROM tickets t 
					LEFT JOIN locales l ON t.local_id = l.id
					LEFT JOIN prioridades p ON t.prioridad_id = p.id
					LEFT JOIN proveedores pr ON t.proveedor_id = pr.id
					LEFT JOIN categorias c ON t.categoria_id = c.id
					LEFT JOIN categorias ch ON c.padre_id = ch.id
				$where
				GROUP BY t.cancelado";
				break;
			default:
				break;
		}
		$result = $this->db->query($query);
		$data = $this->serializeData($result->result_array());
		return $this->output
			->set_header("Access-Control-Allow-Origin: *")
			->set_header("Access-Control-Allow-Headers: *")
			->set_content_type("application/json")
			->set_output(json_encode($data));
	}

	/**
	 * Funcion que le da formato al arreglo para uso del Front End
	 * @return Array
	 **/
	protected function serializeData($result) {
		$labels = [];
		$counts = [];
		$color = [];
		foreach ($result as $value) {
			$labels[] = $value['label'];
			$counts[] = $value['count'];
			$color[] = (isset($value['color'])) ? '#'.$value['color'] : '#'.dechex(rand(0x000000, 0xFFFFFF));
		}

		return compact('labels', 'counts', 'color');
	}
}