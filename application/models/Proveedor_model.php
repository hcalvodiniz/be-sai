<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proveedor_model extends CI_Model {
	public function __construct() {
		$this->table = 'proveedores';
		$this->load->database('default');
	}
}