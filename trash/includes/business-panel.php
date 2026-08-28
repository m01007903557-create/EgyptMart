<?php
/*  $currentFile= $_SERVER['SCRIPT_NAME'];
  $parts = Explode('/', $currentFile);
  $parts = $parts[count($parts) - 1];
  $parts = Explode('.', $parts);
  $pageName = $parts[0];*/
?>
<div class="ap1 mt5">
<a class="<?php if($file=="business-details"){ ?>f1 ap2<?php } else { ?>f1 npo<?php } ?>" href="business-details.php">Business Details</a>
<a class="<?php if($file=="statutory-details"){ ?>f1 ap2 m5<?php } else { ?>f1 npo m5<?php } ?>" href="statutory-details.php">Statutory Details</a>
<a class="<?php if($file=="myproduct-buy"){ ?>f1 ap2 m5<?php } else { ?>f1 npo m5<?php } ?>" href="myproduct-buy.php">Regular Buy Products </a>
<a class="<?php if($file=="myproduct-sell"){ ?>f1 ap2 m5<?php } else { ?>f1 npo m5<?php } ?>" href="myproduct-sell.php">Regular Sell Products</a>
<a class="f1 npo m5" href="product-list.php">My Listed Products</a>
<div class="c3"></div>
</div>