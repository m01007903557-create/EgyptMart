<?php
/*  $currentFile= $_SERVER['SCRIPT_NAME'];
  $parts = Explode('/', $currentFile);
  $parts = $parts[count($parts) - 1];
  $parts = Explode('.', $parts);
  $pageName = $parts[0];*/
?>
<div class="ap1 mt5">
<a class="<?php if($file=="business-details"){ ?>f1 ap2<?php } else { ?>f1 npo<?php } ?>" href="business-details.php"title="Business Details "
> معلومات الأعمال التجارية</a>
<a class="<?php if($file=="statutory-details"){ ?>f1 ap2 m5<?php } else { ?>f1 npo m5<?php } ?>" href="statutory-details.php"title="Statutory Details">المعلومات القانونية  للشركة  </a>
<a class="<?php if($file=="myproduct-buy"){ ?>f1 ap2 m5<?php } else { ?>f1 npo m5<?php } ?>" href="myproduct-buy.php"title="Products We Buy ">منتجاتى المعتاده الشراء</a>
<a class="<?php if($file=="myproduct-sell"){ ?>f1 ap2 m5<?php } else { ?>f1 npo m5<?php } ?>" href="myproduct-sell.php"title=" Products We Sell"> منتجاتى المعتاده للبيع </a>
<a class="f1 npo m5" href="product-list.php"title="Our Listed Products"
>منتجاتى على المنصة   </a>
<div class="c3"></div>
</div>