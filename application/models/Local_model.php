<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Local_model extends CI_Model {
	
	public function __construct() {
		$this->table = 'locales';
		$this->load->database('default');
	}

	public function all() {
		$sql = "SELECT id, nombre, codigo, proyecto, if(region IS NULL, 'Sin Región', CONCAT('Región ', region)) as region_nombre, region FROM locales";
		$result = $this->db->query($sql);
		return $result->result_array();
	}

	public function get_locales($list = '') {
		$this->db->select("id, concat(codigo, ' - ', nombre) as nombre");
		$this->db->from($this->table);
		$this->db->where_in('codigo', str_replace("'", "", explode(',', $list)));
		$result = $this->db->get();
		return $this->Collection($result);
	}
}