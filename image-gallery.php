<?php
include "common.php";

// منع أخطاء المتغيرات غير المعرفة في PHP 8.3
$uid = $uid ?? 0;

$_SESSION['last_page'] = "image-gallery.php";
if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') {
    header("Location: sign-in.php");
    exit;
}
$uid = (int)$_SESSION['uid_indm'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo htmlspecialchars(getSiteTitle() ?? '', ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- css start -->
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/dir-new.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->

<!-- js start -->
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
function backToListing() {
    $("#details").css("display", "none");
    $("#listing").css("display", "block");
}

function editSaleOffer(id) {
    $.post("ajax-file/sale-offer-edit.php", {id: id}, function(data) {
        $('#details').html(data);
    });
}

function delGalleryImage(id) {
    if (confirm("Are you sure to delete this Image?")) {
        $.post("ajax-file/delGalleryImage.php", {id: id}, function(data) {
            showGalleryImage(1);
        });
    }
}

function showGalleryImage(page) {
    $.post("ajax-file/showGalleryImage.php", {page: page}, function(data) {
        $('#res').html(data);
    });
}
</script>
</head>
<body>
<div id="imgtrailer" style="position:absolute; z-index:4; visibility:hidden;"><img src="images/loading.gif" height="32" width="32"></div>

<!--main div:start-->
<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>

    <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? '', ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>

    <div class="inner_wrapper">
        <?php include "includes/header_menu.php"; ?>

        <!--left navigation:start-->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <?php include "includes/seller-tools-panel.php"; ?>
        </div>
        <!--left navigation:ends-->

        <div id="details" style="display:none;"></div>
        <div class="mctr mfl" id="listing">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td valign="TOP" width="100%">
                            <style type="text/css">
                                .sub { display: none; }
                                .tbq { display: none; }
                                .active_tab { background-color: #03F; }
                                .tab { background-color: #999; }
                            </style>

                            <form style="margin:0px;" action="" name="form1">
                                <table align="CENTER" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td valign="TOP" width="100%">
                                                <div class="wd1 mf18 mc5 mta2 mpb10">
                                                    Manage Image Gallery .. إحتفظ بصور الشركة فى جاليرى المنصة
                                                </div>

                                                <div id="masterdiv">
                                                    <div id="sub2" style="display:inline;">
                                                        <script type="text/javascript">
                                                        $(document).ready(function() {
                                                            showGalleryImage(1);
                                                        });
                                                        </script>
                                                        <div id="res"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td valign="TOP"><img src="images/zero.gif" height="1" width="10"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td height="30"></td>
                                        <td><div class="liv" style="margin-right:20px;" align="RIGHT"><b></b></div></td>
                                    </tr>
                                </tbody>
                            </table>

                            <div style="clear:both"><br></div>
                            <div align="CENTER"><br></div>
                            <div align="CENTER"><br><br></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="c3">&nbsp;</div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>