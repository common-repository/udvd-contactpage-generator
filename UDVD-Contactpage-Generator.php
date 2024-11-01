<?php
/*
Plugin Name: UDVD-Contactpage-Generator
Author: Raja
Version: 1.2
Plugin URI: http://blog.youdreamwedevelop.com/udvd-contactpage-generator/
Author URI:http://blog.youdreamwedevelop.com/raja/

Description:★Create a nice Responsive ContactUs page with a click★HTML5+CSS3 Powered ★Stylish Email and Required field validation★Easy to configure ★Multiple contact forms with shortcode support. =>As soon as the plugin gets activated ContactUs page gets generated with an inbuilt shortcode [udvdcontactpage]. Thanks for your support ! 
License: GPLv2 or later
*/


/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/



// Loading CSS inside the plugin folder
add_action('get_header', 'udvd_css');
function udvd_css() {
if (!is_admin()) {
wp_enqueue_style('my-plugin-css-script', plugins_url('/udvd-contactpage-generator/css/style.css'));
}
}


register_activation_hook(__FILE__,"create_contact_page");
register_activation_hook(__FILE__,"set_contact_form_options");
register_deactivation_hook(__FILE__,"unset_contact_form_options");

add_action('admin_menu', 'contact_form_menu');

/*Add short code*/
add_shortcode( 'udvdcontactpage', 'contact_form_function' );

function contact_form_menu() {
	add_options_page('UDVD Contactpage Options', 'UDVD Contactpage', 8, 'udvd-contact-form-options', 'udvd_contact_form_options_page');
}

function set_contact_form_options() {
	global $user_email,$redirect_url,$op_text;
	$udvd_contact_form_options = array('email_address' => $user_email,'redir_url' => $redirect_url,'output_text'=>$op_text);
	add_option('udvd_contact_form_options',$udvd_contact_form_options);	
}

function unset_contact_form_options() {
	delete_option('udvd_contact_form_options');
}

function udvd_contact_form_options_page() {
	$udvd_contact_form_options = get_option('udvd_contact_form_options');

	if (isset($_POST['udvd_contact_form_settings']))
        {
		$udvd_contact_form_options['email_address'] = isset($_POST['email_address']) ? $_POST['email_address'] : $udvd_contact_form_options['email_address'] ;
         $udvd_contact_form_options['redir_url'] = isset($_POST['redir_url']) ? $_POST['redir_url'] : $udvd_contact_form_options['redir_url'] ;
		 $udvd_contact_form_options['output_text'] = isset($_POST['output_text']) ? $_POST['output_text'] : $udvd_contact_form_options['output_text'] ;
		 
		 
                update_option('udvd_contact_form_options', $udvd_contact_form_options);
?>
                <p><strong>"Your Settings has been Updated"</strong></p>
<?php
        }
?>


        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<h2>UDVD Contactpage Settings</h2>
	<p>Set email address where you want contact form entries to be mailed (Default:Admin email). 
        <p>1.Email Address : 
	<input style="width:200px;" name="email_address" type="text" value="<?php echo $udvd_contact_form_options['email_address']; ?>" />
        <br/>
	<p>Enter the url your want to redirect after submission(Dont forget to add http://) : 
        <p>2.Redirect  URL (Recommended):<br/>
	<input style="width:200px;" name="redir_url" type="text" value="<?php echo $udvd_contact_form_options['redir_url']; ?>" />
        <br/><br/>
	3.Email success text: <br/><br/>
	<textarea name="output_text" rows="5" cols="60" ><?php echo $udvd_contact_form_options['output_text']; ?> </textarea> <br /><br/>
		

      <div class="submit">
		<input type="submit" name="udvd_contact_form_settings" value="<?php echo 'Save Changes'; ?>" />
        </div>
        <hr />
	</form>
	
<?php
}

/*Add contact form*/
function contact_form_function( $atts ){

	if (isset($_POST['udvd_contact_form_data']))
{
		
		
			/*everything ok.. lets process */
			$name = $_POST['contact_name'];
			$email_id = $_POST['contact_email'];
			$subject = $_POST['contact_subject'];
			$desc = $_POST['contact_desc'];
			$retmsg = process_contact_form($name,$email_id,$subject,$desc);
		    

		$name = $_POST['contact_name'];
		$email_id = $_POST['contact_email'];
		$subject = $_POST['contact_subject'];
		$desc = $_POST['contact_desc'];

	}
	else {
		$name = "";
		$email_id = "";
		$subject = "";
		$desc = "";
	}

?>
    you can reach us using the form below <br/><br/>

        <form method="post" class="contact_form" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

<span class="required_notification">* Denotes Required Field</span> <br/><br/>
        <p>Name : <br />
	<input style="width:250px;" name="contact_name" type="text" value="<?php echo $name; ?>"placeholder="Your Name" required /> <br />
        <p>Email Address : <br />
	<input style="width:250px;" name="contact_email"  type="email" value="<?php echo $email_id; ?>" placeholder="john_doe@example.com" required  /><br />
	
        <p>Subject : <br />
	<input style="width:250px;" name="contact_subject" type="text" value="<?php echo $subject; ?>"placeholder="Your Subject" required /><br />
        <p>Description : <br />
	<textarea name="contact_desc" rows="8" cols="50" ><?php echo $desc; ?> </textarea> <br />
<!--	<?php wp_nonce_field('ecfa261455','ecfnf'); ?> -->

      <div class="submit">
		
		<button class="submit" type="submit" name="udvd_contact_form_data" value="<?php echo 'Send'; ?>" >Submit Form</button>
        </div>
        <hr />
	</form>

<?php
}

/*Create contact form*/
function create_contact_page ()
{
	global $user_ID;

	$page['post_type']    = 'page';
	$page['post_content'] = '[udvdcontactpage]';
	$page['post_parent']  = 0;
	$page['post_author']  = $user_ID;
	$page['post_status']  = 'publish';
	$page['post_title']   = 'Contact Us';
	$pageid = wp_insert_post ($page);
	if ($pageid == 0) { /* Page Add Failed */ }
}

/*Delete page*/

/*Process Form*/
function process_contact_form ($name,$email_id,$subject,$desc)
{
//	echo "processing";
//	$msg = 'Name - ' . $name . '\n';
//	$msg = 'Message --' . '\n';

	$udvd_contact_form_options = get_option('udvd_contact_form_options');
	$to = $udvd_contact_form_options['email_address'];

    $thanks= $udvd_contact_form_options['output_text'];  
	$subject = $subject;
	$message = $desc;
	$headers = 'From: ' . $email_id . "\r\n" . 'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $message, $headers);


ob_start("ob_udvd"); 
$redirect = $udvd_contact_form_options['redir_url'];
	
if
($redirect!= NULL)
 {
echo  header("Location: $redirect");
	
}
else
{
 echo $thanks;
}
exit;
ob_flush();
}
?>