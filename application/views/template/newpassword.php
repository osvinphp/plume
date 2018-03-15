<style type="text/css">
/*.LOGIn_PENAL_WRAPreset {
    height: 100%;
    left: 0;
    position: absolute;
    right: 0;
    background: #33aeb7;
}
	.RESET_password{ background: #000; border: 1px solid #000;}
	.RESET_password:hover{ background: #848484; border: 1px solid #848484;}
h2.sign_up_title {
    color: #fff;
    font-size: 22px;
    text-align: left;
    text-transform: uppercase;
}
	.miDDLE_Cust {
    margin-top: 37%;
    width: 100%;
}*/
</style>



<!-- <h3><?php// echo $title ; ?></h3> -->
<!-- <form action="http://phphosting.osvin.net/sunSoft/api/User/updatepassword" method="POST">
	<input type="password" name="password" value="" placeholder="New Password">
	<br>
	<input type="password" name="cpassword" value = ""  placeholder="Confirm Password">
	<br>
	<input type="hidden" name="id" value="<?php echo $user_id; ?>">
	<br>
	<input type="submit" value="submit">
</form> -->

<!-- <section class="LOGIn_PENAL_WRAPreset">
<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-4 col-sm-offset-3 col-md-offset-4"> -->
    		<form class="miDDLE_Cust" action="<?php echo base_url(); ?>api/User/updatepassword" method="POST">

				<?php 
					if($this->session->flashdata('msg')){ 
				 		echo "<h4>".$this->session->flashdata('msg')."</h4>";
					} 
				?>

    			<h2 class="sign_up_title">Reset password</h2>
    			<!-- <p>Enter your email address we will sent you the password reset link.</p> -->

    			<div class="form-group">
					<input class="form-control input-md" type="password" name="password" value="" placeholder="New Password">
    			</div>

    			<div class="form-group">
					<input class="form-control input-md" type="password" name="cpassword" value = ""  placeholder="Confirm Password">
					<input type="hidden" name="id" value="<?php echo $user_id; ?>">
    			</div>

    			<div class="form-group">
					<input class="RESET_password btn btn-success btn-block btn-lg" type="submit" value="Submit">
    			</div>
    		</form>
    	<!-- </div>
    </div>
</div>
</section>
 -->














