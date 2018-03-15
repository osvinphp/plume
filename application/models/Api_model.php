<?php 
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Api_model extends CI_Model{

    	public function select_data($select,$table,$where=null,$order=null){
	        if(empty($where)&&empty($order)){
		        $this->db->select($select);
		        $this->db->from($table);
		        $query = $this->db->get()->result();
		    }elseif(empty($order)){
			    $this->db->select($select);
				$this->db->from($table);
				$this->db->where($where);
				$query = $this->db->get()->result();
			}else{
			    $this->db->select($select);
	            $this->db->from($table);
	            $this->db->where($where);
	            $this->db->order_by($order);
	            $query = $this->db->get()->result();
			}
	    	return $query;
	    }
	    public function insert_data($table,$data){
	        $this->db->insert($table,$data);
	        $insert_id = $this->db->insert_id();
	        return $insert_id;
	    }
	    public function update_data($where,$table,$data){
	    	$this->db->where($where);
	    	$this->db->update($table,$data);
	    }
	    public function delete_data($where,$table){
	    	$this->db->where($where);
	    	$this->db->delete($table);
	    	return "delete";
	    }
	    public function signup($data){
	    	$sel = $this->select_data("*","tbl_users","email = '".$data['email']."' OR fullname = '".$data['fullname']."'");
	    	if(!empty($sel)){
	    		if(!empty($data['fb_id']) && empty($sel[0]->fb_id)){
	    			$this->update_data(array('email'=>$data['email']),'tbl_users',array('fb_id'=>$data['fb_id']));
	    			$sel1 = $this->select_data("*","tbl_users",array('email'=>$data['email']));
		    		$send = [
	    				'response'=>'insert',
	    				'info'=>$sel1[0]
	    			];
	    			return $send;
	    		}else{
	    			if($sel[0]->fullname == $data['fullname']){
	    				return "name";
	    			}else{
	    				return "email";
	    			}
	    		}
	    	}else{
	    		$insert = array(
	    			'fullname'=>$data['fullname'],
	    			'email'=>$data['email'],
	    			'password'=>$data['password'],
	    			'fb_id'=>$data['fb_id'],
	    			'age'=>$data['age'],
	    			'phone'=>$data['phone'],
	    			'date_created'=>date('Y-m-d H:i:s')
	    		);
	    		$ins = array_filter($insert);
	    		$last_id = $this->insert_data('tbl_users',$ins);
	    		$this->db->insert('tbl_login',array('userId'=>$last_id,'unique_deviceId'=>$data['unique_deviceId'],'device_id'=>$data['device_id'],'token_id'=>$data['token_id'],'status'=> 1,'date_created'=>date('Y-m-d H:i:s')));
	    		$sel1 = $this->select_data("*","tbl_users",array('id'=>$last_id));
	    		$send = [
    				'response'=>'insert',
    				'info'=>$sel1[0]
    			];
    			return $send;
	    	}
	    }
	    public function login($data){
	    	if(empty($data['fb_id'])){
	    		$sel = $this->select_data("*","tbl_users","(fullname = '".$data['fullname']."' AND password = '".$data['password']."')");
	    		if(!empty($sel)){
	    			$login = $this->db->query("SELECT * FROM tbl_login WHERE status = 1 AND userId = '".$sel[0]->id."' ORDER BY id DESC ")->row();
	    			if($login->status == 1){
	    				$this->pushdata($sel[0]->id,'You have been logged out of this account.','logout','Logout');
	    				$this->db->where('userId',$sel[0]->id);
	    				$this->db->update('tbl_login',array('status'=>0));	    				
	    			}
	    			$this->db->insert('tbl_login',array('userId'=>$sel[0]->id,'unique_deviceId'=>$data['unique_deviceId'],'device_id'=>$data['device_id'],'token_id'=>$data['token_id'],'status'=> 1,'date_created'=>date('Y-m-d H:i:s')));
	    			$select = $this->Api_model->commonPath($sel,'profile_pic');
	    			$send = [
	    				'response'=>'login',
	    				'info'=>$select[0]
	    			];
	    			return $send;
	    		}else{
	    			return "invalid";
	    		}
	    	}else{
	    		$sel = $this->select_data("*","tbl_users",array('fb_id'=>$data['fb_id']));
	    		if(!empty($sel)){
	    			$login = $this->db->query("SELECT * FROM tbl_login WHERE status = 1 AND userId = '".$sel[0]->id."' ORDER BY id DESC ")->row();
	    			if($login->status == 1){
	    				$this->pushdata($sel[0]->id,'You have been logged out of this account.','logout','Logout');
	    				$this->db->where('userId',$sel[0]->id);
	    				$this->db->update('tbl_login',array('status'=>0));	    				
	    			}
	    			$this->db->insert('tbl_login',array('userId'=>$sel[0]->id,'unique_deviceId'=>$data['unique_deviceId'],'device_id'=>$data['device_id'],'token_id'=>$data['token_id'],'status'=> 1,'date_created'=>date('Y-m-d H:i:s')));
	    			if(!empty($sel[0]->profile_pic)){
	    				$sel[0]->profile_pic = base_url().''.$sel[0]->profile_pic;
	    			}
	    			$send = [
	    				'response'=>'login',
	    				'info'=>$sel[0]
	    			];
	    			return $send;
	    		}else{
	    			return "invalid";
	    		}
	    	}
	    }
	    public function forgotpassword($email){
	    	$sel = $this->select_data("*","tbl_users",array('email'=>$email));
			if(empty($sel)){
				return "error";
			}else{
				$email1 = $sel[0]->email;
		        $userid = $sel[0]->id;
		        $static_key = "afvsdsdjkldfoiuy4uiskahkhsajbjksasdasdgf43gdsddsf";
		        $id = $userid . "_" . $static_key;
		        $result['b_id'] = base64_encode($id);
		        $result['name'] = $sel[0]->fullname;		       
				return $result;
			}
		}
		public function logout($userid){
			$sel = $this->select_data('*','tbl_users',array('id'=>$userid));
			if(!empty($sel)){
				$this->db->where('userId',$sel[0]->id);
				$this->db->update('tbl_login',array('status'=>0));
				return "update";
			}else{
				return "error";
			}
		}
		public function postdata($filter,$data){
			$query = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$data['userId']."'")->row();
			if(!empty($query)){
				$this->db->insert('tbl_post',$filter);
				$last_id = $this->db->insert_id();
				$sel = $this->db->query("SELECT tbl_post.*,tbl_users.fullname from tbl_post JOIN tbl_users ON tbl_users.id = tbl_post.userId WHERE tbl_post.id = '".$last_id."'")->row();
				//$sel = $this->db->query("SELECT tbl_post.*,tbl_users.fullname as fullname FROM tbl_post LEFT JOIN tbl_users ON tbl_post.userId = tbl_users.id WHERE tbl_post.id = '".$last_id."'")->result();
				$sel->height = (int)$sel->height;
				$sel->width = (int)$sel->width;
				$type = $sel->type;
				if(!empty($sel->thumbnail)){
					$sel->thumbnail = base_url().''.$sel->thumbnail;
				}
				if($type == 0){
					if(!empty($sel->image)){
						$sel->image = base_url().''.$sel->image;
					}
				}elseif($type == 1){
					if(!empty($sel->map_image)){
						$sel->map_image = base_url().''.$sel->map_image;
					}
				}else{
					if(!empty($sel->map_image)){
						$sel->map_image = base_url().''.$sel->map_image;
					}
					if(!empty($sel->image)){
						$sel->image = base_url().''.$sel->image;
					}
				}
				return $sel;
			}
			else{
				return "error";
			}
		}
		public function follow($data){
			$check = $this->Api_model->select_data('*','tbl_users',array('id'=>$data['to_id']));
			if(!empty($check)){
				$sel = $this->db->query("SELECT * FROM tbl_follow WHERE to_id = '".$data['to_id']."' and from_id = '".$data['from_id']."' ")->row();
				if(empty($sel)){
					$both = $this->db->query("SELECT * FROM tbl_follow WHERE (from_id = '".$data['to_id']."' and to_id = '".$data['from_id']."') ")->result();
					if(!empty($both)){
						$this->db->where("(from_id = '".$data['to_id']."' and to_id = '".$data['from_id']."')");
						$this->db->update('tbl_follow',array('status_both'=>2));
						$this->db->insert('tbl_follow',$data);
						$last_id = $this->db->insert_id();
						$this->db->where('id',$last_id);
						$this->db->update('tbl_follow',array('status_both'=>2));
					}else{
						$this->db->insert('tbl_follow',$data);
						$last_id = $this->db->insert_id();
					}

					$send = $this->db->query("SELECT status FROM tbl_follow WHERE id='".$last_id."' ")->row();
					//pushdata($userid,$message,$action,$title,$challengeId=null)
					$info = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$data['from_id']."' ")->row();
					$pushstatus = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$data['to_id']."' ")->row();
				    $this->pushdata($pushstatus->id,"".$info->fullname." is following you now.","follow","Follow");
					$this->db->insert('tbl_notification',array('type'=>3,'post_id'=>$last_id,'to_id'=>$data['from_id'],'from_id'=>$data['to_id'],'date_created'=>date('Y-m-d H:i:s')));
					return array('response'=>$send,'mmsg'=>"follow");
				}else{			
					//print_r($sel);
					$both = $this->db->query("SELECT * FROM tbl_follow WHERE (from_id = '".$data['to_id']."' and to_id = '".$data['from_id']."') ")->result();
					if(!empty($both)){
						if($data['status'] == 0){
							$this->db->where("(from_id = '".$data['to_id']."' and to_id = '".$data['from_id']."')");
							$this->db->update('tbl_follow',array('status_both'=>0));						
							$this->db->where("from_id = '".$data['from_id']."' AND to_id = '".$data['to_id']."'");
							$this->db->update('tbl_follow',array('status_both'=>0));
						}else{
							$this->db->where("(from_id = '".$data['to_id']."' and to_id = '".$data['from_id']."')");
							$this->db->update('tbl_follow',array('status_both'=>2));
							$this->db->where("from_id = '".$data['from_id']."' AND to_id = '".$data['to_id']."'");
							$this->db->update('tbl_follow',array('status_both'=>2));
						}
					}
					//$this->db->where("to_id = '".$data['from_id']."' AND from_id = '".$data['to_id']."'");
					$this->db->where("from_id = '".$data['from_id']."' AND to_id = '".$data['to_id']."'");
					$this->db->update('tbl_follow',array('status'=>$data['status']));
					$response = $this->db->query("SELECT status FROM tbl_follow WHERE to_id = '".$data['to_id']."' and from_id = '".$data['from_id']."'")->row();
					$response1 = $this->db->query("SELECT id FROM tbl_follow WHERE to_id = '".$data['to_id']."' and from_id = '".$data['from_id']."'")->row();
					// print_r($response1->id);die;
					if($response->status == 1){
						$this->db->insert('tbl_notification',array('type'=>3,'post_id'=>$response1->id,'to_id'=>$data['from_id'],'from_id'=>$data['to_id'],'date_created'=>date('Y-m-d H:i:s')));
						$info = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$data['from_id']."' ")->row();
						$pushstatus = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$data['to_id']."' ")->row();
				    	$this->pushdata($pushstatus->id,"".$info->fullname." is following you now.","follow","Follow");
						$msg = "follow";
					}else{
						$msg = "unfollow";
					}
					return array('response'=>$response,'mmsg'=>$msg);
				}
			}else{
				return "error";
			}
		}
		// public function follow($data){
		// 	$sel = $this->db->query("SELECT * FROM tbl_follow WHERE (to_id = '".$data['to_id']."' and from_id = '".$data['from_id']."') OR (to_id = '".$data['from_id']."' and from_id = '".$data['to_id']."')")->row();
		// 	// print_r($sel);die;
		// 	if(empty($sel)){
		// 		$this->db->insert('tbl_follow',$data);
		// 		return "follow";
		// 	}else{				
		// 		$this->db->where("to_id = '".$data['from_id']."' AND from_id = '".$data['to_id']."'");
		// 		$this->db->or_where("from_id = '".$data['from_id']."' AND to_id = '".$data['to_id']."'");
		// 		$this->db->update('tbl_follow',array('status'=>$data['status']));
		// 		$response = $this->db->query("SELECT status FROM tbl_follow WHERE (to_id = '".$data['to_id']."' and from_id = '".$data['from_id']."') OR (to_id = '".$data['from_id']."' and from_id = '".$data['to_id']."')")->row();
		// 		// print_r($response->status);die;
		// 		if($response->status == 1){
		// 			$this->db->insert('tbl_notification',array('type'=>3,'to_id'=>$data['to_id'],'from_id'=>$data['from_id'],'date_created'=>date('Y-m-d H:i:s')));
		// 			$msg = "follow";
		// 		}else{
		// 			$msg = "unfollow";
		// 		}
		// 		return array('response'=>$response,'mmsg'=>$msg);
		// 	}
		// }
		public function blockuser($data){
			if($data['is_block'] == 1 || $data['is_block'] == 0){
				$ssel = $this->db->query("SELECT * FROM tbl_follow WHERE (to_id = '".$data['to_id']."' AND from_id = '".$data['from_id']."') OR (from_id = '".$data['to_id']."' AND to_id = '".$data['from_id']."')")->row();
				if(!empty($ssel)){
					$this->db->where("(from_id = '".$data['from_id']."' AND to_id = '".$data['to_id']."' AND (status = 0 OR status = 1)) OR (to_id = '".$data['from_id']."' AND from_id = '".$data['to_id']."' AND (status = 0 OR status = 1))");
					$this->db->update('tbl_follow',array('is_block'=>$data['is_block']));
					return "block";
				}else{
					$this->db->insert('tbl_follow',$data);
					return "block";
				}
			}else{
				return "error";
			}
		}
		public function mapdata($data){
			$sel = "SELECT tbl_post.*,tbl_users.fullname,tbl_users.profile_pic,( 3959 * acos( cos( radians('".$data['lat']."') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('".$data['lng']."') ) + sin( radians('".$data['lat']."') ) * sin( radians( lat ) ) ) ) AS distance FROM tbl_post LEFT JOIN tbl_users ON tbl_post.userId = tbl_users.id WHERE tbl_users.post_type = 0 AND tbl_post.cron_status = 0 AND (tbl_post.type = '".$data['type']."' OR tbl_post.type = 2)";
			$sel .=" HAVING distance <= 20 ";
			$sel .=" ORDER BY `distance` DESC";  
    	 	$select = $this->db->query($sel)->result();
    	 	foreach ($select as $key => $value) {
    	 		$particularlike=$this->db->query("SELECT * from tbl_postLike where userId='".$data['userid']."' and post_id='".$value->id."'")->row();
				if (!empty($particularlike)){
					$value->particularlike=$particularlike->status;
				}
				else{
					$value->particularlike=0;	
				}
    	 		if(!empty($value->profile_pic)){
					$value->profile_pic = base_url().''.$value->profile_pic;
				}
    	 		$type = $value->type;
    	 		if(!empty($value->thumbnail)){
					$value->thumbnail = base_url().''.$value->thumbnail;
				}
				if($type == 0){
					if(!empty($value->image)){
						$value->image = base_url().''.$value->image;
					}
				}elseif($type == 1){
					if(!empty($value->map_image)){
						$value->map_image = base_url().''.$value->map_image;
					}
				}else{
					if(!empty($value->map_image)){
						$value->map_image = base_url().''.$value->map_image;
					}
					if(!empty($value->image)){
						$value->image = base_url().''.$value->image;
					}
				}
    	 	}
    	 	$login = $this->db->query("SELECT * FROM tbl_login WHERE userId = '".$data['userid']."' ORDER BY id DESC")->row();
			return array('loginStatus'=>$login->status,'response'=>$select);
		}
		public function likepost($data){
			$sel = $this->Api_model->select_data('*','tbl_postLike',array('post_id'=>$data['id'],'userId'=>$data['userId']));
			$post = $this->Api_model->select_data('*','tbl_post',array('id'=> $data['id']));
			if(empty($sel)){
				$this->db->insert('tbl_postLike',array('post_id'=>$data['id'],'userId'=>$data['userId'],'status'=>$data['status'],'date_created'=>date('Y-m-d H:i:s')));
				$last_id = $this->db->insert_id();
				$this->db->insert('tbl_notification',array('type'=>1,'post_id'=>$last_id,'to_id'=>$data['userId'],'from_id'=>$post[0]->userId,'date_created'=>date('Y-m-d H:i:s')));

				$info = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$post[0]->userId."' ")->row();
				$pushstatus = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$data['userId']."' ")->row();
				if($post[0]->userId == $data['userId']){}else{
		    		$this->pushdata($info->id,"".$pushstatus->fullname." liked your moment.","like","Like",$data['id']);
		    	}
				return "like";
			}else{
				if($data['status'] == 0){  // for unlike
					$this->Api_model->update_data(array('post_id'=>$data['id'],'userId'=>$data['userId']),'tbl_postLike',array('status'=>$data['status']));
					$msg ="unlike";
				}else{ // for like
					$this->Api_model->update_data(array('post_id'=>$data['id'],'userId'=>$data['userId']),'tbl_postLike',array('status'=>$data['status']));
					$this->db->insert('tbl_notification',array('type'=>1,'post_id'=>$post[0]->id,'to_id'=>$data['userId'],'from_id'=>$post[0]->userId,'date_created'=>date('Y-m-d H:i:s')));
					$info = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$post[0]->userId."' ")->row();
					$pushstatus = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$data['userId']."' ")->row();
			    	if($post[0]->userId == $data['userId']){}else{
			    		$this->pushdata($info->id,"".$pushstatus->fullname." liked your moment.","like","Like",$data['id']);
			    	}
			    	$msg ="like";
				}
				$res = $this->db->query("SELECT * FROM tbl_postLike WHERE post_id='".$data['id']."' AND userId = '".$data['userId']."'")->row();
				return array('msg'=>$msg,'response'=>$res);
			}
		}
		public function deletepost($id,$type){
			$sel = $this->db->query("SELECT * FROM tbl_post Where id = '".$id."' ")->row();
			if(empty($sel)){
				return "error";
			}else{
				if($sel->type == 0){
					$path = $sel->image;
					unlink($path);
					$this->db->where('post_id',$id);
				   	$this->db->delete('tbl_notification');
				   	$this->db->where('id',$id);
				   	$this->db->delete('tbl_post');

				   	return "delete";
				}elseif($sel->type == 1){
					$path = $sel->map_image;
					unlink($path);
					$this->db->where('id',$id);
				   	$this->db->delete('tbl_post');
				   	$this->db->where('post_id',$id);
				   	$this->db->delete('tbl_notification');
				   	return "delete";
				}else{
					if($type == 0){
						$path = $sel->image;
						$path1 = $sel->map_image;
						unlink($path1);
						unlink($path);
						$this->db->where('id',$id);
					   	$this->db->delete('tbl_post');
					   	$this->db->where('post_id',$id);
				   		$this->db->delete('tbl_notification');
					   	return "delete";
					}elseif($type == 1){
						$path1 = $sel->map_image;
						unlink($path1);
						$this->db->where('id',$sel->id);
						$this->db->update('tbl_post',array('map_image'=>''));
					   	return "delete";
					}
				}			   
			}
		}
		public function profilelist($userid){
			$sel = $this->db->query("SELECT * FROM tbl_users  WHERE tbl_users.id <> '".$userid."' ")->result();
			foreach ($sel as $key => $value) {
				$query = $this->db->query("SELECT * FROM tbl_follow WHERE status = 0 AND to_id <>  '".$value->id."' ")->row();
				$post = $this->db->query("SELECT id FROM tbl_post WHERE userId = '".$value->id."'")->row()->id;
				// if($query->status == 1){
				// 	unset($sel[$key]);
				// }
				$value->follow = empty($query)?'':$query;
				$value->post_id = empty($post)?'':$post;
			}
			$select = $this->Api_model->commonPath($sel,'profile_pic');
			return $select;
		}
		public function profile($id){
			$sel['profile'] = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$id."'")->row();
			if(!empty($sel['profile']->profile_pic)){
				$sel['profile']->profile_pic = base_url().''.$sel['profile']->profile_pic;
			}
			$sel['profile_post'] = $this->db->query("SELECT * FROM tbl_post WHERE (type = 0 OR type = 2) AND userId = '".$id."'")->result();
			foreach ($sel['profile_post'] as $key => $value) {
				$value->height = (int)$value->height;
				$value->width = (int)$value->width;
				if($value->media_type == 2){
					$value->thumbnail = base_url().''.$value->thumbnail;
				}
				$type = $value->type;
				if($type == 0){
					if(!empty($value->image)){
						$value->image = base_url().''.$value->image;
					}
				}elseif($type == 1){
					if(!empty($value->map_image)){
						$value->map_image = base_url().''.$value->map_image;
					}
				}else{
					if(!empty($value->map_image)){
						$value->map_image = base_url().''.$value->map_image;
					}
					if(!empty($value->image)){
						$value->image = base_url().''.$value->image;
					}
				}
			}
			$sel['map_post'] = $this->db->query("SELECT * FROM tbl_post WHERE (type = 1 OR type = 2) AND cron_status = 0 AND userId = '".$id."'")->result();
			foreach ($sel['map_post'] as $key => $value) {
				$value->height = (int)$value->height;
				$value->width = (int)$value->width;
				if($value->media_type == 2){
					$value->thumbnail = base_url().''.$value->thumbnail;
				}
				$type = $value->type;
				if($type == 0){
					if(!empty($value->image)){
						$value->image = base_url().''.$value->image;
					}
				}elseif($type == 1){
					if(!empty($value->map_image)){
						$value->map_image = base_url().''.$value->map_image;
					}
				}else{
					if(!empty($value->map_image)){
						$value->map_image = base_url().''.$value->map_image;
					}
					if(!empty($value->image)){
						$value->image = base_url().''.$value->image;
					}
				}
			}
			return $sel;
		}
		public function profiledetail($id,$userid){
			$sel = $this->Api_model->select_data('*','tbl_post',array('id'=>$id));
			if(!empty($sel)){
				foreach($sel as $key => $value){
					$usr = $this->db->query("SELECT * from tbl_users where id='".$value->userId."'")->row();
					if($value->media_type == 2){
						$value->thumbnail = base_url().''.$value->thumbnail;
					}
					$type = $value->type;
					if($type == 0){
						if(!empty($value->image)){
							$value->image = base_url().''.$value->image;
						}
					}elseif($type == 1){
						if(!empty($value->map_image)){
							$value->map_image = base_url().''.$value->map_image;
						}
					}else{
						if(!empty($value->map_image)){
							$value->map_image = base_url().''.$value->map_image;
						}
						if(!empty($value->image)){
							$value->image = base_url().''.$value->image;
						}
					}

					$particularlike=$this->db->query("SELECT * from tbl_postLike where userId='".$userid."' and post_id='".$value->id."'")->row();
					if (!empty($particularlike)){
						$value->particularlike=$particularlike->status;
					}
					else{
						$value->particularlike=0;	
					}

					$query = $this->db->query("SELECT tbl_postComent.*,fullname,profile_pic FROM tbl_postComent JOIN tbl_users ON tbl_users.id = tbl_postComent.userId WHERE post_id = '".$value->id."'")->result();
					foreach ($query as $key => $value12) {
						if(!empty($value12->profile_pic)){
							$value12->profile_pic = base_url().''.$value12->profile_pic;
						}
					}
					$like = $this->db->query("SELECT tbl_postLike.*,fullname,profile_pic FROM tbl_postLike JOIN tbl_users ON tbl_users.id = tbl_postLike.userId WHERE post_id = '".$value->id."'")->result();
					foreach ($like as $key => $value121) {
						if(!empty($value121->profile_pic)){
							$value121->profile_pic = base_url().''.$value121->profile_pic;
						}
					}
					$value->like = empty($query)?$like:$like;
					$value->comment = empty($query)?$query:$query;
					$value->fullname = $usr->fullname;
					$ppc = base_url().''.$usr->profile_pic;
					$value->profile_pic = empty($usr->profile_pic)?'':$ppc;
				}
				return $sel;
			}else{
				return "error";
			}
		}
		public function postcomment($data){
			$sel = $this->Api_model->select_data('*','tbl_post',array('id'=>$data['post_id']));
			if(!empty($sel)){
				$last_id = $this->Api_model->insert_data('tbl_postComent',$data);
				$selct = $this->db->query("SELECT tbl_postComent.*,tbl_users.fullname,tbl_users.profile_pic FROM tbl_postComent JOIN tbl_users ON tbl_users.id = tbl_postComent.userId WHERE tbl_postComent.id = '".$last_id."'")->result();
				$select = $this->Api_model->commonPath($selct,'profile_pic');
				$this->db->insert('tbl_notification',array('type'=>2,'post_id'=>$sel[0]->id,'to_id'=>$data['userId'],'from_id'=>$sel[0]->userId,'date_created'=>date('Y-m-d H:i:s')));

				$info = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$sel[0]->userId."' ")->row();
				$pushstatus = $this->db->query("SELECT * FROM tbl_users WHERE id = '".$data['userId']."' ")->row();
				if($sel[0]->userId != $data['userId']){
		    		$this->pushdata($info->id,"".$pushstatus->fullname." commented your picture.","comment","Comment",$sel[0]->id);
		    	}
				return $select[0];
			}else{
				return "error";
			}
		}
		public function blocklist($id){
			$this->db->select('tbl_users.*,tbl_follow.is_block');
			$this->db->from('tbl_follow');
			$this->db->join('tbl_users','tbl_users.id = tbl_follow.to_id');
			$this->db->where('tbl_follow.from_id',$id);
			$this->db->where('tbl_follow.is_block',1);
			$sel = $this->db->get()->result();
			$select = $this->Api_model->commonPath($sel,'profile_pic');
			return $select;
		}
		public function commonPath($variable,$obj){
			foreach ($variable as $key => $value) {
				if(!empty($value->$obj)){
					$value->$obj = base_url().''.$value->$obj;
				}
			}
			return $variable;
		}
		public function searchfriend($data){
			function custom_sort($a,$b) {
		        return $b->count>$a->count;
		    }
            $this->db->select('tbl_users.id as user_id,bio,fullname,email,profile_pic');
            $this->db->from('tbl_users');
            $this->db->where('tbl_users.id !=',$data['id']);
			$sel = $this->db->get()->result();
			// print_r($sel);die;
			foreach ($sel as $key => $value) {
				$count = $this->db->query("SELECT count(id) as count FROM tbl_post WHERE userId = '".$value->user_id."'")->row()->count;
				if(!empty($value->profile_pic)){
					$value->profile_pic = base_url().''.$value->profile_pic;
				}
				$query = $this->db->query("SELECT * FROM tbl_post WHERE userId = '".$value->user_id."' ORDER BY date_created desc")->row();
				$follow = $this->db->query("SELECT id,to_id,from_id,status,is_block FROM tbl_follow WHERE ( (to_id = '".$value->user_id."' AND from_id = '".$data['id']."') OR (to_id = '".$data['id']."' AND from_id = '".$value->user_id."') ) ")->row();
				//print_r($follow);
				if($follow->is_block == 1){
					unset($sel[$key]);
				}
				// print_r($follow);
				if(empty($query)){
					unset($sel[$key]);
				}
				if($follow->is_block == 1){
					unset($sel[$key]);
				}
				$value->post_id = empty($query)?'':$query->id;
				$value->status = empty($query)?'':$follow->status;
				$value->is_block = empty($follow)?'0':$follow->is_block;
				$value->count = empty($count)?'0':$count;
			}
			$val = array_values($sel);
			usort($val,"custom_sort");
			return $val;
		}
		public function followerslist($data){
			if($data['type'] == 1){
				$sel = $this->db->query("SELECT tbl_follow.id,tbl_follow.status,tbl_follow.to_id,tbl_follow.from_id,tbl_users.fullname,tbl_users.profile_pic FROM tbl_follow JOIN tbl_users ON tbl_users.id = tbl_follow.to_id WHERE status = 1 AND is_block = 0 AND ( from_id = '".$data['userid']."' )")->result();
				// print_r($sel);die;
				foreach ($sel as $key => $value) {
					$qq = $this->db->query("SELECT * FROM tbl_follow WHERE status = 1 AND is_block = 0 AND (to_id = '".$value->from_id."' AND from_id = '".$value->to_id."') ")->row();
					
					if(!empty($qq)){
						unset($sel[$key]);
					}
					// print_r($qq);
					if(!empty($value->profile_pic)){
						$value->profile_pic = base_url().''.$value->profile_pic;
					}
				}
				// $fol = $this->db->query("SELECT id as from_id,fullname,profile_pic FROM tbl_users WHERE id != '".$data['userid']."'")->result();
				// foreach ($fol as $key => $value) {
				// 	$qqry = $this->db->query("SELECT * FROM tbl_follow WHERE status = 1 AND (is_block = 0 OR is_block = 1 ) AND ( (to_id = '".$value->id."' AND from_id = '".$data['userid']."') OR (from_id = '".$value->id."' AND to_id = '".$data['userid']."') )")->row();
				// 	if(!empty($qqry)){
				// 		unset($fol[$key]);
				// 	}
				// 	if(!empty($value->profile_pic)){
				// 		$value->profile_pic = base_url().''.$value->profile_pic;
				// 	}
				// 	$value->status = 0;
				// 	$value->id = '';
				// 	$value->to_id = $data['userid'];

				// }
				//$send = array_merge($sel,$fol);
				return array_values($sel);
			}elseif($data['type'] == 2){
				$sel = $this->db->query("SELECT * FROM tbl_follow WHERE status = 1 AND is_block = 0 AND ( to_id = '".$data['userid']."' OR from_id = '".$data['userid']."')")->result();
				// print_r($sel);die;
				foreach ($sel as $key => $value) {
					$query[] = $this->db->query("SELECT tbl_follow.id,tbl_follow.status,tbl_follow.to_id,tbl_follow.from_id,tbl_users.fullname,tbl_users.profile_pic FROM tbl_follow JOIN tbl_users ON tbl_users.id = tbl_follow.to_id WHERE status = 1 AND is_block = 0 AND (to_id = '".$value->from_id."' AND from_id = '".$data['userid']."') ")->row();
					// if(empty($query)){
					// 	unset($sel[$key]);
					// }else{
						
					// }
					// print_r($query);
				}
				foreach ($query as $key => $value) {
					if(!empty($value->profile_pic)){
						$value->profile_pic = base_url().''.$value->profile_pic;
					}
				}
				return array_values(array_filter($query));
			}else{
				return "error";
			}			
		}
		public function mycommunity($data){
			$sel = $this->db->query("SELECT * FROM tbl_follow WHERE status = 1 AND is_block = 0 AND ( to_id = '".$data['userid']."' OR from_id = '".$data['userid']."')")->result();
				foreach ($sel as $key => $value) {
					$query[] = $this->db->query("SELECT tbl_follow.id,tbl_follow.status,tbl_follow.to_id,tbl_follow.from_id,tbl_users.fullname,tbl_users.profile_pic FROM tbl_follow JOIN tbl_users ON tbl_users.id = tbl_follow.to_id WHERE status = 1 AND is_block = 0 AND status_both = 2 AND (to_id = '".$value->from_id."' AND from_id = '".$data['userid']."') ")->row();
				}
				// print_r($sel);die;
				foreach ($query as $key => $value) {
					// if($data['tab_type'] == $data['userid']){
					// 	$value->type = 2;
					// }else{
						$value->type = 1;
					//}
					if(!empty($value->profile_pic)){
						$value->profile_pic = base_url().''.$value->profile_pic;
					}
				}
				$common =  array_values(array_filter($query));

			if($data['type'] == 1){
				$single = $this->db->query("SELECT tbl_follow.id,tbl_follow.status,tbl_follow.to_id,tbl_follow.from_id,tbl_users.fullname,tbl_users.profile_pic FROM tbl_follow JOIN tbl_users ON tbl_users.id = tbl_follow.from_id WHERE status = 1 AND is_block = 0 AND status_both = 0 AND to_id = '".$data['userid']."'")->result();
				 // print_r($single);die;
				foreach ($single as $key => $value) {
					// if($value->to_id != $data['userid']){
					// 	$value->type = 2;
					// }else{
						$value->type = 0;
					//}
					if(!empty($value->profile_pic)){
						$value->profile_pic = base_url().''.$value->profile_pic;
					}
				}
				$merge = array_merge($common,$single);
				foreach ($merge as $key => $value) {
					if($value->type == 1){
						$from = $value->from_id;
						$value->from_id = $value->to_id;
						$value->to_id = $from;
					}
				}
				// print_r($merge);die;
				// foreach ($merge as $k => $v) {
				// 	if (strpos(serialize($result),$v->from_id) === FALSE) {
				// 		$result[] = $v;
				// 	}
				// }
				return $merge;
			}elseif($data['type'] == 2){
				$single = $this->db->query("SELECT tbl_follow.id,tbl_follow.status,tbl_follow.to_id,tbl_follow.from_id,tbl_users.fullname,tbl_users.profile_pic FROM tbl_follow JOIN tbl_users ON tbl_users.id = tbl_follow.to_id WHERE status = 1 AND is_block = 0  AND status_both = 0 AND from_id = '".$data['userid']."'")->result();
				foreach ($single as $key => $value) {
					if($data['tab_type'] == 0){
						$value->type = 1;
					}else{
						$value->type = 0;
					}
					if(!empty($value->profile_pic)){
						$value->profile_pic = base_url().''.$value->profile_pic;
					}
				}
				$merge = array_merge($common,$single);
				foreach ($merge as $key => $value) {
					if($value->type == 1){
						$from = $value->from_id;
						$value->from_id = $value->to_id;
						$value->to_id = $from;
					}
				}
				// foreach ($merge as $k => $v) {
				// 	if (strpos(serialize($result),$v->from_id) === FALSE) {
				// 		$result[] = $v;
				// 	}
				// }
				return $merge;
			}else{
				return "error";
			}
		}
		public function trendfollowlisting($data){
			if($data['type'] == 0){
				$sel = $this->db->query("SELECT * FROM tbl_post ORDER BY date_created DESC")->result();
				foreach ($sel as $key => $value) {
					if(!empty($value->thumbnail)){
						$value->thumbnail = base_url().''.$value->thumbnail;
					}
					if($value->type == 0){
						if(!empty($value->image)){
							$value->image = base_url().''.$value->image;
						}
					}elseif($value->type == 1){
						if(!empty($value->map_image)){
							$value->map_image = base_url().''.$value->map_image;
						}
					}else{
						if(!empty($value->map_image)){
							$value->map_image = base_url().''.$value->map_image;
						}
						if(!empty($value->image)){
							$value->image = base_url().''.$value->image;
						}
					}
					$user = $this->db->query("SELECT id,fullname,profile_pic FROM tbl_users WHERE id = '".$value->userId."' AND post_type = 0 ")->row();
					if(empty($user)){
						unset($sel[$key]);
					}
					$like = $this->db->query("SELECT userId,status FROM tbl_postLike WHERE POST_id = '".$value->id."' AND status = 1 ")->result();
					foreach ($like as $key => $valu) {
						$namee = $this->db->query("SELECT profile_pic FROM tbl_users WHERE id = '".$valu->userId."'")->row();
						if(!empty($namee->profile_pic)){
							$valu->profile_pic = base_url().''.$namee->profile_pic;
						}else{
							$valu->profile_pic = "";
						}
					}
					$value->fullname = $user->fullname;
					$value->profile_pic = $user->profile_pic;
					$value->like = $like;
				}
				$select = $this->commonPath($sel,'profile_pic');

				foreach ($select as $key => $value) {
					$particularlike=$this->db->query("SELECT * from tbl_postLike where userId='".$data['userid']."' and post_id='".$value->id."'")->row();
					if (!empty($particularlike)){
						$select[$key]->particularlike=$particularlike->status;
					}
					else{
						$select[$key]->particularlike=0;	
					}
				}




				return array_values($select);
			}elseif($data['type'] == 1){
				$posts = $this->db->query("SELECT tbl_post.*,tbl_users.fullname,tbl_users.profile_pic FROM tbl_post JOIN tbl_users ON tbl_users.id = tbl_post.userId ORDER BY date_created DESC")->result();
				foreach ($posts as $key => $value) {
					if(!empty($value->thumbnail)){
						$value->thumbnail = base_url().''.$value->thumbnail;
					}
					if($value->type == 0){
						if(!empty($value->image)){
							$value->image = base_url().''.$value->image;
						}
					}elseif($value->type == 1){
						if(!empty($value->map_image)){
							$value->map_image = base_url().''.$value->map_image;
						}
					}else{
						if(!empty($value->map_image)){
							$value->map_image = base_url().''.$value->map_image;
						}
						if(!empty($value->image)){
							$value->image = base_url().''.$value->image;
						}
					}
					$single = $this->db->query("SELECT tbl_follow.from_id as user_id FROM tbl_follow  WHERE status = 1 AND is_block = 0 AND (to_id = '".$data['userid']."' AND from_id = '".$value->userId."')")->row();
					if(empty($single)){
						unset($posts[$key]);
					}
					$like = $this->db->query("SELECT userId,status,profile_pic FROM tbl_postLike JOIN tbl_users On tbl_users.id = tbl_postLike.userId  WHERE post_id = '".$value->id."' AND status = 1 ")->result();
					$llk = $this->commonPath($like,'profile_pic');
					$value->like = $llk;
				}
				$poost = $this->commonPath($posts,'profile_pic');

				foreach ($poost as $key => $value) {
					$particularlike=$this->db->query("SELECT * from tbl_postLike where userId='".$data['userid']."' and post_id='".$value->id."'")->row();
					if (!empty($particularlike)){
						$value->particularlike=$particularlike->status;
					}
					else{
						$value->particularlike=0;	
					}
				}


				return array_values($poost);
			}else{
				return "error";
			}
		}

		public function pushdata($userid,$message,$action,$title,$challengeId=null){
			$selectRes = $this->db->select('*')->from('tbl_users')->where('id',$userid)->get()->row();
			// print_r($selectRes);die;
	        $selectPreviousUsers = $this->db->select('*')
	                                         ->from('tbl_login')
	                                         ->where('userId',$selectRes->id)
	                                         ->where('status',1)
	                                         ->limit(1,'DESC')
	                                         ->get()->row(); 
	                                         // print_r($selectPreviousUsers);die;
	        $pushData['message'] = $message;
	        $pushData['action'] = $action;
	        $pushData['title'] = $title;
	        $pushData['profile_pic'] = $selectRes->profile_pic;
	        $pushData['id'] = $challengeId;
	        $pushData['token'] = $selectPreviousUsers->token_id;
	        // echo"<pre>"; print_r($pushData);die;
	        //if($selectPreviousUsers->device_id == 1){
	            $this->iosPush($pushData);
	        // }else if($selectPreviousUsers->device_id == 0){
	        //     $this->androidPush($pushData);
	        // }
		}
		public function iosPush($pushData=null){
		    $deviceToken = $pushData['token'];
		    // print_r($deviceToken);die;
		    $passphrase = '@osvin1';
		    $ctx = stream_context_create();
		    stream_context_set_option($ctx, 'ssl', 'local_cert', './certs/apns-dev-cert.pem');
		    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		    // Open a connection to the APNS server
		    $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
		   // if (!$fp) exit("Failed to connect: $err $errstr" . PHP_EOL);
	        $body['aps'] = array(
				  'alert' => array(
			           'title' =>$pushData['title'],
			           'body' =>$pushData['message']
			       ),
			  "req_id"=>$pushData['id'],
	      	  "action" => $pushData['action'],
	      	  'profile_pic' => $pushData['profile_pic'],
              'sound' => 'default'
    		);
    		// print_r($body);die;
		    // Encode the payload as JSON
		    $payload = json_encode($body);
		    // Build the binary notification
		    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		    // Send it to the server
		    $result = fwrite($fp, $msg, strlen($msg)); 
		   // print_r($result);die;
		   // echo"there";
		    fclose($fp);
		}
		public function taguser($data){
			$explode = explode(',',$data['tag_id']);
			$select = $this->db->query("SELECT * FROM tbl_post WHERE id = '".$data['post_id']."'")->row();
			if(!empty($select)){
				foreach ($explode as $key => $value) {
					$sel = $this->db->query("SELECT * FROM tbl_postTag WHERE post_id = '".$data['post_id']."' AND tag_id = '".$value."'")->row();
					if(empty($sel)){
						$this->db->insert('tbl_postTag',array('post_id'=>$data['post_id'],'tag_id'=>$value,'date_created'=>date('Y-m-d H:i:s')));
						$this->db->insert('tbl_notification',array('type'=>4,'to_id'=>$value,'from_id'=>$select->userId,'date_created'=>date('Y-m-d H:i:s')));
					}
				}
				return "insert";
			}else{
				return "error";
			}
		}
    }
?>