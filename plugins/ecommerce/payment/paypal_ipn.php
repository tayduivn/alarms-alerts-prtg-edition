<?php

/*===============================================*\
|| ############################################# ||
|| # CLARICOM.CA                                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2016 CLARICOM All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!file_exists('../../../config.php')) die('[ipn.php] config.php not exist');
require_once '../../../config.php';

// Include the paypal library
include_once ('paypal.php');

$sendmail = 0;

// Create an instance of the paypal library
$myPaypal = new Paypal();

// Log the IPN results
$myPaypal->ipnLog = TRUE;

// Enable test mode if needed
//$myPaypal->enableTestMode();

// Check validity and write down it
if ($myPaypal->validateIpn()) {
    if ($myPaypal->ipnData['payment_status'] == 'Completed') {
    
    	$item_name = $myPaypal->ipnData['item_name'];
    	$payment_currency = $myPaypal->ipnData['mc_currency'];
    	$payment_status = $myPaypal->ipnData['payment_status'];
    	$txn_id = $myPaypal->ipnData['txn_id'];
    	$receiver_email = $myPaypal->ipnData['receiver_email'];
    	$payment_amount = $myPaypal->ipnData['mc_gross'];
    	$payer_email = $myPaypal->ipnData['payer_email'];
    	$custom = base64_decode($myPaypal->ipnData['custom']);
    	
    	// Explode custom field with order number and orderid
    	$customA = explode(":#:", $custom);
    	
    	// check that payment_amount/payment_currency are correct
    	global $jakdb;
    	
    	// process payment
    	$result = $jakdb->query('INSERT INTO '.DB_PREFIX.'shop_payment_ipn SET 
    	ordernr = "'.smartsql($customA[1]).'",
    	time = NOW(),
    	status = "'.smartsql($payment_status).'",
    	amount = "'.smartsql($payment_amount).'",
    	currency = "'.smartsql($payment_currency).'",
    	txn_id = "'.smartsql($txn_id).'",
    	receiver_email = "'.smartsql($receiver_email).'",
    	payer_email = "'.smartsql($payer_email).'",
    	paid_with = "Paypal"');
    	
    	// check that txn_id has not been previously processed
    	$jakdb->query('SELECT id FROM '.DB_PREFIX.'shop_payment_ipn WHERE txn_id = "'.smartsql($txn_id).'"');
    	if ($jakdb->affected_rows == 1) {
    	
    		// Select price and item
    		$result1 = $jakdb->query('SELECT id, total_price, email, name, userid FROM '.DB_PREFIX.'shop_order WHERE id = "'.smartsql($customA[0]).'" AND ordernumber = "'.smartsql($customA[1]).'"');
    		$row1 = $result1->fetch_assoc();
    			
    	if ($row1['total_price'] == $payment_amount) {
    	
    		$sendmail = 1;
    		
    		$jakdb->query('UPDATE '.DB_PREFIX.'shop_order SET paid = 1, paidtime = NOW() WHERE id = "'.smartsql($row1['id']).'"');
    		
    	}
    	   
    	   // Send email to customer if everything is paid
    	   if ($sendmail) {
    	   
    	   		// Send email to administrator
    	   		// log for manual investigation
    	   		$maila = new PHPMailer(); // defaults to using php "mail()"
    	   		$maila->SetFrom($payer_email, $payer_email);
    	   		$maila->AddAddress($jkv["shopemail"], $jkv["e_title"]);
    	   		$maila->Subject = $jkv["e_title"].' - PAYPAL Success';
    	   		$maila->Body = 'There is a new payment thru Paypal, order number: '.$customA[1].' - '.$item_name.' - '.$payment_status.' - '.$payment_amount.' - '.$payment_currency.' - '.$txn_id.' - '.$receiver_email.' - '.$payer_email;
    	   		$maila->Send(); // Send email without any warnings
    	   		
    	   		// Send Email to customer
    	   		$mail = new PHPMailer(); // defaults to using php "mail()"
       	   		$body = str_ireplace("[\]", '', $jkv["e_thanks"].'<p>Order Number:'.$customA[1].'</p>');
    	   		$mail->SetFrom($jkv["shopemail"], $jkv["e_title"]);
    	   		$mail->AddAddress($payer_email);
    	   		$mail->Subject = $jkv["title"].' - Payment approved';
    	   		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
    	   		$mail->MsgHTML($body);
    	   		$mail->Send(); // Send email without any warnings
    	   		
    	   		// Now let's update the usergroup
    	   		$rowu = $jakdb->queryRow('SELECT t2.id, t2.usergroup FROM '.DB_PREFIX.'shop_order_details AS t1 LEFT JOIN '.DB_PREFIX.'shop AS t2 ON (t1.shopid = t2.id) WHERE t1.orderid = "'.smartsql($row1['id']).'" AND t2.usergroup != 0 GROUP BY t1.shopid LIMIT 1');
    	   		
    	   		if ($jakdb->affected_rows === 1) {
    	   			
    	   			$jakdb->query('UPDATE '.DB_PREFIX.'user SET 
    	   				usergroupid = "'.smartsql($rowu["usergroup"]).'",
    	   				WHERE id = "'.smartsql($row1['userid']).'"');
    	   		
    	   		}
    	   		
    	   		// Now let's send the digital files if there are any
    	   		$row_option = $jakdb->queryRow('SELECT t2.id, t2.title, t2.digital_file FROM '.DB_PREFIX.'shop_order_details AS t1 LEFT JOIN '.DB_PREFIX.'shop AS t2 ON (t1.shopid = t2.id) WHERE t1.orderid = "'.$row1['id'].'" AND t2.digital_file != "" GROUP BY t1.shopid LIMIT 1');
    	   		
    	   		// we have a digital file
    	   		if ($jakdb->affected_rows === 1) {
    	   							
    	   			$downloadid = time();
    	   								
    	   			$jakdb->query('UPDATE '.DB_PREFIX.'shop_order SET 
    	   			downloadid = "'.smartsql($downloadid).'",
    	   			downloadtime = ADDDATE(NOW(), INTERVAL 7 DAY)
    	   			WHERE id = "'.smartsql($row1['id']).'"');
    	   									
    	   			// Create download link
    	   			$download_link = '<p>'.$jkv["e_shop_download_b"].' <a href="'.(JAK_USE_APACHE ? substr(str_replace('plugins/ecommerce/payment/', '', BASE_URL), 0, -1) : str_replace('plugins/ecommerce/payment/', '', BASE_URL)).JAK_rewrite::jakParseurl($customA[3], 'dl', $customA[1], $row_option['id'], $downloadid).'">'.$jkv["e_shop_download_bt"].'</a></p>';
    	   			
    	   			$body = '<body style="margin:10px;">
    	   			<div style="width:750px; font-family: \'Droid Serif\', Helvetica, Arial, sans-serif; font-size: 14px;">
    	   			<div align="center"><img src="'.str_replace('payment/', '', BASE_URL).'img/header.jpg" style="height: 90px; width: 750px"></div>
    	   			<p>
    	   			'.$jkv["e_shop_download"].'
    	   			</p>
    	   			'.$download_link.'
    	   			<p>'.$jkv["title"].'</p>
    	   			</div>
    	   			</body>';
    	   								
    	   			$mailf = new PHPMailer(); // defaults to using php "mail()"
    	   			$bodyf = str_ireplace("[\]",'',$body);
    	   			
    	   			if ($jkv["shopemail"]) {
    	   			   	$mailf->SetFrom($jkv["shopemail"], $jkv["e_title"]);
    	   			} else {
    	   				$mailf->SetFrom($jkv["email"], $jkv["title"]);
    	   			}
    	   			
    	   			$mailf->AddAddress($row1['email'], $row1['name']);
    	   			$mailf->Subject = $jkv["title"].' - Digital File(s) - '.$row_option['title'];
    	   			$mailf->AltBody = 'To view the message, please use an HTML compatible email viewer!';
    	   			$mailf->MsgHTML($bodyf);
    	   			
    	   			if ($mailf->Send()) {
    	   			
    	   				$maila1 = new PHPMailer(); // defaults to using php "mail()"
    	   				$maila1->SetFrom($jkv["shopemail"], $jkv["e_title"]);
    	   				$maila1->AddReplyTo($row1['email'], $row1['name']);
    	   				$maila1->AddAddress($jkv["shopemail"], $jkv["e_title"]);
    	   				$maila1->Subject = $jkv["e_title"].' - PAYPAL Success - Digital File(s)';
    	   				$maila1->Body = 'Link has been sent...';
    	   				$maila1->Send(); // Send email without any warnings
    	   			
    	   			}
    	   			    
    	   		}
    	   		
    	   	} else {
    	   	
    	   		// log for manual investigation amount is not the same
    	   		$mail = new PHPMailer(); // defaults to using php "mail()"
    	   		$mail->SetFrom($payer_email, $payer_email);
    	   		$mail->AddAddress($jkv["shopemail"], $jkv["shopemail"]);
    	   		$mail->Subject = $jkv["e_title"].' - PAYPAL Success, but...';
    	   		$mail->Body = 'There is a new payment thru Paypal, order number: '.$customA[1].' - '.$item_name.' - '.$payment_status.' - '.$payment_amount.' - '.$payment_currency.' - '.$txn_id.' - '.$receiver_email.' - '.$payer_email.' But the amount was paid is not the same amount was ordered, please check in the shop order details.';
    	   		$mail->Send(); // Send email without any warnings
    	   	
    	   	}
    	   	
    	   	// Finally Delete the shopping Cart
    	   	$jakdb->query('DELETE FROM '.DB_PREFIX.'shopping_cart WHERE session = "'.smartsql($customA[2]).'"');
    	   	
    	}
    	       	
    } else {
         
         // log for manual investigation
         $mail = new PHPMailer(); // defaults to using php "mail()"
         $mail->SetFrom($payer_email, 'Paypal HTTP error');
         $mail->AddAddress($jkv["shopemail"], $jkv["title"]);
         $mail->Subject = $jkv["title"].' - PAYPAL HTTP error';
         $mail->Body = 'There is an error with PAYPAL, please check the text file.';
    }
}