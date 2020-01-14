<?php 
    /* coded by Rahul Barui ( https://github.com/Rahul-Barui ) */
    include "dbcon.php";

	/*print "<pre>";
	print_r($_POST);
	var_dump($_POST);
	die;*/

	$payment_id = $statusMsg = ''; 
	$ordStatus = 'error';
	$id = '';

	// Check whether stripe token is not empty

	if(!empty($_POST['stripeToken'])){

		// Get Token, Card and User Info from Form
		$token = $_POST['stripeToken'];
		$name = $_POST['holdername'];
		$email = $_POST['email'];
		$card_no = $_POST['card_number'];
		$card_cvc = $_POST['card_cvc'];
		$card_exp_month = $_POST['card_exp_month'];
		$card_exp_year = $_POST['card_exp_year'];

		// Get Product ID From - Form
		$productId = $_POST['productId'];

		// Get Product Details By Using Product-Id
		$SQL_getPr = "SELECT * FROM `products` WHERE `id`='$productId'";
	    $res_getPr = mysqli_query($con,$SQL_getPr) or die("MySql Query Error".mysqli_error($con));
	    $row_getPr = mysqli_fetch_assoc($res_getPr);
	    $price = $row_getPr['price'];
	    $pr_desc = $row_getPr['name'];

		// Include STRIPE PHP Library
		require_once('stripe-php/init.php');

		// set API Key
		$stripe = array(
		"SecretKey"=>"sk_test_cEjXShRlZO1YrzP8Txn6Ulya00EhUj1dRS",
		"PublishableKey"=>"pk_test_18lgnnPV3SZZn36tyAFO131T00P2pCl90m"
		);

		// Set your secret key: remember to change this to your live secret key in production
		// See your keys here: https://dashboard.stripe.com/account/apikeys
		\Stripe\Stripe::setApiKey($stripe['SecretKey']);

		// Add customer to stripe 
	    $customer = \Stripe\Customer::create(array( 
	        'email' => $email, 
	        'source'  => $token,
	        'name' => $name,
	        'description'=>$pr_desc
	    ));

	    // Generate Unique order ID 
	    $orderID = strtoupper(str_replace('.','',uniqid('', true)));
	     
	    // Convert price to cents 
	    $itemPrice = ($price*100);
	    $currency = "usd";
	    $itemName = $row_getPr['name'];

	    // Charge a credit or a debit card 
	    $charge = \Stripe\Charge::create(array( 
	        'customer' => $customer->id, 
	        'amount'   => $itemPrice, 
	        'currency' => $currency, 
	        'description' => $itemName, 
	        'metadata' => array( 
	            'order_id' => $orderID 
	        ) 
	    ));

	    // Retrieve charge details 
    	$chargeJson = $charge->jsonSerialize();

    	// Check whether the charge is successful 
    	if($chargeJson['amount_refunded'] == 0 && empty($chargeJson['failure_code']) && $chargeJson['paid'] == 1 && $chargeJson['captured'] == 1){ 

	        // Order details 
	        $transactionID = $chargeJson['balance_transaction']; 
	        $paidAmount = $chargeJson['amount']; 
	        $paidCurrency = $chargeJson['currency']; 
	        $payment_status = $chargeJson['status'];
	        $payment_date = date("Y-m-d H:i:s");
	        $dt_tm = date('Y-m-d H:i:s');

	        // Insert tansaction data into the database

	        $sql = "INSERT INTO `orders`(`name`,`email`,`card_number`,`card_exp_month`,`card_exp_year`,`item_name`,`item_number`,`item_price`,`item_price_currency`,`paid_amount`,`paid_amount_currency`,`txn_id`,`payment_status`,`created`,`modified`) VALUES ('$name','$email','$card_no','$card_exp_month','$card_exp_year','$itemName','','$itemPrice','$currency','$paidAmount','$paidCurrency','$transactionID','$payment_status','$dt_tm','$dt_tm')";
	        mysqli_query($con,$sql) or die("Mysql Error Stripe-Charge(SQL)".mysqli_error($con));

    		//Get Last Id
    		$sql_g = "SELECT * FROM `orders`";
    		$res_g = mysqli_query($con,$sql_g) or die("Mysql Error Stripe-Charge(SQL2)".mysqli_error($con));
    		while($row_g=mysqli_fetch_assoc($res_g)){
    			$id = $row_g['id'];
    		}

	        // If the order is successful 
	        if($payment_status == 'succeeded'){ 
	            $ordStatus = 'success'; 
	            $statusMsg = 'Your Payment has been Successful!'; 
	    	} else{ 
	            $statusMsg = "Your Payment has Failed!"; 
	        } 
	    } else{ 
	        //print '<pre>';print_r($chargeJson); 
	        $statusMsg = "Transaction has been failed!"; 
	    } 
	} else{ 
	    $statusMsg = "Error on form submission."; 
	} 
	
?>

<!DOCTYPE html>
<html>
	<head>
        <title> Stripe Payment Gateway Integration in PHP </title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="css/stripe.css">
    </head>

    <div class="container">
        <h2 style="text-align: center; color: blue;">Stripe Payment Gateway Integration in PHP </h2>
        <h4 style="text-align: center;">This is - Stripe Payment Success URL </h4>
        <br>
        <div class="row">
	        <div class="col-lg-12">
				<div class="status">
					<h1 class="<?php echo $ordStatus; ?>"><?php echo $statusMsg; ?></h1>
				
					<h4 class="heading">Payment Information - </h4>
					<br>
					<p><b>Reference ID:</b> <strong><?php echo $id; ?></strong></p>
					<p><b>Transaction ID:</b> <?php echo $transactionID; ?></p>
					<p><b>Paid Amount:</b> <?php echo $paidAmount.' '.$paidCurrency; ?> ($<?php echo $price;?>.00)</p>
					<p><b>Payment Status:</b> <?php echo $payment_status; ?></p>
					<h4 class="heading">Product Information - </h4>
					<br>
					<p><b>Name:</b> <?php echo $itemName; ?></p>
					<p><b>Price:</b> <?php echo $itemPrice.' '.$currency; ?> ($<?php echo $price;?>.00)</p>
				</div>
				<a href="index.php" class="btn-continue">Back to Home</a>
			</div>
		</div>
	</div>
</html>