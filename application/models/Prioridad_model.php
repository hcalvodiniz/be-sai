<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prioridad_model extends CI_Model {
	public function __construct() {
		$this->table = 'prioridades';
		$this->load->database('default');
	}
}