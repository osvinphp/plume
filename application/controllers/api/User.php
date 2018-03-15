<?php 
    defined('BASEPATH') OR exit('No direct script access allowed');
    // This can be removed if you use __autoload() in config.php OR use Modular Extensions
    /** @noinspection PhpIncludeInspection */
    error_reporting(0);
    require APPPATH . '/libraries/REST_Controller.php';
    /**
     * This is an example of a few basic user interaction methods you could use
     * all done with a hardcoded array
     *
     * @package         CodeIgniter
     * @subpackage      Rest Server
     * @category        Controller
     * @author          Phil Sturgeon, Chris Kacerguis
     * @license         MIT
     * @link            https://github.com/chriskacerguis/codeigniter-restserver
     */
    class User extends REST_Controller {

        function __construct(){
            // Construct the parent class
            parent::__construct();
            $this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
            $this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key
            $this->load->model('Api_model');
            $this->load->helper('date');
            $this->load->helper(array('form','url'));
            $config = Array(
                'protocol' => 'sendmail',
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'wordwrap' => TRUE
            );
            
            $this->load->database();
            date_default_timezone_set('UTC');
        }

        public function signUp_post(){
            $data = [
                'fullname'=>$this->input->post('fullname'),
                'email'=>$this->input->post('email'),
                'password'=>md5($this->input->post('password')),
                'fb_id'=>$this->input->post('fb_id'),
                'age'=>$this->input->post('age'),
                'phone'=>$this->input->post('phone'),
                'device_id'=>$this->input->post('device_id'),
                'unique_deviceId'=>$this->input->post('unique_deviceId'),
                'token_id'=>$this->input->post('token_id')
            ];
            $var = $this->Api_model->signup($data);
            if($var['response'] == "insert"){
                $result = array(
                    "controller"=>"User",
                    "action"=>"signUp",
                    "ResponseCode" => true,
                    "MessageWhatHappen" => "Successfully Registered!",
                    "signUpResponse"=>$var['info']
                );
            }elseif($var == "email"){
                $result = array(
                    "controller"=>"User",
                    "action"=>"signUp",
                    "ResponseCode" => false,
                    "MessageWhatHappen" =>"Email already exist!"
                );
            }elseif($var == "name"){
                $result = array(
                    "controller"=>"User",
                    "action"=>"signUp",
                    "ResponseCode" => false,
                    "MessageWhatHappen" =>"Username already exist!"
                );
            }else{
                $result = array(
                    "controller"=>"User",
                    "action"=>"signUp",
                    "ResponseCode" => false,
                    "MessageWhatHappen" => "something went wrong!"
                );
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function login_post(){
            $data = [
                'fullname'=>$this->input->post('fullname'),
                'password'=>md5($this->input->post('password')),
                'fb_id'=>$this->input->post('fb_id'),
                'device_id'=>$this->input->post('device_id'),
                'unique_deviceId'=>$this->input->post('unique_deviceId'),
                'token_id'=>$this->input->post('token_id')
            ];
            $var = $this->Api_model->login($data);
            if($var['response'] == "login"){
                $result = array(
                    "controller"=>"login",
                    "action"=>"signUp",
                    "ResponseCode" => true,
                    "MessageWhatHappen" => "Login success!",
                    "loginResponse"=>$var['info']
                );
            }else{
                $result = array(
                    "controller"=>"login",
                    "action"=>"signUp",
                    "ResponseCode" => false,
                    "MessageWhatHappen" => "Username or password does not exist!"
                );
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function forgotpassword_post(){        
            $email = $this->input->post('email');
            $var = $this->Api_model->forgotpassword($email);
            if($var == "error"){
                $result = array(
                    "controller"=> "User",
                    "action"=> "forgotpassword",
                    "ResponseCode" => false,
                    "MessageWhatHappen"=> "Email does not exist in our database."
                );
            }else{
                $body = "<!DOCTYPE html>
                <head>
                <meta content=text/html; charset=utf-8 http-equiv=Content-Type />
                <title>Feedback</title>
                <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600' rel='stylesheet' type='text/css'>
                </head>
                <body>
                <table width=60% border=0 bgcolor=#53CBE6 style=margin:0 auto; float:none;font-family: 'Open Sans', sans-serif; padding:0 0 10px 0;>
                <tr>
                <th width=20px></th>
                <th width=20px  style=padding-top:30px;padding-bottom:30px;></th>
                <th width=20px></th>
                </tr>
                <tr>
                <td width=20px></td>
                <td bgcolor=#fff style=border-radius:10px;padding:20px;>
                <table width=100%;>
                <tr>
                <th style=font-size:20px; font-weight:bolder; text-align:right;padding-bottom:10px;border-bottom:solid 1px #ddd;> Hello " . $var['name'] . "</th>
                </tr>
                <tr>
                <td style=font-size:16px;>
                <p> You have requested a password retrieval for your user account at Plume.To complete the process, click the link below.</p>
                <p><a href=" . base_url('api/User/newpassword/' . $var['b_id']) . ">Change Password</a></p>
                </td>
                </tr>
              <tr>
                <td style=text-align:center; padding:20px;>
                <h2 style=margin-top:50px; font-size:29px;>Best Regards,</h2>
                <h3 style=margin:0; font-weight:100;>Customer Support</h3>
                <h3 style=margin:0; font-weight:100;></h3>
                </td>
                </tr>
                </table>
                </td>
                <td width=20px></td>
                </tr>
                <tr>
                <td width=20px></td>
                <td style=text-align:center; color:#fff; padding:10px;> Copyright © Plume All Rights Reserved</td>
                <td width=20px></td>
                </tr>
                </table>
                </body>";
                $this->load->library('email');
                $this->email->set_newline("\r\n");
                $this->email->from('osvinphp@gmail.com', 'Plume');
                $this->email->to($email);
                $this->email->subject('Forgot Password');
                $this->email->message($body);
                $this->email->send();

                $result = array(
                    "controller"=> "User",
                    "action"=> "forgotpassword",
                    "ResponseCode" => true,
                    "MessageWhatHappen"=> "Mail sent successfully."
                );
            }
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        public function newpassword_get($userid=null){
            if ($userid!="") {
                $id = base64_decode($userid);
            }else{
                $id = base64_decode($this->get('id'));
            }
            $id1 = explode("_",$id);
            $id2 = $id1[0];
            $data['user_id'] = $id2;
            $data['title'] = "new Password";
            $this->load->library('session');
            // $this->load->view('templete/header');
            $this->load->view('template/newpassword', $data);
        }
        public function updatepassword_post(){
            $this->load->library('session');
            $uid = $this->input->post('id');
            $static_key = "afvsdsdjkldfoiuy4uiskahkhsajbjksasdasdgf43gdsddsf";
            $id = $uid . "_" . $static_key;
            $id = base64_encode($id);
            $data = ['id' => $this->input->post('id') , 'password' => $this->input->post('password') ,'cpassword' => $this->input->post('cpassword'), 'base64id' => $id];
            if($data['password'] != $data['cpassword']){
                $this->session->set_flashdata('msg', '<span style="color:#f00">Please enter same password</span>');
                redirect("api/User/newpassword?id=" . $data['base64id']);
            }elseif(empty($data['password'])){
                $this->session->set_flashdata('msg', '<span style="color:#f00">Please enter password</span>');
                redirect("api/User/newpassword?id=" . $data['base64id']);
            }else{
                $var = $this->Api_model->update_data(array('id'=>$data['id']),'tbl_users',array('password'=>md5($data['password'])));
                $this->session->set_flashdata('msg', '<span style="color:green;">Password updated successfully</span>');
                redirect("api/User/newpassword?id=" . $data['base64id']);
            }
            $this->load->view('templete/header');
            $this->load->view('templete/successmsg');
        }
        public function editProfile_post(){
            $upload = $this->upload('public/profile_pic','IMAGE','profile_pic');
            $data = [
                'id'=>$this->input->post('userid'),
                'fullname'=>$this->input->post('fullname'),
                'email'=>$this->input->post('email'),
                'bio'=>$this->input->post('bio'),
                'post_type'=>$this->input->post('posttype'),
                'pic_type'=>$this->input->post('pictype')
            ];
            if(!empty($upload)){
                $data['profile_pic'] = $upload;
            }
            $filt = array_filter($data);
            $var = $this->Api_model->update_data(array('id'=>$data['id']),'tbl_users',$filt);
            $selct = $this->Api_model->select_data('*','tbl_users',array('id'=>$data['id']));
            $v1 = $this->Api_model->commonPath($selct,'profile_pic');
            $result = array(
                "controller"=> "User",
                "action"=> "editProfile",
                "ResponseCode" => true,
                "MessageWhatHappen"=> "Profile update successfully.",
                "editProfileResponse"=>$v1[0]
            ); 
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function upload($path,$name,$imagename){
        	$config['upload_path'] = $path;
        	$config['allowed_types'] = '*';
        	$config['max_size'] = '50000';
        	$config['max_width'] = '5024';
        	$config['max_height'] = '5068';
        	$new_name = $name.'_'.time();
        	$config['file_name'] = $new_name;
	        $this->load->library('upload', $config);
	        if(!$this->upload->do_upload($imagename)){
	            $error = array(
	                'error' => $this->upload->display_errors()
	            );
	            $image = "";
	        }else{
                $dataS = $this->upload->data();
	            $image = $path.'/'.$dataS['file_name'];
	        }
	        return $image;
        }
        public function postData_post(){
        	$data2 = [
        		'userId'=>$this->input->post('userid'),
        		'type'=>$this->input->post('type'),
        		'caption'=>$this->input->post('caption'),
        		'lat'=>$this->input->post('lat'),
        		'lng'=>$this->input->post('lng'),
                'duration'=>$this->input->post('duration'),
                'address'=>$this->input->post('address'),
                'media_type'=>$this->input->post('media_type'),
                'height'=>$this->input->post('height'),
                'width'=>$this->input->post('width'),
        		'date_created'=>date('Y-m-d H:i:s')
        	];
            if($data2['type'] == 0){
                $upload_data = $this->upload('public/image','IMG','profile_pic');
                $data2['image'] = $upload_data;
            }elseif($data2['type'] == 1){
                $upload_data = $this->upload('public/map_image','MAP','profile_pic');
                $data2['map_image'] = $upload_data;
            }elseif($data2['type'] == 2){
                $upload_data = $this->upload('public/image','IMG','profile_pic');
                $upload_data1 = $this->upload('public/image','IMG','profile_pic');
                $data2['image'] = $upload_data;
                $data2['map_image'] = $upload_data1;
            }
            $upload_data = $this->upload('public/image','IMG','thumbnail');
            $data2['thumbnail'] = $upload_data;

            $filter = array_filter($data2);
            $var[] = $this->Api_model->postdata($filter,$data2);
            // print_r($var);die;
            if($var == "error"){
	        	$result = array(
	                "controller"=> "User",
	                "action"=> "postData",
	                "ResponseCode" => false,
	                "MessageWhatHappen"=> "something went wrong."
	            );
	        }else{
	        	$result = array(
	                "controller"=> "User",
	                "action"=> "postData",
	                "ResponseCode" => true,
	                //"MessageWhatHappen"=> "Mail sent successfully."
	                "postResponse"=>$var
	            );
	        }
        	$this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function logout_post(){
        	$userid = $this->input->post('userid');
        	$var = $this->Api_model->logout($userid);
        	if($var == "update"){
	        	$result = array(
	                "controller" => "User",
	                "action" => "logout",
	                "ResponseCode" => true,
	                "MessageWhatHappen" => "Logged out successfully."
	            );
	        }else{
	        	$result = array(
	                "controller" => "User",
	                "action" => "logout",
	                "ResponseCode" => false,
	                "MessageWhatHappen" => "something went wrong."
	            );
	        }
        	$this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function notification_post(){
            $userid = $this->input->post('userid');
            $var = $this->db->query("SELECT tbl_notification.*,tbl_users.fullname,tbl_users.profile_pic from tbl_notification JOIN tbl_users ON tbl_users.id = tbl_notification.to_id  join tbl_post on tbl_post.id=tbl_notification.post_id where from_id = '".$userid."' ORDER BY date_created DESC")->result();

            foreach ($var as $key => $value) {
                if($value->type == 3){
                    $value->status = 0;
                }
               if($value->to_id == $value->from_id){
                unset($var[$key]);
               }
            }
            $data = $this->Api_model->commonPath($var,'profile_pic');
            $result = array(
                "controller" => "User",
                "action" => "notification",
                "ResponseCode" => true,
                "notificationResponse" => array_values($data)
            );
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function follow_post(){
        	$data = [
        		'to_id'=>$this->input->post('friendid'),
        		'from_id'=>$this->input->post('userid'),
        		'status'=>$this->input->post('status'),  ///// 1 ->follow , 0 -> unfollow
        		'date_created'=>date('Y-m-d H:i:s')
        	];
        	$var = $this->Api_model->follow($data);
            if($var == "error"){
                $result = array(
                    "controller" => "User",
                    "action" => "follow",
                    "ResponseCode" => false,
                    "MessageWhatHappen" => "something went wrong."
                );
            }elseif($var['mmsg'] == "follow"){
        		$result = array(
	                "controller" => "User",
	                "action" => "follow",
	                "ResponseCode" => true,
	                "MessageWhatHappen" => "Follow successfully.",
                    "followResponse"=>$var['response']
	            );
        	}else{
        		$result = array(
	                "controller" => "User",
	                "action" => "follow",
	                "ResponseCode" => true,
	                "MessageWhatHappen" => "Unfollow successfully.",
                    "followResponse"=>$var['response']
	            );
        	}
        	$this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function mapTrendingPost_post(){
            $data = [
                'userid'=>$this->input->post('userid'),
                'lat'=>$this->input->post('lat'),
                'lng'=>$this->input->post('lng'),
                'type'=>$this->input->post('type'),
                'address'=>$this->input->post('address')
            ];
            $var = $this->Api_model->mapdata($data);
            $result = array(
                "controller" => "User",
                "action" => "mapTrendingPost",
                "ResponseCode" => true,
                "loginStatus"=>$var['loginStatus'],
                "mapTrendingResponse" => $var['response']
            );
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function likePost_post(){
            $data = [
                'id'=>$this->input->post('postid'),
                'userId'=>$this->input->post('userid'),
                'status'=>$this->input->post('status')
            ];
            $var = $this->Api_model->likepost($data);
            if($var['msg'] == "like"){
                $result = array(
                    "controller" => "User",
                    "action" => "likePost",
                    "ResponseCode" => true,
                    "MessageWhatHappen" => "Like.",
                    "likeResponse"=>$var['response']
                );
            }else{
                $result = array(
                    "controller" => "User",
                    "action" => "likePost",
                    "ResponseCode" => true,
                    "MessageWhatHappen" => "Unlike.",
                    "likeResponse"=>$var['response']
                );
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function deletePost_post(){
            $id = $this->input->post('postid');
            $type = $this->input->post('type'); /// 0 -> profile , 1->map 
            $var = $this->Api_model->deletepost($id,$type);
            $result = array(
                "controller" => "User",
                "action" => "deletePost",
                "ResponseCode" => true,
                "MessageWhatHappen" => "Successfully deleted."
            );
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function profileList_post(){
            $userid = $this->input->post('userid');
            $var = $this->Api_model->profilelist($userid);
            $result = array(
                "controller" => "User",
                "action" => "profileList",
                "ResponseCode" => true,
                "profileListResponse"=>$var
            );
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function profile_post(){
            $id = $this->input->post('userid');
            $var = $this->Api_model->profile($id);
            $result = array(
                "controller" => "User",
                "action" => "profile",
                "ResponseCode" => true,
                "profileResponse"=> $var
            );
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function profileDetail_post(){
            $id = $this->input->post('postid');
            $userid = $this->input->post('userid');
            $var = $this->Api_model->profiledetail($id,$userid);
            if($var == "error"){
                $result = array(
                    "controller" => "User",
                    "action" => "profileDetail",
                    "ResponseCode" => false,
                    "MessageWhatHappen" => "something went wrong."
                );
            }else{
                $result = array(
                    "controller" => "User",
                    "action" => "profileDetail",
                    "ResponseCode" => true,
                    "profileDetResponse"=> $var[0]
                );
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        } 
        public function postComment_post(){
            $data = [
                'post_id'=>$this->input->post('postid'),
                'userId'=>$this->input->post('userid'),
                'comment'=>$this->input->post('comment'),
                'date_created'=>date('Y-m-d H:i:s')
            ];
            $var = $this->Api_model->postcomment($data);
            if($var == "error"){
                $result = array(
                    "controller" => "User",
                    "action" => "postComment",
                    "ResponseCode" => false,
                    "MessageWhatHappen" => "something went wrong."
                );
            }else{
                $result = array(
                    "controller" => "User",
                    "action" => "postComment",
                    "ResponseCode" => true,
                    "commentResponse"=>$var
                );
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function changePassword_post(){
            $data = [
                'id'=>$this->input->post('userid'),
                'oldpass'=>md5($this->input->post('oldpass')),
                'password'=>md5($this->input->post('newpass'))
            ];
            $var = $this->Api_model->select_data('*','tbl_users',array('id'=>$data['id'],'password'=>$data['oldpass']));
            if(!empty($var)){
                $this->Api_model->update_data(array('id'=>$data['id'],'password'=>$data['oldpass']),'tbl_users',array('password'=>$data['password']));
                $result = array(
                    "controller" => "User",
                    "action" => "changePassword",
                    "ResponseCode" => true,
                    "MessageWhatHappen" => "Password changed successfully."
                );
            }else{
                $result = array(
                    "controller" => "User",
                    "action" => "changePassword",
                    "ResponseCode" => false,
                    "MessageWhatHappen" => "Incorrect old password"
                );
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function userProfile_post(){
            $userid = $this->input->post('userid');
            $var = $this->Api_model->select_data('id,fullname,email,profile_pic,bio,phone,','tbl_users',array('id'=>$userid));
            $v1 = $this->Api_model->commonPath($var,'profile_pic');
            $result = array(
                "controller" => "User",
                "action" => "userProfile",
                "ResponseCode" => true,
                "userProfileResponse"=>$v1[0]
            );
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function setProfilePrivate_post(){
            $id = $this->input->post('userid');
            $sel = $this->Api_model->select_data('*','tbl_users',array('id'=>$id));
            if($sel[0]->post_type == 0){
                $var = $this->Api_model->update_data(array('id'=>$id),'tbl_users',array('post_type'=>1));
            }else{
                $var = $this->Api_model->update_data(array('id'=>$id),'tbl_users',array('post_type'=>0));
            }
            $select = $this->Api_model->select_data('post_type','tbl_users',array('id'=>$id));
            $result = array(
                "controller" => "User",
                "action" => "setProfilePrivate",
                "ResponseCode" => true,
                "setPrivateResponse"=>$select[0]
            );
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function blockUser_post(){
            $data = [
                'to_id'=>$this->input->post('friendid'),
                'from_id'=>$this->input->post('userid'),
                'is_block'=>$this->input->post('block')
            ];
            $var = $this->Api_model->blockuser($data);
            if($data['is_block'] == 1){
                $msg ="Blocked Successfully.";
            }else{
                $msg = "Unblocked Successfully";
            }
            if($var == "error"){
                 $result = array(
                    "controller" => "User",
                    "action" => "blockUser",
                    "ResponseCode" => false,
                    "MessageWhatHappen"=>"something went wrong"
                );  
            }else{
                $result = array(
                    "controller" => "User",
                    "action" => "blockUser",
                    "ResponseCode" => true,
                    "blockUserResponse"=>$msg
                );  
            }
            
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function blockList_post(){
            $id = $this->input->post('userid');
            $var = $this->Api_model->blocklist($id);
            $result = array(
                "controller" => "User",
                "action" => "blockList",
                "ResponseCode" => true,
                "blockListResponse"=>$var
            );  
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function searchPostProfile_post(){
            $data = array(
                'id'=>$this->input->post('userid')
            );
            $var = $this->Api_model->searchfriend($data);
            $result = array(
                "controller" => "User",
                "action" => "searchFriend",
                "ResponseCode" => true,
                "searchResponse"=>$var
            );   
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        public function followersList_post(){
            $data = [
                'userid'=>$this->input->post('userid'),
                'type'=>$this->input->post('type'),  //// 1 ->followers , 2-> following
                'tab_type'=>$this->input->post('tab_type') //// 0 -> my community , 1-> frndsprofile
            ];
            $var = $this->Api_model->mycommunity($data);
            if($var == "error"){
                $result = array(
                    "controller" => "User",
                    "action" => "followersList",
                    "ResponseCode" => false,
                    "MessageWhatHappen"=>"something went wrong"
                );
            }else{
                $result = array(
                    "controller" => "User",
                    "action" => "followersList",
                    "ResponseCode" => true,
                    "followResponse"=>$var
                );  
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function trendFollowListing_post(){
            $data = [
                'userid'=>$this->input->post('userid'),
                'type'=>$this->input->post('type'),   /// 0 ->trend , 1->follo
                'comment'=>$this->input->post('comment'),
                'offset'=>$this->input->post('offset')
            ];
            $var = $this->Api_model->trendfollowlisting($data);
            $count=count($var);
            $var = array_slice( $var, $data['offset'], 10 ); 
            if($var == "error"){
                $result = array(
                    "controller" => "User",
                    "action" => "trendFollowListing",
                    "ResponseCode" => false,
                    "MessageWhatHappen"=>"something went wrong"
                );
            }else{
                $result = array(
                    "controller" => "User",
                    "action" => "trendFollowListing",
                    "ResponseCode" => true,
                    "trendFollowResponse"=>$var,
                    "count"=>$count
                );  
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
        public function tagUsers_post(){
            $data = [
                'post_id'=>$this->input->post('postid'),
                'tag_id'=>$this->input->post('tagid')
            ];
            $var = $this->Api_model->taguser($data);
            if($var == "error"){
                $result = array(
                    "controller" => "User",
                    "action" => "tagUsers",
                    "ResponseCode" => false,
                    "trendFollowResponse"=>"something went wrong."
                );
            }else{
                $result = array(
                    "controller" => "User",
                    "action" => "tagUsers",
                    "ResponseCode" => true,
                    "trendFollowResponse"=>"Successfully tagged."
                );
            }
            $this->set_response($result,REST_Controller::HTTP_OK);
        }
    }
?>