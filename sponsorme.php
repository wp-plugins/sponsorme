<?php
/*
Plugin Name: Sponsor Me
Plugin URI: http://www.u-g-h.com/index.php/wordpress-plugins/wordpress-plugin-sponsorme/
Description: Plugin to run a sponsorship campaign that lets friends and family contribute to a target amount.
Version: 0.5.2
Author: Owen Cutajar
Author URI: http://www.u-g-h.com 
*/

/* History:
  v0.1 - OwenC - Created base version
  v0.2 - OwenC - Prepared for public release
  v0.3 - OwenC - Added external styling ability (and added a style) and other cosmetic fixes
  v0.4 - OwenC - Added ability to accept non-PayPal pledges
  v0.5 - OwenC - Integrated non-paypal pledges to front-end
  v0.5.1 - OwenC - Added some validation to inputs and currency formatting
  v0.5.2 - OwenC - Fixed issue with $ currency breaking display - also issue with form style having too common a name
  
  Note: Thanks to Gene for for all your feedback (and text version of widget)
*/

// cater for stand-alone calls
if (!function_exists('get_option'))
	require_once('../../../wp-config.php');

// Consts
define('SM_PLUGIN_EXTERNAL_PATH', '/wp-content/plugins/sponsorme/');
define('SM_PLUGIN_NAME', 'sponsorme.php');
define('SM_PLUGIN_PATH', 'sponsorme/sponsorme.php');

$sponsorme_db_version = "1.1";

if (strstr($_SERVER['PHP_SELF'],SM_PLUGIN_EXTERNAL_PATH.SM_PLUGIN_NAME) && isset($_GET['graph'])) :

   global $wpdb;
   $table_name = $wpdb->prefix . "sponsorme";
   $strSQL = "SELECT SUM(amount) FROM $table_name WHERE verified <> 'N'";
   $thistotal = $wpdb->get_var($strSQL); 

   $options = get_option('SponsorMe');
   $title = $options['title'];
   $targetdesc = $options['targetdesc'];
   $targetamount = $options['targetamount'];
   $currency = $options['currency'];
   $paypal = $options['paypal'];
   $pageID = $options['pageID'];
   $backcol = $options['backcol'];
   $barscol = $options['barscol'];

   include('postgraph.class.php'); 

   $data = array(1 => $thistotal,$targetamount);

   if (isset($_GET['sidebar'])) {
      $graph = new PostGraph(150,200);
   } else {
      $graph = new PostGraph(300,400);
   }
   $graph->setGraphTitles($targetdesc, 'Donated vs Needed', 'Cash');
   $graph->setYNumberFormat('integer');
   $graph->setYTicks(10);
   $graph->setData($data);
   if ($backcol != "") {
      $graph->setBackgroundColor(html2rgb($backcol));
   } else {
      $graph->setBackgroundColor(array(255,255,0));
   }
   
   if ($barscol != "") {
      $graph->setBarsColor(html2rgb($barscol));
   }
      
   $graph->setTextColor(array(144,144,144));
   $graph->setXTextOrientation('horizontal');

   // prepare image
   $graph->drawImage();
   $graph->printImage();

   exit;
endif;

function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

function widget_SponsorMe_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_SponsorMe($args) {

		extract($args);
    $options = get_option('SponsorMe');
   	$title = $options['widget_title'];

		echo $before_widget . $before_title . $title . $after_title;
		docommon_SponsorMe_sidebar();
		echo $after_widget;
	}
	
	function widget_SponsorMe_control() {
				
   $options = get_option('SponsorMe');
   
 		if ( $_POST['sponsorme-submit'] ) {               
            // Change sidebar title
			$options['widget_title'] = strip_tags(stripslashes($_POST['sponsorme-widget_title']));
			update_option('SponsorMe', $options);
    }
   	$widget_title = htmlspecialchars($options['widget_title'], ENT_QUOTES);
   	echo '<p style="text-align:right;"><label for="sponsorme-widget_title">' . __('Widget Title:') . ' <input style="width: 200px;" id="sponsorme-widget_title" name="sponsorme-widget_title" type="text" value="'.$widget_title.'" /></label></p>';
		echo 'Please configure the other settings for the widget from the SponsorMe Configuration Screen';
		echo '<input type="hidden" id="sponsorme-submit" name="sponsorme-submit" value="1" />';
	}

	register_sidebar_widget(array('SponsorMe', 'widgets'), 'widget_SponsorMe');
	register_widget_control(array('SponsorMe', 'widgets'), 'widget_SponsorMe_control', 300, 130);
;
}

function SponsorMe_sidebar(){

   docommon_SponsorMe_sidebar();
}

function docommon_SponsorMe_sidebar(){

   global $wpdb;

   $options = get_option('SponsorMe');
   $title = $options['title'];
   $targetdesc = $options['targetdesc'];
   $targetamount = $options['targetamount'];
   $currency = $options['currency'];
   $paypal = $options['paypal'];
   $pageID = $options['pageID'];
   $textwidget = $options['textwidget'];

   echo '<div align="center">';
   if ($textwidget == "Yes") {
   
      global $wpdb;
      $table_name = $wpdb->prefix . "sponsorme";
      $strSQL = "SELECT SUM(amount) FROM $table_name WHERE verified <> 'N'";
      $thistotal = $wpdb->get_var($strSQL); 
   
      echo "<p><b>Please Donate to<br />" . $targetdesc . "<br /><br />Target amount: " . $currency . number_format($targetamount, 2, '.', ',') . "<br />Total Donations: " . $currency . number_format($thistotal, 2, '.', ',') . "<br />Amount Needed: " . $currency . number_format(($targetamount-$thistotal), 2, '.', ',') . "<br /><br />Thank you for your support!</b></p>";
   
   } else {
      echo '<img src="'.get_bloginfo('wpurl') . SM_PLUGIN_EXTERNAL_PATH . SM_PLUGIN_NAME .'?graph&sidebar">';
   }
   echo '<a href="'.get_permalink($pageID).'">Click to Donate</a></div>';
}


//To replace the <!--SponsorMe-page--> with the blogroll links
function SponsorMe_text($text) {
	global $wpdb;
    $table_name = $wpdb->prefix . "sponsorme";

	//Only perform plugin functionality if post/page text has <!--SponsorMe-->
	if (preg_match("|<!--SponsorMe-page-->|", $text)) {

        // Stuff goes here
        $SponsorMeDisplay = "";

        $options = get_option('SponsorMe');
        $title = $options['title'];
		$targetdesc = $options['targetdesc'];
	    $targetamount = $options['targetamount'];
	    $currency = $options['currency'];
	    $paypal = $options['paypal'];

		// "Gemigene $ bug" - If "$" is used as a currency, it needs to be escaped otherwise pre_replace does a funny
		if ($currency=="$") {$currency="\\$";}
		
        // Process if needed
        if ($_POST["sponsorme_process"] == "yes") {
           // Update database

           $name = strip_tags(stripslashes($_POST['sponsorme_name']));
           $email = strip_tags(stripslashes($_POST['sponsorme_email']));
           $url = strip_tags(stripslashes($_POST['sponsorme_URL']));
           $amount = strip_tags(stripslashes($_POST['sponsorme_amount']));
           $comments = htmlspecialchars(strip_tags(stripslashes($_POST['sponsorme_comments'])), ENT_QUOTES);
           $method = strip_tags(stripslashes($_POST['sponsorme_method']));
           
           // Validate that we have a name and and amount
           $result = "";
           
           	if (!is_numeric($amount)):          // amount not numeric
           		$result = 'Please specify an amount';
            elseif (trim($name == '')):        // pledger name not specified
              $result = 'Please specify your name';
            endif;

           if ($result == "") {
              if ($method == "cheque") { $needcontact = "Y"; } else { $needcontact = "N"; }

              $sql = 'INSERT INTO `'.$table_name.'` (`id`, `name`, `email`, `URL`, `amount`, `comments` ,`verified`, `needcontact`) VALUES (NULL, \''.$name.'\', \''.$email.'\', \''.$url.'\','.$amount.', \''.$comments.'\', \'N\', \''.$needcontact.'\');';
              $wpdb->query($sql);

              if ($method == "paypal") {
               $SponsorMeDisplay .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="myform">';
               $SponsorMeDisplay .= '<input type="hidden" name="cmd" value="_xclick">';
               $SponsorMeDisplay .= '<input type="hidden" name="business" value="'.$paypal.'">';
               $SponsorMeDisplay .= '<input type="hidden" name="item_name" value="Sponsor Me">';
               $SponsorMeDisplay .= '<input type="hidden" name="amount" value="'.$amount.'">';
               $SponsorMeDisplay .= '<input type="hidden" name="shipping" value="0.00">';
               $SponsorMeDisplay .= '<input type="hidden" name="no_shipping" value="0">';
               $SponsorMeDisplay .= '<input type="hidden" name="no_note" value="1">';
               $SponsorMeDisplay .= '<input type="hidden" name="currency_code" value="'.$currency.'">';
               $SponsorMeDisplay .= '<input type="hidden" name="lc" value="GB">';
               $SponsorMeDisplay .= '<input type="hidden" name="bn" value="PP-BuyNowBF">';
               $SponsorMeDisplay .= '<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but02.gif" border="0" name="submit">';
               $SponsorMeDisplay .= '<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">';
               $SponsorMeDisplay .= '</form>';
               $SponsorMeDisplay .= '<SCRIPT language="JavaScript">document.myform.submit();</SCRIPT>';
              } else {
               $SponsorMeDisplay .= '<div align="center"><font size="+1">Thank you for your pledge. You will be contacted shortly with details about where to send the cheque. Thank you once again for your generosity.</font></div>';           
             }
           } else {
             $SponsorMeDisplay .= '<div align="center"><font size="+1" color="red">An error have occurred: '.$result.'</font></div>';                   
           } 
        }
        $SponsorMeDisplay .= '<div align="center"><img src="'.get_bloginfo('wpurl') . SM_PLUGIN_EXTERNAL_PATH . SM_PLUGIN_NAME .'?graph"><br>Powered by <a href="http://www.u-g-h.com/index.php/wordpress-plugins/wordpress-plugin-sponsorme/">SponsorMe Plugin</a></div>';

        $SponsorMeDisplay .= '<hr>';


        $SponsorMeDisplay .= '<p>If you\'d like to help, you can fill in the form below to leave a donation.</p>';
        $SponsorMeDisplay .= '<form class="sponsorme_form" name="sponsorme" method="POST">';
        $SponsorMeDisplay .= '<fieldset>';
        $SponsorMeDisplay .= '<legend>Pledge Details</legend>';
        $SponsorMeDisplay .= '<table><tr><td><label for "sponsorme_name">Name:</label></td><td><input name="sponsorme_name" id="sponsorme_name" value="'.$name.'"/></td></tr>';
        $SponsorMeDisplay .= '<tr><td><label for "sponsorme_email">Email</label></td><td><input name="sponsorme_email" id="sponsorme_email" value="'.$email.'"/></td></tr>';
        $SponsorMeDisplay .= '<tr><td><label for "sponsorme_URL">URL</label></td><td><input name="sponsorme_URL" id="sponsorme_URL" value="'.$url.'"/></td></tr>';
        $SponsorMeDisplay .= '<tr><td><label for "sponsorme_amount">Amount ('.$currency.')</label></td><td><input name="sponsorme_amount" id="sponsorme_amount" value="'.$amount.'"/></td></tr>';
        $SponsorMeDisplay .= '<tr><td><label for "sponsorme_comment">Comment</label></td><td><textarea name="sponsorme_comments" id="sponsorme_comments" rows="5" cols="40">'.$comment.'</textarea></td></tr>';
        $SponsorMeDisplay .= '<tr><td><label for "sponsorme_method">Method</label></td><td><select name="sponsorme_method"><option value="paypal">PayPal</option><option value="cheque">Cheque</option></select></td></tr>';
        $SponsorMeDisplay .= '<tr><td colspan="2" align="center"><input type="hidden" name="sponsorme_process" value="yes"><input type="submit" value="Sponsor Me"></td></tr>';
        $SponsorMeDisplay .= '</table>';        
        $SponsorMeDisplay .= '</fieldset>';
        $SponsorMeDisplay .= '</form>';

        $SponsorMeDisplay .= '<hr>';

        $SponsorMeDisplay .= '<p>Thanks to the following for their kind donations:</p>';

        // prepare result
	      $strSQL = "SELECT name, URL, amount,comments FROM $table_name WHERE verified = 'Y'";
	      $rows = $wpdb->get_results ($strSQL);
 
	      if (is_array($rows)):
        $SponsorMeDisplay .= '<ul>';

		      foreach ($rows as $row) { 
                 $SponsorMeDisplay .= '<li>';
                 if ($row->URL != '') $SponsorMeDisplay .= '<a href="'.$row->URL.'">';
                 $SponsorMeDisplay .= $row->name;
                 if ($row->URL != '') $SponsorMeDisplay .= '</a>';
                 $SponsorMeDisplay .= ' - '.$currency . number_format($row->amount, 2, '.', ',').' - '.$row->comments.'</li>';
              }
              $SponsorMeDisplay .= '</ul>';
           else:
              $SponsorMeDisplay .= 'No sponsorships yet';
           endif;

          // check for pending payments
	      $strSQL = "SELECT name, URL, amount FROM $table_name WHERE verified = 'N'";
	      $rows2 = $wpdb->get_results ($strSQL);
 
	      if (is_array($rows2)):
            $SponsorMeDisplay .= '<hr>';
            $SponsorMeDisplay .= '<p>Pending payments:</p>';

            $SponsorMeDisplay .= '<ul>';
		        foreach ($rows2 as $row) { 
                $SponsorMeDisplay .= '<li>Awaiting Confirmation - '.$currency . number_format($row->amount, 2, '.', ',').'</li>';
              }
              $SponsorMeDisplay .= '</ul>';
         endif;
       
		$text = preg_replace("|<!--SponsorMe-page-->|", $SponsorMeDisplay, $text);

	}

	return $text;
} 


function SponsorMe_options() {

   // make sure table is always current
   sponsorme_install();

   global $wpdb;
   $table_name = $wpdb->prefix . "sponsorme";

   // check if anything needs verifying
   if ( $_GET['action'] == 'verify') {
      $sql = "UPDATE ".$table_name." SET verified = 'Y' WHERE id=".$_GET['id'];
      $wpdb->query($sql);
   }

   // check if anything needs clearing
   if ( $_GET['action'] == 'contact') {
      $sql = "UPDATE ".$table_name." SET needcontact = 'N' WHERE id=".$_GET['id'];
      $wpdb->query($sql);
   }

   // check if anything needs deleting
   if ( $_GET['action'] == 'delete') {
      $sql = "DELETE FROM ".$table_name." WHERE id=".$_GET['id'];
      $wpdb->query($sql);
   }

   // check if we're looking for the ID
   if ( $_GET['action'] == 'find') {
			$foundID = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."posts WHERE post_content LIKE '%--SponsorMe-page--%'");
   }

   // Note: Options for this plugin include a "Title" setting which is only used by the widget
   $options = get_option('SponsorMe');
	
   //set initial values if none exist
   if ( !is_array($options) ) {
      $options = array( 'title'=>'Sponsor Me', 'targetdesc'=>'My Target', 'targetamount'=>'100', 'currency'=>'GBP', 'paypal'=>'account@paypal.com', 'pageID'=>'0','backcol'=>'#FFFFFF','barscol'=>'#121212','textwidget'=>'');
   }

   if ( $_POST['SM-submit'] ) {
      $options['title'] = strip_tags(stripslashes($_POST['SM-title']));
      $options['targetdesc'] = strip_tags(stripslashes($_POST['SM-targetdesc']));
      $options['targetamount'] = strip_tags(stripslashes($_POST['SM-targetamount']));
      $options['currency'] = strip_tags(stripslashes($_POST['SM-currency']));
      $options['paypal'] = strip_tags(stripslashes($_POST['SM-paypal']));
      $options['pageID'] = strip_tags(stripslashes($_POST['SM-pageID']));
      $options['textwidget'] = strip_tags(stripslashes($_POST['textwidget']));
      $options['backcol'] = strip_tags(stripslashes($_POST['SM-backcol']));
      $options['barscol'] = strip_tags(stripslashes($_POST['SM-barscol']));
      $options['textwidget'] = strip_tags(stripslashes($_POST['SM-textwidget']));
      update_option('SponsorMe', $options);
   }

   $title = htmlspecialchars($options['title'], ENT_QUOTES);
   $targetdesc = htmlspecialchars($options['targetdesc'], ENT_QUOTES);
   $targetamount = htmlspecialchars($options['targetamount'], ENT_QUOTES);
   $currency = htmlspecialchars($options['currency'], ENT_QUOTES);
   $paypal = htmlspecialchars($options['paypal'], ENT_QUOTES);
   $pageID = htmlspecialchars($options['pageID'], ENT_QUOTES);
   $backcol = htmlspecialchars($options['backcol'], ENT_QUOTES);
   $barscol = htmlspecialchars($options['barscol'], ENT_QUOTES);
   $textwidget = htmlspecialchars($options['textwidget'], ENT_QUOTES);	
   
   if ($foundID != '') $pageID = $foundID;
?>

<div class="wrap"> 

  <h2><?php _e('Donations') ?></h2> 
	<fieldset class="options">
	<legend></legend>
	<?php
		$strSQL = "SELECT * FROM $table_name ORDER BY id DESC";
		$rows = $wpdb->get_results ($strSQL);
	?>
	<table class="widefat">
       <thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>URL</th>
			<th>Amount</th>
			<th>Comment</th>
			<th>Verified</th>
			<th>Need Contact</th>
			<th>Action</th>
		</tr>
       </thead>
	<?php if (is_array($rows)): ?>
		<?php foreach ($rows as $row) { 
             $style=" ";
             if($intAlternate==1) $style=$style."alternate "; 
             if($row->verified=='Y') $style=$style."active ";

             ?>
			<tr<?php if($style!=" "): ?> class="<?php echo $style ?>"<?php endif; ?>>
				<td><?php print $row->name; ?></td>
				<td><?php print "<a href='mailto:".$row->email."'>".$row->email."</a>" ?> </td>
				<td><?php print $row->URL; ?> </td>
				<td><?php print $row->amount; ?> </td>
				<td><?php print $row->comments; ?> </td>
				<td><?php print $row->verified; ?> </td>
			  <td><?php print $row->needcontact; ?> </td>
				<td>
            <?php if($row->needcontact=='Y'): ?>
               <a href="javascript:if(confirm('Have you contacted this individual ?')==true) location.href='<?php echo $_SERVER['PHP_SELF']; ?>?page=sponsorme.php&amp;action=contact&amp;id=<?php echo $row->id ?>';" class="edit">Contacted</a><br>
            <?php endif; ?>
            <?php if($row->verified=='N'): ?>
               <a href="javascript:if(confirm('Are you sure you want to verify ?')==true) location.href='<?php echo $_SERVER['PHP_SELF']; ?>?page=sponsorme.php&amp;action=verify&amp;id=<?php echo $row->id ?>';" class="edit">Verify</a><br>
            <?php endif; ?>
            <a href="javascript:if(confirm('Are you sure you want to delete ? (once it\'s gone, it\'s gone!)')==true) location.href='<?php echo $_SERVER['PHP_SELF']; ?>?page=sponsorme.php&amp;action=delete&amp;id=<?php echo $row->id ?>';" class="edit">Delete</a>
        </td>
			</tr>
			<?php
				if($intAlternate == 1):
					$intAlternate=0;
				else:
					$intAlternate=1;
				endif;
			?>
		<? } ?>
	<?php else: ?>
		<tr><td colspan="5">No donations</td></tr>
	<?php endif; ?>
	</table>
	</fieldset>
(People who don't want to pay by PayPal will be marked as "Need Contact" above. Contact these by email and then click "Contacted" to clear the flag)
<hr>

  <h2><?php _e('Sponsor Me Options') ?></h2> 
  <form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=sponsorme.php">

     <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Title:') ?></th> 
        <td><input name="SM-title" type="text" id="SM-title" value="<?php echo $title; ?>" size="80" />
		<br />
        <?php _e('Enter the title to use') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Target:') ?></th> 
        <td><input name="SM-targetdesc" type="text" id="SM-targetdesc" value="<?php echo $targetdesc; ?>" size="80" />
        <br />
        <?php _e('What is the item/target you are aiming for?') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Target Amount:') ?></th> 
        <td><input name="SM-targetamount" type="text" id="SM-targetamount" value="<?php echo $targetamount; ?>" size="80" />
        <br />
        <?php _e('What is the target amount you are trying to collect?') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Currency:') ?></th> 
        <td><input name="SM-currency" type="text" id="SM-currency" value="<?php echo $currency; ?>" size="80" />
        <br />
        <?php _e('What currency would you like to us?') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('PayPal:') ?></th> 
        <td><input name="SM-paypal" type="text" id="SM-paypal" value="<?php echo $paypal; ?>" size="80" />
        <br />
        <?php _e('What is your paypal account?') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Page ID:') ?></th> 
        <td><input name="SM-pageID" type="text" id="SM-pageID" value="<?php echo $pageID; ?>" size="80" /><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=sponsorme.php&amp;action=find">Find this for me</a>
        <br />
        <?php _e('What is the Page ID of the page you have put the '.htmlspecialchars('<!--SponsorMe-page-->').' tag on') ?></td> 
      </tr> 
    </table>
    
  <h2><?php _e('Presentation Options') ?></h2>     

    <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Text Widget:') ?></th> 
        <td>
        <select id="SM-textwidget" name="SM-textwidget">
                <option value="" <?php if ($textwidget=='') echo 'selected'; ?>>No, I prefer a graph</option>
                <option value="Yes" <?php if ($textwidget=='Yes') echo 'selected'; ?>>Yes, I prefer text in my sidebar</option>
         </select>
        <br />
        <?php _e('Select whether you prefer a graph in your sidebar or just text') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Background Colour:') ?></th> 
        <td><input name="SM-backcol" type="text" id="SM-backcol" value="<?php echo $backcol; ?>" size="80" />
		<br />
        <?php _e('Enter the background colour to use (ex #FFFFFF)') ?></td> 
      </tr> 
      <tr valign="top"> 
        <th scope="row"><?php _e('Bars Colour:') ?></th> 
        <td><input name="SM-barscol" type="text" id="SM-barscol" value="<?php echo $barscol; ?>" size="80" />
        <br />
        <?php _e('Enter the bars colour to use (ex #121212)') ?></td> 
      </tr> 
    </table>

	<input type="hidden" id="-submit" name="SM-submit" value="1" />

    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
    </p>
  </form> 

</div>

<?php
}


function sponsorme_uninstall () {

   // Cleanup routine. Not sure if we'll need this in the final build, But for now it makes experimenting
   // with table structures much easier.

//   global $wpdb;

//   $table_name = $wpdb->prefix . "sponsorme";
//   if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
//      $wpdb->query("DROP TABLE {$table_name}");
//   }

}



function sponsorme_install () {
   global $wpdb;
   global $sponsorme_db_version;
   
   $installed_ver = get_option("sponsorme_db_version");
      
   if ($installed_ver != $sponsorme_db_version) {
   
      $table_name = $wpdb->prefix . "sponsorme";
     
      $sql = "CREATE TABLE " . $table_name . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name tinytext NOT NULL default '',
      email tinytext NOT NULL default '',
      URL tinytext NOT NULL default '',
      amount decimal(10,2) NOT NULL,
      comments tinytext NOT NULL default '',
      verified enum('Y', 'N') DEFAULT 'N' NOT NULL,
      needcontact enum('Y', 'N'),
      UNIQUE KEY id (id)
      );";

      if ($wp_version >= '2.3') {
         require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      } else {
         require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
      }
      dbDelta($sql);

      update_option("sponsorme_db_version", $wpa_db_version);

      // Insert a test Record if table is blank
    
      $record_count = $wpdb->get_var("select count(*) from ".$table_name);
      
      if($record_count == 0) {    
         $sql = 'INSERT INTO `'.$table_name.'` (`id`, `name`, `email`, `URL`, `amount`, `comments`  ,`verified`) VALUES (NULL, \'Owen\', \'owen@cutajar.net\', \'http://www.u-g-h.com\', 0, \'Good luck!\', \'Y\');';
         $wpdb->query($sql);
      }
   }
}

function wp_sponsorme_header() {
     echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . SM_PLUGIN_EXTERNAL_PATH . 'style/style.css" />' . "\n\n";
}    

function SponsorMe_adminmenu(){
   if (function_exists('add_management_page')) {
	add_management_page('Sponsor Me Options', 'SponsorMe', 9, 'sponsorme.php', 'SponsorMe_options');
   }
}

add_action('wp_head', 'wp_sponsorme_header');
add_filter('the_content', 'SponsorMe_text', 2);
add_action('admin_menu', 'SponsorMe_adminmenu',1);
add_action('activate_'.plugin_basename(__FILE__), 'sponsorme_install');
add_action('deactivate_'.plugin_basename(__FILE__), 'sponsorme_uninstall');
add_action('widgets_init', 'widget_SponsorMe_init');
?>