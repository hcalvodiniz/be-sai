<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categoria_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->table = 'categorias';
		$this->load->database('default');
	}

	public function all() {
		$this->db->from($this->table);
		$this->db->where('padre_id IS NULL');
		$result = $this->db->get();
		return $this->Collection($result);
	}
}