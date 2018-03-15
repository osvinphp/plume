<?php
error_reporting(0);
//ini_set('display_error', 1);
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {
	function __construct(){
	    parent::__construct();
	    $this->load->model('Api_model','',TRUE);
	    $this->load->helper('url');
	    date_default_timezone_set('utc');
	}

	public function cron(){
		$current = date('Y-m-d H:i:s');
		$sort = strtotime($current);
		$map_post = $this->Api_model->select_data('*','tbl_post',"( (type = 1 OR type = 2 ) AND cron_status = 0)");
		foreach ($map_post as $key => $value) {
			$post_create = $value->date_created;
			$date_sort = strtotime($post_create);
			$add_time = date('Y-m-d H:i:s',strtotime('+24 hour ',strtotime($value->date_created)));
			$sort_time = strtotime($add_time);
			if($sort_time < $sort){
				$this->db->where('id',$value->id);
				$this->db->update('tbl_post',array('cron_status'=>1));
			}			
		}
	}
}
?>