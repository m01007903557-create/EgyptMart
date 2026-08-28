<?php
/**
 * File: tender-details.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض تفاصيل المناقصة للواجهة الأمامية
 * Display tender details for frontend
 * 
 * Features:
 * - عرض جميع تفاصيل المناقصة
 * - عرض معلومات الشركة
 * - إرسال استفسار
 * - شراء المناقصة بالرصيد
 * - عرض خطط العضوية
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "common.php";

// Get tender ID from token
$tnd_id = isset($_GET['id']) ? (int)substr($_GET['id'], 4) : 0;

if ($tnd_id === 0) {
    header("Location: tenders.php");
    exit;
}

// Get user ID if logged in
$uid = (int)($_SESSION['uid_indm'] ?? 0);

// Get membership plan if logged in
$membership_plan = '';
if ($uid > 0) {
    $query_mp = mysqli_query($con, "SELECT mst_name FROM smembership_plan mp 
                                     JOIN user u ON u.usr_mp_id = mp.mp_id 
                                     WHERE u.usr_id = $uid");
    if ($query_mp && mysqli_num_rows($query_mp) > 0) {
        $row_mp = mysqli_fetch_object($query_mp);
        $membership_plan = $row_mp->mst_name ?? '';
    }
}

// Get tender details
$sql = "SELECT t.*, pc.pc_name as category_name, u.*, bf.* 
        FROM tender t
        LEFT JOIN product_category_arabyos pc ON t.tnd_pc_id = pc.pc_id
        JOIN user u ON t.tnd_usr_id = u.usr_id
        JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
        WHERE md5(t.tnd_id) = '" . mysqli_real_escape_string($con, (string)$tnd_id) . "'";

$res = mysqli_query($con, $sql);
$row = mysqli_fetch_object($res);

if (!$row) {
    // Try without category join
    $sql = "SELECT t.*, u.*, bf.* 
            FROM tender t
            JOIN user u ON t.tnd_usr_id = u.usr_id
            JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
            WHERE md5(t.tnd_id) = '" . mysqli_real_escape_string($con, (string)$tnd_id) . "'";
    
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_object($res);
}

if (!$row) {
    header("Location: tenders.php");
    exit;
}

// Get category path
$sql_pcat = "SELECT m.pc_id as main_id, m.pc_name as main_name, 
                    c.pc_id as cat_id, c.pc_sort_name as cat_name,
                    s.pc_sort_name as subcat_name
             FROM product_category_arabyos s
             JOIN product_category_arabyos c ON s.pc_parent_id = c.pc_id
             JOIN product_category_arabyos m ON c.pc_parent_id = m.pc_id
             WHERE s.pc_id = " . (int)$row->tnd_pc_id;

$res_pcat = mysqli_query($con, $sql_pcat);
$category_path = mysqli_fetch_assoc($res_pcat);

// Check if user has purchased this tender
$purchased = false;
$purchase_date = null;
if ($uid > 0 && $uid != $row->tnd_usr_id) {
    $sql_chk = "SELECT * FROM purchased_tender 
                WHERE ptnd_usr_id = $uid AND ptnd_tnd_id = " . (int)$row->tnd_id;
    $res_chk = mysqli_query($con, $sql_chk);
    
    if (mysqli_num_rows($res_chk) > 0) {
        $purchased = true;
        $row_chk = mysqli_fetch_object($res_chk);
        $purchase_date = $row_chk->ptnd_purchase_date ?? null;
    }
}

// Check user credit
$user_credit = 0;
$credit_available = false;
if ($uid > 0) {
    $sql_usr = "SELECT usr_credit FROM user WHERE usr_id = $uid";
    $res_usr = mysqli_query($con, $sql_usr);
    $row_usr = mysqli_fetch_object($res_usr);
    $user_credit = (int)($row_usr->usr_credit ?? 0);
    $credit_available = ($user_credit >= 20);
}

// Check if user is member of specific plans
$is_promo_plan = (
    strpos($membership_plan, 'خطة برومو') === false && 
    strpos($membership_plan, 'راعى رئيسى') === false && 
    strpos($membership_plan, 'عضو مميز') === false &&
    stripos($membership_plan, 'sponser') === false && 
    stripos($membership_plan, 'sponsor') === false && 
    stripos($membership_plan, 'senior') === false
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle()); ?></title>
    
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
    
    <!-- CSS Files -->
    <link href="css/trade-7.css" rel="stylesheet" type="text/css">
    <link href="css/bl_form_temp1.css" rel="stylesheet" type="text/css">
    <link href="css/trade-detail1.css" rel="stylesheet" type="text/css">
    <link href="css/mojozoom.css" rel="stylesheet" type="text/css">
    
    <!-- jQuery -->
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    
    <!-- mojoZoom for image zoom -->
    <script type="text/javascript" src="js/mojozoom.js"></script>
    
    <style>
        .form_area {
            background: #FFF;
            border: 1px solid #bebebe;
            width: 683px;
            min-height: 216px;
            text-align: left;
            font-family: arial;
            border-radius: 5px;
            -webkit-border-radius: 3px;
            -moz-border-radius: 5px;
            box-shadow: 0px 1px 11px rgba(0,0,0,0.30);
            -webkit-box-shadow: 0px 1px 11px rgba(0,0,0,0.30);
            -moz-box-shadow: 0px 1px 11px rgba(0,0,0,0.30);
            padding-top: 3px;
        }
        
        .sbtn, .sbtn:focus {
            color: #000;
            font-family: arial;
            font-size: 18px;
            font-weight: bold;
            padding: 6px 15px 6px 15px;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            margin-top: 5px;
            width: 288px;
            clear: both;
            border: solid #db8700 1px;
            background: #ffcb65;
            background: -webkit-gradient(linear, 0 0, 0 100%, from(#ffcb65), to(#ffb13a));
            background: -moz-linear-gradient(top, #ffcb65, #ffb13a);
            -ms-filter: progid:DXImageTransform.Microsoft.gradient(startColorStr=#ffcb65, endColorStr=#ffb13a);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorStr=#ffcb65, endColorStr=#ffb13a);
            text-align: center;
            cursor: pointer;
            border-radius: 3px 3px 3px 3px;
            height: auto !important;
        }
        
        .sbtn:hover {
            background: #ffcb65;
            background: -webkit-gradient(linear, 0 0, 0 100%, from(#ffb13a), to(#ffcb65));
            background: -moz-linear-gradient(top, #ffb13a, #ffcb65);
            -ms-filter: progid:DXImageTransform.Microsoft.gradient(startColorStr=#ffb13a, endColorStr=#ffcb65);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorStr=#ffb13a, endColorStr=#ffcb65);
            text-align: center;
        }
        
        input.q_pro, input.stat_name, textarea.desq {
            border: 1px solid rgb(211, 211, 211) !important;
            border-top: 1px solid rgb(163, 163, 163) !important;
        }
        
        #eto_ofr_ftr_frm {
            padding-bottom: 18px !important;
        }
        
        .sh {
            font-size: 12px;
            font-weight: bold;
            margin: 0px 13px 0px 13px;
            padding: 3px 0 8px 0;
            border-bottom: 1px dotted #bebebe;
        }
        
        .tx_h {
            background-image: url(images/fform-main10.png);
            background-repeat: no-repeat;
        }
        
        .doff {
            display: none;
        }
        
        #buy_alert_msg {
            background: #fffdea;
            width: 700px;
            position: fixed;
            top: 50%;
            left: 50%;
            font-family: arial;
            font-size: 14px;
            padding: 3px 10px 10px 10px;
            line-height: 23px;
            border: 4px solid #e4be75;
            z-index: 99;
            margin-left: -300px;
            margin-top: -130px;
        }
    </style>
    
    <script type="text/javascript">
        function purchaseTender(id) {
            if (confirm('Are you sure you want to buy this Tender?')) {
                $.post("ajax-file/purchaseTender.php", {id: id}, function(data) {
                    $("#buy_alert_msg").removeClass("doff");
                }).fail(function() {
                    alert('Failed to purchase tender. Please try again.');
                });
            }
        }
        
        function openAlertClose() {
            window.location.reload();
        }
        
        function showMessage() {
            alert('Please purchase credits to buy this Tender.');
        }
        
        function choosePackage(id) {
            window.location.href = "payment-option.php?id=" + id;
        }
        
        function sendEnquiry() {
            var msg_message = document.getElementById('msg_message');
            
            if (!msg_message.value || msg_message.value.trim() === '') {
                alert('Please fill in your enquiry.');
                msg_message.focus();
                return false;
            }
            
            if (msg_message.value.length < 20) {
                alert('Enquiry must be at least 20 characters long.');
                msg_message.focus();
                return false;
            }
            
            $("#enqloading").css("display", "block");
            $("#enqloading1").css("display", "none");
            
            $.post("ajax-file/sendMessage.php", {
                lead_headline: $("#lead_headline").text(),
                msg_from: $("#msg_from").val(),
                msg_to: $("#msg_to").val(),
                msg_subject: $("#msg_subject").val(),
                msg_message: msg_message.value
            }, function(data) {
                setTimeout(function() {
                    if (data == 1) {
                        alert('Your enquiry has been sent successfully');
                        msg_message.value = "";
                    } else {
                        alert('Your enquiry could not be sent. Please try again later.');
                    }
                    $("#enqloading").css("display", "none");
                    $("#enqloading1").css("display", "block");
                }, 500);
            }).fail(function() {
                alert('Failed to send enquiry. Please try again.');
                $("#enqloading").css("display", "none");
                $("#enqloading1").css("display", "block");
            });
        }
    </script>
</head>
<body>
    <div class="q_hm1">
        <!-- Header -->
        <?php include "includes/header_login.php"; ?>
        
        <div class="q_bt"><img src="images/zero.gif" alt="<?php echo htmlspecialchars(getWebSiteName()); ?>" height="1" width="1"></div>
        
        <p class="q_c3"></p>
        
        <div class="inner_wrapper">
            <div align="CENTER">
                <div style="width:100%;">
                    <div class="p3 pl lf mm">
                        <a href="tenders.php" class="c12 td">Tenders</a> &nbsp;&gt; &nbsp;
                        <?php if (!empty($category_path['main_id'])): ?>
                            <a href="category.php?token=<?php echo rand(1000,9999) . md5((string)$category_path['main_id']); ?>" class="c12" style="text-decoration:none">
                                <?php echo htmlspecialchars(ucwords($category_path['main_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            &nbsp;&gt;&nbsp;
                            <a href="catcompany.php?token=<?php echo rand(1000,9999) . md5((string)$category_path['cat_id']); ?>" class="c12" style="text-decoration:none">
                                <?php echo htmlspecialchars(ucwords($category_path['cat_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            &nbsp;&gt;&nbsp;
                            <?php echo htmlspecialchars(ucwords($category_path['subcat_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                        <br>
                    </div>
                </div>
                
                <div style="float:left; width:70%; text-align:left">
                    <div class="e5 lbx" style="margin-bottom:4px;" id="lftdsc">
                        <h1 class="f6 cl2" id="lead_headline" style="display:inline">
                            <?php echo htmlspecialchars($row->tnd_heading ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </h1>
                        
                        <?php if (!empty($row->tnd_preferred_location)): ?>
                            - <span class="f5">
                                <?php
                                $location = $row->tnd_preferred_location;
                                if ($location == 'any') {
                                    echo "Anywhere";
                                } elseif ($location == 'abroad') {
                                    echo "Foreign";
                                } elseif ($location == 'domestic') {
                                    echo htmlspecialchars(get_country_name((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                    <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" alt="" height="16" width="24">&nbsp;&nbsp;
                                <?php } elseif ($location == 'my_city' && !empty($row->bnsprof_city) && $row->bnsprof_city != '0') {
                                    echo htmlspecialchars(get_city_name((int)$row->bnsprof_city), ENT_QUOTES, 'UTF-8');
                                }
                                ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php
                        $cid = rand(1000,9999) . md5((string)($row->bnsprof_id ?? ''));
                        if (!empty($row->bnsprof_compname) && $uid > 0):
                            $sql_icon = "SELECT sip.mst_icon, sip.mst_name 
                                        FROM smembership_icon_plan sip 
                                        JOIN user u ON sip.mp_id = u.usr_mp_id 
                                        WHERE u.usr_id = $uid";
                            $get_icon = mysqli_query($con, $sql_icon);
                            
                            if ($get_icon && mysqli_num_rows($get_icon) > 0):
                                $icon = mysqli_fetch_assoc($get_icon);
                                $title = 'Junior';
                                
                                if (stripos($icon['mst_name'] ?? '', 'senior') !== false) {
                                    $title = 'Senior';
                                } elseif (stripos($icon['mst_name'] ?? '', 'sponsor') !== false || 
                                          stripos($icon['mst_name'] ?? '', 'sponser') !== false) {
                                    $title = 'Sponsor';
                                }
                        ?>
                                <span>
                                    <?php if ($title == 'Junior'): ?>
                                        <img src="admin/images/<?php echo htmlspecialchars($icon['mst_icon'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                             title="<?php echo strtoupper($title); ?>" 
                                             style="width:18px; height:15px; border:0;" alt=""/>
                                    <?php else: ?>
                                        <a href="company/index.php?c=<?php echo htmlspecialchars($cid, ENT_QUOTES, 'UTF-8'); ?>">
                                            <img src="admin/images/<?php echo htmlspecialchars($icon['mst_icon'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                 title="<?php echo strtoupper($title); ?>" 
                                                 style="width:18px; height:15px; border:0;" alt=""/>
                                        </a>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <span class="vlogoB1 tooltip2 valb mb1">
                            <span class="g9 d1" style="font-weight:bold; padding:0px 2px 0px 21px; line-height:19px; display:inline-block; background:#0095f9 url('images/verified-sign.jpg') left no-repeat;">
                                Verified &amp; Updated
                            </span>
                        </span>
                        
                        <div style="padding-bottom:4px; margin-top:12px">
                            <p style="color: rgb(185, 184, 184); float:right; text-align:right;" class="j1 cb">
                                <font style="color: rgb(152, 151, 151);">Tender Publish Date :</font> 
                                <?php echo !empty($row->tnd_publish_date) ? date("d M, Y", strtotime($row->tnd_publish_date)) : 'N/A'; ?>
                            </p>
                            <div style="clear:both;"></div>
                        </div>
                        
                        <span class="c12 bo fs" style="font-size:14px">Tender Details:</span>
                        <div class="bdt" id="hdiv1" style="padding-top:5px">
                            <div class="g2 fs k7">
                                <?php if (!empty($row->tnd_value) && $row->tnd_value != '0' && $row->tnd_value != '0.00'): ?>
                                    <div><span class="c13"><strong>Tender Value </strong>: <?php echo number_format((float)$row->tnd_value, 2) . " " . htmlspecialchars(getCurrency((int)($row->tnd_currency ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($row->tnd_notice_type)): ?>
                                    <div><span class="c13"><strong>Notice Type </strong>: <?php echo htmlspecialchars($row->tnd_notice_type, ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($row->tnd_qty) && $row->tnd_qty != '0'): ?>
                                    <div><span class="c13"><strong>Quantity </strong>: <?php echo (float)$row->tnd_qty . " " . htmlspecialchars(measurement_unit((int)($row->tnd_qty_mu_id ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($row->tnd_emd)): ?>
                                    <div><span class="c13"><strong>EMD </strong>: <?php echo htmlspecialchars($row->tnd_emd, ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($row->tnd_document_fees) && $row->tnd_document_fees != '0' && $row->tnd_document_fees != '0.00'): ?>
                                    <div><span class="c13"><strong>Document Fees </strong>: <?php echo number_format((float)$row->tnd_document_fees, 2) . " " . htmlspecialchars(getCurrency((int)($row->tnd_document_fees_currency ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($row->tnd_project_period)): ?>
                                    <div><span class="c13"><strong>Project Period </strong>: <?php echo htmlspecialchars($row->tnd_project_period, ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($row->tnd_products)): ?>
                                    <div><span class="c13"><strong>Products </strong>: <?php echo htmlspecialchars($row->tnd_products, ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <?php endif; ?>
                                
                                <br>
                                
                                <?php if (!empty($row->tnd_preferred_location)): ?>
                                    <div><span class="c13"><strong>Preferred location </strong>: 
                                        <?php
                                        $location = $row->tnd_preferred_location;
                                        if ($location == 'any') {
                                            echo "Anywhere";
                                        } elseif ($location == 'abroad') {
                                            echo "Foreign";
                                        } elseif ($location == 'domestic') {
                                            echo htmlspecialchars(get_country_name((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                            &nbsp;<img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" alt="" height="16" width="24">
                                        <?php } elseif ($location == 'my_city' && !empty($row->bnsprof_city) && $row->bnsprof_city != '0') {
                                            echo htmlspecialchars(get_city_name((int)$row->bnsprof_city), ENT_QUOTES, 'UTF-8');
                                        }
                                        ?>
                                    </span></div>
                                    <br>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <span class="bdd bo" style="font-size:14px"><span class="artb sbg"></span> Important Dates</span>
                        <div class="bdt" id="hdiv1" style="padding-top:5px">
                            <div class="g2 fs k7">
                                <div class="c15 pt4 f1 pl">
                                    <?php if (!empty($row->tnd_publish_date) && $row->tnd_publish_date != '0000-00-00'): ?>
                                        <div><span class="c13"><strong>Publish Date </strong>: <?php echo date("d-M, Y", strtotime($row->tnd_publish_date)); ?></span></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($row->tnd_docSaleStart_date) && $row->tnd_docSaleStart_date != '0000-00-00'): ?>
                                        <div><span class="c13"><strong>Document Sale Starts </strong>: <?php echo date("d-M, Y", strtotime($row->tnd_docSaleStart_date)); ?></span></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($row->tnd_docSaleEnd_date) && $row->tnd_docSaleEnd_date != '0000-00-00'): ?>
                                        <div><span class="c13"><strong>Document Sale Ends </strong>: <?php echo date("d-M, Y", strtotime($row->tnd_docSaleEnd_date)); ?></span></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($row->tnd_docSubmitBefore_date) && $row->tnd_docSubmitBefore_date != '0000-00-00'): ?>
                                        <div><span class="c13"><strong>Document Submit Before </strong>: <?php echo date("d-M, Y", strtotime($row->tnd_docSubmitBefore_date)); ?></span></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($row->tnd_due_date) && $row->tnd_due_date != '0000-00-00'): ?>
                                        <div><span class="c13"><strong>Due Date </strong>: <?php echo date("d-M, Y", strtotime($row->tnd_due_date)); ?></span></div>
                                    <?php endif; ?>
                                </div>
                                <br>
                            </div>
                        </div>
                        
                        <span class="bdd bo" style="font-size:14px"><span class="artb sbg"></span> Pre-qualification Criteria</span>
                        <div class="bdt" id="hdiv1" style="padding-top:5px">
                            <div class="g2 fs k7">
                                <div class="c15 pt4 f1 pl">
                                    <div><span class="c13"><?php echo nl2br(htmlspecialchars(stripslashes($row->tnd_prequalification_criteria ?? ''), ENT_QUOTES, 'UTF-8')); ?></span></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($row->tnd_details)): ?>
                            <span class="bdd bo" style="font-size:14px"><span class="artb sbg"></span> Detail Description</span>
                            <div class="bdt" id="hdiv1" style="padding-top:5px">
                                <div class="g2 fs k7">
                                    <div class="c15 pt4 f1 pl">
                                        <div><span class="c13"><?php echo nl2br(htmlspecialchars(stripslashes($row->tnd_details), ENT_QUOTES, 'UTF-8')); ?></span></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        $sql_af = "SELECT af.*, tav.tav_value 
                                  FROM tender_additional_value tav
                                  JOIN additional_field af ON tav.tav_af_id = af.af_id
                                  WHERE tav.tav_tnd_id = " . (int)$row->tnd_id . "
                                  GROUP BY af.af_id";
                        $res_af = mysqli_query($con, $sql_af);
                        
                        if (mysqli_num_rows($res_af) > 0):
                        ?>
                            <span class="bdd bo" style="font-size:14px"><span class="artb sbg"></span> Additional Information</span>
                            <div class="bdt" id="hdiv1" style="padding-top:5px">
                                <div class="g2 fs k7">
                                    <div class="c15 pt4 f1 pl">
                                        <?php while ($row_af = mysqli_fetch_object($res_af)): ?>
                                            <div>
                                                <span class="c13">
                                                    <strong><?php echo htmlspecialchars(stripslashes($row_af->af_label ?? ''), ENT_QUOTES, 'UTF-8'); ?> </strong>: 
                                                    <?php
                                                    $sql_tav = "SELECT tav_value FROM tender_additional_value 
                                                               WHERE tav_af_id = " . (int)($row_af->tav_af_id ?? 0) . " 
                                                               AND tav_tnd_id = " . (int)$row->tnd_id;
                                                    $res_tav = mysqli_query($con, $sql_tav);
                                                    $values = [];
                                                    while ($row_tav = mysqli_fetch_object($res_tav)) {
                                                        $values[] = stripslashes($row_tav->tav_value);
                                                    }
                                                    echo htmlspecialchars(implode(', ', $values), ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                </span>
                                            </div>
                                            <br>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="clear:both;"></div>
                
                <!-- Purchase Alert Message -->
                <div class="doff" id="buy_alert_msg">
                    <div style="padding-top:10px">
                        <b>Thank You for Purchasing the Tender!</b><br>
                        <div style="padding-left:5px; padding-top:3px">
                            &#8226; This Tender is saved in your "<a href="manage-purchased-tenders.php">Purchased Tenders</a>" section
                            
                            <?php if ($is_promo_plan): ?>
                                <div style="height:10px; overflow:hidden"></div>
                                &#8226; You can submit your response to this Buyer from "<a href="manage-purchased-tenders.php">Purchased Tenders</a>"
                                <div style="height:10px; overflow:hidden"></div>
                                &#8226; This purchase will reflect in your "<a href="transaction_history.php">Transaction History</a>" as well
                            <?php endif; ?>
                            
                            <div style="height:10px; overflow:hidden"></div>
                            &#8226; Write us at <a href="contact_us.php">Contact Us Page</a> within 7 days of purchase incase:
                            <div style="font-size:12px; padding-left:10px">
                                - You are unable to contact the buyer (Buyer's email id as well as phone number are wrong)<br>
                                - Buyer's requirement fulfilled before lead purchase
                            </div>
                        </div>
                    </div>
                    <div style="padding-top:5px" align="center">
                        <input onclick="openAlertClose()" value="OK" style="font-size:16px; font-weight:bold" type="button">
                    </div>
                </div>
            </div>
            
            <!-- Right Panel -->
            <div class="wd9 lf gv" style="float:right;">
                <?php if ($uid > 0): ?>
                    <div class="lgnDtl k7 mb3">
                        <div class="f5 bdd">
                            Welcome <?php echo htmlspecialchars(user_info($uid, 'name_prefix') . " " . user_info($uid, 'fname'), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        
                        <?php if (!$is_promo_plan): ?>
                            <span class="alrt">
                                <span class="awa sbg"></span>
                                <span class="c13 bo pl3" name="prcrdt" id="prcrdt">
                                    <a href="membership_plans.php" style="color: rgb(51, 51, 51);">Pay Annual Subscription</a>
                                </span>
                            </span>
                        <?php elseif ($user_credit < 20): ?>
                            <span class="alrt">
                                <span class="awa sbg"></span>You do not have any credit in your account!<br>
                                <span class="c13 bo pl3" name="prcrdt" id="prcrdt">
                                    <a href="subscription.php" style="color: rgb(51, 51, 51);">Purchase Credits now</a>
                                </span>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div id="rtmain" class="lbx1" style="background-color:#FAF4FF;">
                    <p class="sbg d_bp1 bo f1">
                        <?php echo !empty($row->tnd_publish_date) ? date("d M, Y", strtotime($row->tnd_publish_date)) : 'N/A'; ?>
                        <span class="x2 date_tp tooltip2 qmart sbg" 
                              title="Tender Last Updated on: <?php echo !empty($row->tnd_publish_date) ? date("d M, Y", strtotime($row->tnd_publish_date)) : 'N/A'; ?> ### Publish Date: <?php echo !empty($row->tnd_publish_date) ? date("d M, Y", strtotime($row->tnd_publish_date)) : 'N/A'; ?>">
                            &nbsp;
                        </span>
                    </p>
                    
                    <div class="ef3">
                        <?php if (empty($row->tnd_preferred_supplier_location)): ?>
                            <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="" align="left" height="16" width="24">
                            <span class="e4 f1 wb e5">&nbsp;<b><?php echo htmlspecialchars(get_country_name((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?></b></span>
                        <?php else: ?>
                            <span class="e4 f1 wb e5">&nbsp;<b>
                                <?php
                                $location = $row->tnd_preferred_supplier_location;
                                if ($location == 'any') {
                                    echo "Anywhere";
                                } elseif ($location == 'abroad') {
                                    echo "Foreign";
                                } elseif ($location == 'domestic') {
                                    echo htmlspecialchars(get_country_name((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                    &nbsp;&nbsp;<img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" alt="" align="left" height="16" width="24">
                                <?php } elseif ($location == 'my_city' && !empty($row->bnsprof_city) && $row->bnsprof_city != '0') {
                                    echo htmlspecialchars(get_city_name((int)$row->bnsprof_city), ENT_QUOTES, 'UTF-8');
                                }
                                ?>
                            </b></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($uid > 0 && $uid != $row->tnd_usr_id): ?>
                        <?php if ($purchased): ?>
                            <div id="sourcediv1">
                                <div class="mt12 l1 k7 mb">
                                    <div class="btn1 point mt12 f4" id="buybtn" style="line-height:20px; padding:5px 27px;" 
                                         onclick="purchaseTender(<?php echo (int)$row->tnd_id; ?>);">
                                        <span class="f1">Purchased on: <?php echo !empty($purchase_date) ? date("d M, Y", strtotime($purchase_date)) : 'N/A'; ?></span>
                                        <div class="inAr sbg"></div>
                                    </div>
                                    <div id="tps" class="doff sbg g1 k7">
                                        Purchasing of this Lead will make the full contact details visible to you for sending response to Buyer
                                    </div>
                                </div>
                            </div>
                            
                            <div style="position:relative;" id="rtmain1">
                                <div class="rit_ar" style="width:267px; margin-left:-22px;">
                                    <div id="topref" itemscope="">
                                        <p class="m2"></p>
                                        <p class="tr mr6 w1">
                                            <?php
                                            if (!empty($row->bnsprof_yoe) && $row->bnsprof_yoe > 0):
                                                $yr_diff = (int)date("Y") - (int)$row->bnsprof_yoe;
                                                if ($yr_diff > 0):
                                            ?>
                                                <span title="<?php echo $yr_diff; ?> year of Membership" class="opacity b1 vam mems tc">
                                                    <span title="<?php echo $yr_diff; ?> year of Membership" class="sp-mem1">
                                                        <?php echo $yr_diff; ?><span class="sp-mem2">yr</span>
                                                    </span>
                                                </span>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </p>
                                        
                                        <?php if (!$is_promo_plan): ?>
                                            <p class="vcl txl ef3 lh21 pr2 rd f2 bo ml27">
                                                <?php echo htmlspecialchars(stripslashes($row->bnsprof_compname ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if (!empty($row->bnsprof_yoe) && $row->bnsprof_yoe != '0'): ?>
                                                    <span class="estd" style="margin-top:3px">
                                                        (Estd. <span style="margin-left:5px"><?php echo (int)$row->bnsprof_yoe; ?></span>)
                                                    </span>
                                                <?php endif; ?>
                                            </p>
                                            
                                            <p itemprop="address" itemscope="" class="txl txt1 vcl3 mt5 cn_cl ml27 lh21">
                                                <?php 
                                                echo htmlspecialchars(
                                                    trim(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? '')),
                                                    ENT_QUOTES, 'UTF-8'
                                                ); ?>
                                                <br>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($row->country) && $row->country != '0'): ?>
                                            <span itemprop="addressCountry">
                                                <?php echo htmlspecialchars(get_country_name((int)$row->country), ENT_QUOTES, 'UTF-8'); ?>
                                                &nbsp;<img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag((int)$row->country), ENT_QUOTES, 'UTF-8'); ?>" 
                                                          alt="" class="w4" align="top" height="15" width="23">
                                            </span>
                                        <?php endif; ?>
                                        
                                        <div id="sourcediv1">
                                            <div class="mt12 l1 k7 mb">
                                                <?php if (!$is_promo_plan && !empty($row->mobile1) && $row->mobile1 != '0'): ?>
                                                    <p class="mt2 ml27 cn_cl">
                                                        <span class="sbg ph a1"></span>
                                                        <span itemprop="telephone">
                                                            +<?php echo htmlspecialchars(($row->country_ph_code ?? '') . '-' . $row->mobile1, ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if ($uid > 0 && $uid != $row->usr_id): ?>
                                                    <div name="logein" id="TP" class="form-container" style="margin-left:6px;">
                                                        <a id="clx" style="display:none" class="clx1" href="#TP">
                                                            <div id="cls" class="form-close">
                                                                <img alt="" src="images/zero.gif" class="bg close-image" border="0" height="16" width="16">
                                                            </div>
                                                        </a>
                                                        <p class="form-caption">Send E-mail Enquiry</p>
                                                        
                                                        <div class="form-block">
                                                            <p class="form-tagname">Message:</p>
                                                            <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $uid; ?>">
                                                            <input type="hidden" id="msg_to" name="msg_to" value="<?php echo (int)$row->usr_id; ?>">
                                                            <input type="hidden" id="msg_subject" name="msg_subject" value="Enquiry for Tenders">
                                                            <textarea id="msg_message" name="msg_message" class="form-textarea" style="width:242px; height:100px;"></textarea>
                                                        </div>
                                                        
                                                        <div id="enqloading1">
                                                            <input onclick="sendEnquiry();" class="point sndb" 
                                                                   value="Contact this Supplier NOW!" 
                                                                   style="padding:5px 3px; margin-left:6px; margin-right:0px; width:240px; font-size:16px;" 
                                                                   type="button">
                                                        </div>
                                                        
                                                        <div style="text-align:center; display:none; margin:14px 0;" class="bo" id="enqloading">
                                                            <img src="images/indicator.gif" align="absmiddle">&nbsp;Processing...
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <p class="c3"></p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if ($is_promo_plan): ?>
                                <div id="sourcediv1">
                                    <div class="mt12 l1 k7 mb">
                                        <div class="btn1 point mt12 f4" id="buybtn" style="line-height:20px; padding:5px 27px;" 
                                             onclick="purchaseTender(<?php echo (int)$row->tnd_id; ?>);">
                                            View Tender Information<br>
                                            <span class="f1">Buy this Tender Now</span>
                                            <div class="inAr sbg"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div id="sourcediv1">
                                    <div class="mt12 l1 k7 mb">
                                        <div class="btn1 point mt12 f4" id="buybtn" style="line-height:20px; padding:5px 27px;" 
                                             <?php if ($credit_available): ?>
                                                 onclick="purchaseTender(<?php echo (int)$row->tnd_id; ?>);"
                                             <?php else: ?>
                                                 onclick="showMessage();"
                                             <?php endif; ?>>
                                            View Tender Information<br>
                                            <span class="f1">Buy this Tender Now</span>
                                            <div class="inAr sbg"></div>
                                            <div id="tps" class="doff sbg g1 k7">
                                                After Purchasing this Tender, Authority Contact Details and full information will be visible to you
                                            </div>
                                        </div>
                                    </div>
                                    <div class="f3 mt11">
                                        in <strong class="z6 f4">20 Credits</strong>
                                    </div>
                                    Lead will make the full contact details visible to you for sending response to Buyer
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Credit Plans -->
                <?php if ($user_credit == 0 && $uid > 0): ?>
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td>
                                <div class="c13" id="pkg" style="margin-top:20px; text-align:center;">
                                    <h2 class="f4 ts1 w3">
                                        Select Credit Plan
                                        <span class="x2 date_tp tooltip4 qmart1 sbg" 
                                              title="Credit Plans consists of Credits which you will need to contact the buyer. These Credits will be added into your account, once you purchase any package.">
                                            &nbsp;
                                        </span>
                                    </h2>
                                    <div class="pkg">
                                        <?php
                                        $sql_mp = "SELECT * FROM membership_plan WHERE mp_status='1'";
                                        $res_mp = mysqli_query($con, $sql_mp);
                                        while ($row_mp = mysqli_fetch_object($res_mp)):
                                        ?>
                                            <p class="c13 bdd" style="line-height:26px; font-size:16px; text-align:center !important;">
                                                <?php echo htmlspecialchars($row_mp->mp_name ?? '', ENT_QUOTES, 'UTF-8'); ?><br>
                                                <span class="c12 bo f3">
                                                    <?php echo (int)($row_mp->mp_credits ?? 0); ?> Credits for 
                                                    <span class="WebRupee f4"><?php echo htmlspecialchars(getCurrencySymbol(), ENT_QUOTES, 'UTF-8'); ?></span> 
                                                    <?php echo number_format((float)($row_mp->mp_amount ?? 0), 2); ?>
                                                </span><br>
                                                <a onclick="choosePackage('<?php echo rand(10000, 99999) . md5((string)$row_mp->mp_id); ?>');" 
                                                   class="point" style="font-size:14px; padding:2px 8px; background:#0e4ec7; color:#fff; text-decoration:none; margin:5px auto 10px; display:inline-block; width:66px">
                                                    Buy Now
                                                </a>
                                            </p>
                                        <?php endwhile; ?>
                                    </div>
                                    <div class="bsSd sbg"></div>
                                </div>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>
                
                <div class="n1 n2 z1">
                    <span id="gright"><div class="bxr w1 w3"></div></span>
                </div>
            </div>
        </div>
        
        <div class="m2"></div>
    </div>
    
    <div style="clear:both;"><br></div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
<?php ob_end_flush(); ?>