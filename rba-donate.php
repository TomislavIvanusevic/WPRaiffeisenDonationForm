<?php
/*
  Plugin Name: RBA donation form
  Plugin URI: https://github.com/techlogx/WPRaiffeisenDonationForm
  Description: Donation+etomitreba RBA gateway.
  Version: 1.0
  Author: logx
  Author URI: https://github.com/techlogx

 */
   function donate_form () {
   	ob_start() ?>
   		<form name="donate" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
   			<p>
   				<input type="radio" name="amount" value="2000" /> <a class="donation">20</a>
  				<input type="radio" name="amount" value="5000"/> <a class="donation">50</a>
  				<input type="radio" name="amount" value="10000" /> <a class="donation">100</a>
  				<input type="radio" name="amount" value="20000" /> <a class="donation">200</a>
  				<input type="radio" name="amount" value="50000" /> <a class="donation">500</a> <br />
  			</p>

  			<p>
  				<input type="radio" name="amount" value="other" /> Other amount:
  				<input type="text" name="otherAmount"/>
  				<input type="hidden" name="action" value="donate_form">
   			</p>
   			<input type="submit" name="submit">
   		</form>
   		<?php
      return ob_get_clean();
   }
 //register shortcode to display the ^form^
add_shortcode( 'donate', 'donate_form' );

 function form_processing (){

   //gateway info
 	  $MerchantID = 'xxx'; //enter your merchantID
    $TerminalID = 'xxx'; //enter your TerminalID
    $postActionUrl = 'https://secure.rba.hr/ecgtesthr/enter'; //gateway process URL, consult the instructions for the correct process URL(this one is for Croatian demo purposes)
    $PurchaseTime = date("ymdHis") ;
    //workaround: RBA gateway requires amount to be without decimal points. eg. 200.00 = 20000
    if ($_POST["amount"] === 'other') {
    	    $Amount = $_POST['otherAmount'];
    	    $TotalAmount = $Amount .+ 0 .+ 0;

    }else {
    	    $TotalAmount = $_POST['amount'];

    }
    $CurrencyID = '191'; //191=HRK, enter your correct ISO currency code
    $session_id = session_id();
    $order_id = date("His"); //workaround: provides current time as an unique order ID
    $data = "$MerchantID;$TerminalID;$PurchaseTime;$order_id;$CurrencyID;$TotalAmount;$session_id;";
    //load the key and sign
    $fp = fopen("path/to/your/.pem file", "r");
	  $priv_key = fread($fp, 8192);
	  fclose($fp);
	  $pkeyid = openssl_get_privatekey($priv_key);
    openssl_sign( $data, $signature, $pkeyid );
    openssl_free_key($pkeyid);

    $b64sign = base64_encode($signature);

    //RBA form and POST to gateway
    ?>
    <!DOCTYPE html>
    <html>
    <head>
    <title>RBA</title>
    </head>
    <body>
      	<form id="paymentsend" action="<?php echo $postActionUrl ?>" method="post"   >
  		   <p><input name="Version" type="hidden" value="1" >
  	     <p><input name="MerchantID" type="hidden" value="<?php echo $MerchantID ?>"  >
  	     <p><input name="TerminalID" type="hidden" value="<?php echo $TerminalID ?>"  >
  	     <p><INPUT name="TotalAmount" type="hidden" value="<?php echo $TotalAmount ?>">
  	     <p><INPUT name="Currency" type="hidden" value="<?php echo $CurrencyID ?>" >
  	     <p><INPUT name="Locale" type="hidden" value="en">
  	     <p><input name="SD" type="hidden" value="<?php echo $session_id ?>" >
  	     <p><input name="OrderID" type="hidden" value="<?php echo $order_id ?>"  >
  	     <p><input name="PurchaseTime" type="hidden" value="<?php echo $PurchaseTime ?>">
  	     <p><input name="PurchaseDesc" type="hidden" value="Donation" >
  	     <p><input name="Signature" type="hidden" value="<?php echo $b64sign ?>" >

	      </form>
	       <script>
            document.getElementById('paymentsend').submit();
         </script>
    </body>
    </html>

<?php

 }
add_action( 'admin_post_nopriv_donate_form', 'form_processing' );
add_action( 'admin_post_donate_form', 'form_processing' );
?>
