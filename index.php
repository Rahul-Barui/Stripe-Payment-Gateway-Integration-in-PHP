<?php 
    /* coded by Rahul Barui ( https://github.com/Rahul-Barui ) */
    include "dbcon.php";
?>
<html>
    <head>
            <title> Stripe Payment Gateway Integration in PHP </title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
            <link rel="stylesheet" type="text/css" href="css/design.css">
            <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
            <script src="js/jquery.min.js"></script>
    </head>
    <body>

        <div class="container">
            <h2 style="text-align: center; color: blue;">Stripe Payment Gateway Integration in PHP </h2>

            <?php 
            $sql = "SELECT * FROM `products`";
            $res = mysqli_query($con,$sql) or die("MySql Query Error".mysqli_error($con));
            while($row=mysqli_fetch_assoc($res)){
            ?>
            <form method="post" action="stripe_form.php">
                <div class="col-md-4 column productbox">
                    <input type="hidden" name="id" value="<?php echo $row['id'];?>"/>
                    <img src="<?php echo $row['image'];?>" class="img-responsive">
                    <div class="producttitle"><?php echo $row['name'];?></div>
                    <div class="productprice">
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary btn-sm" name="submit" role="button">Buy Now</button>
                        </div>
                        <div class="pricetext">$<?php echo $row['price'];?></div>
                    </div>
                </div>
            </form>
            <?php } ?>
        </div>
       
    </body>
</html>