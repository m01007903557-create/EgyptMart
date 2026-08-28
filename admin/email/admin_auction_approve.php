<?php
/**
 * File: email/admin_auction_approve.php

 * Version: PHP 8.3
 * Description: قالب البريد الإلكتروني لإشعار الموافقة على المزاد
 * 
 * هذا الملف يحتوي على قالب البريد الإلكتروني الذي يتم إرساله للمستخدم
 * عند الموافقة على المزاد الخاص به ونشره على المنصة
 */
 
 echo "<!-- المسار الحالي: " . __DIR__ . " -->";
echo "<!-- هل الملف موجود؟ " . (file_exists(__DIR__ . '/../../lib/function.php') ? 'نعم' : 'لا') . " -->";
require_once __DIR__ . '/../../lib/function.php';
// التحقق من وجود المتغيرات الأساسية
$current_date = date('M d, Y');
$random_token = rand(1000, 9999);

// اسم المستخدم
$user_name = isset($suname) ? htmlspecialchars($suname) : 'المستخدم';

// بيانات المزاد
$auction_id = isset($sproduct->auc_id) ? (int)$sproduct->auc_id : 0;
$auction_token = $random_token . md5((string)$auction_id);
$auction_link = "https://egyptmart.shop/auction-details.php?id=" . $auction_token;

// معلومات الموقع
$user_email = isset($suser->email) ? urlencode($suser->email) : '';

// بناء قالب البريد الإلكتروني
$comment = "<div style='width: 628px;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;'>";

// رأس البريد
$comment .= "<div style='height: 100px; width: 100%; float: left; '>";
$comment .= "<div style='height: 100px; width: 30%; float: left;'>";
$comment .= "<img src='https://egyptmart.shop/images/logo.png' style='width: 100%;color: #00F;font-size: 22px;font-weight: bold;' alt='EgyptMART'>";
$comment .= "</div>";
$comment .= "<div style='height:100px;width:43%;float:left;'><h2 style='font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;'>Today's Latest<br> Auctions Approval</h2></div>";
$comment .= "<div style='min-height: 100px; width: 27%; float: right; padding-top: 3px;'>";
$comment .= "<span style='font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;'> Notification</span>";
$comment .= "<span style='float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;'>" . $current_date . "</span>";
$comment .= "</div></div>";

// تحية المستخدم
$comment .= "<div style='width:100%;float:left;color:#000000;'>";
$comment .= "<p style='font-size:16px;color:#000000'><strong>Dear " . $user_name . "</strong>,<br><br>Your below auction was approved to be live on EgyptMART:</p>";
$comment .= "</div>";

$comment .= "<div  style='height:auto;width:100%;float:left; margin-top:10px;'>";
$comment .= "<div style='height:auto;width:100%;float:left;'>";

// عنوان المزاد
$product_title_display = isset($product_title) ? htmlspecialchars($product_title) : '';
$comment .= "<div style='width:100%;height:auto;float:right;'>";
$comment .= "<div style='width:100%;float:left;font-size:18px;font-weight:bold;color:#466da0;padding-left:0px;text-align: center;text-transform: uppercase;background-color: #F2F241;'>" . $product_title_display . "</div>";

// تفاصيل المزاد
$comment .= "<div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left: 0px;padding-top: 10px;color:#000000;'>";
$comment .= "<div style='padding-left:0px;width:31%; float:left;'>Location<span style='text-align:right; float:right;'>:</span></div>";
$location = isset($suser->cn_name) ? htmlspecialchars($suser->cn_name) : '';
$location .= isset($suser->state_name) ? ' / ' . htmlspecialchars($suser->state_name) : '';
$location .= isset($suser->ct_name) ? ' / ' . htmlspecialchars($suser->ct_name) : '';
$comment .= "<div style='color:#000;padding-left:5px;width:64%; float:left;'>" . $location . "</div>";
$comment .= "</div>";

// تاريخ الاستحقاق
$due_date = isset($sproduct->auc_due_date) ? date('d M, Y', strtotime($sproduct->auc_due_date)) : '';
$comment .= "<div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left: 0px;padding-top: 10px;color:#000000;'>";
$comment .= "<div style='padding-left:0px;width:31%; float:left;'>Due Date<span style='text-align:right; float:right;'>:</span></div>";
$comment .= "<div style='color:#e9582c; line-height:15px;font-size:15px;font-weight:bold;padding-left:5px;width:auto; float:left;'>" . $due_date . "</div>";
$comment .= "<div style='color:#000;padding-left:5px;width:40%; float:left;'></div>";
$comment .= "</div>";

// قيمة المزاد
$auc_value = isset($sproduct->auc_value) ? htmlspecialchars($sproduct->auc_value) : '';
$auc_currency = isset($sproduct->auc_currency) ? htmlspecialchars(getCurrency($sproduct->auc_currency)) : '';
$comment .= "<div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left:0px;padding-top: 10px;color:#000000;'>";
$comment .= "<div style='padding-left:0px;width:31%; float:left;'>Value<span style='text-align:right; float:right;'>:</span></div>";
$comment .= "<div style='color:#e9582c; line-height:15px;font-size:15px;font-weight:bold;padding-left:5px;width:24%; float:left;'>" . $auc_value . "</div>";
$comment .= "<div style='color:#000;padding-left:5px;width:40%; float:left;'>" . $auc_currency . "</div>";
$comment .= "</div>";

// الكمية
$auc_qty = isset($sproduct->auc_qty) ? htmlspecialchars($sproduct->auc_qty) : '';
$auc_unit = isset($sproduct->auc_qty_mu_id) ? htmlspecialchars(measurement_unit($sproduct->auc_qty_mu_id)) : '';
$comment .= "<div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left:0px;padding-top: 10px;color:#000000;'>";
$comment .= "<div style='padding-left:0px;width:31%; float:left;'>Quantity<span style='text-align:right; float:right;'>:</span></div>";
$comment .= "<div style='color:#e9582c;font-weight:bold;padding-left:5px;width:24%; float:left;font-size:15px;line-height:15px;'>" . $auc_qty . "</div>";
$comment .= "<div style='color:#000;padding-left:5px;width:40%; float:left;'>" . $auc_unit . "</div>";
$comment .= "</div>";

// مدة المشروع
$project_period = isset($sproduct->auc_project_period) ? htmlspecialchars($sproduct->auc_project_period) : '';
$comment .= "<div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left:0px;padding-top: 10px;color:#000000;'>";
$comment .= "<div style='padding-left:0px;width:31%; float:left;'>Project Period<span style='text-align:right; float:right;'>:</span></div>";
$comment .= "<div style='color:#e9582c;font-weight:bold;padding-left:5px;width:24%; float:left;font-size:15px;line-height:15px;'>" . $project_period . "</div>";
$comment .= "<div style='color:#000;padding-left:5px;width:40%; float:left;'></div>";
$comment .= "</div>";

// رابط المزيد
$comment .= "<div style='height:16%;width:97%;float:left;font-size:12px;font-weight:bold;text-align:right;padding-top:15px;padding-right:0px;'>";
$comment .= "<a href='" . $auction_link . "' style='text-decoration:none;color:#466da0;padding-right: 2px;'>Learn More >></a>";
$comment .= "</div></div></div>";

// تفاصيل الاتصال
$comment .= "<div style='width:100%;height:auto;float:right;'>";
$comment .= "<p style='line-height:1.5em;text-align:left;font-size:1.4em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em;color: #002757;'>Contact Details :</p>";
$comment .= "</div>";

$contact_details_display = isset($contact_details) ? $contact_details : '';
$comment .= "<div style='width: 100%; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em'>";
$comment .= $contact_details_display;
$comment .= "<div style='width: 90%; float: left;'>";
$comment .= "<span style='font-size:1.0em;font-weight:normal'></span>";
$comment .= "</div>";
$comment .= "</div>";

// معلومات تسجيل الدخول
$comment .= "<div>";
$comment .= "<p style='line-height:1.5em;text-align:left;font-size:1.4em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em;color: #002757;'>Not Signed in Before Yet ? .. </p>";
$comment .= "<div style='color: #000;'>Use your current mail address + default password: 123456  ";
$comment .= "<a href='https://egyptmart.shop/sign-in.php?email=" . $user_email . "' style='text-decoration:none;margin-left: 50px;font-size: 14px;'>Sign in NOW</a>";
$comment .= "</div>";

// روابط سريعة
$comment .= "<div>";
$comment .= "<p style='color: #000000;font-size: 19px;font-weight: 900;background-color: #eaeaea;padding: 10px;  margin-bottom: 5px;'> You may need to post <strong style='color:#da4e1e;font-size: 19px;font-weight: 900'>FREE</strong>:</p>";
$comment .= "<table align='center'>";
$comment .= "<tr>";
$comment .= "<td style='padding: 5px;'><a href='https://egyptmart.shop/sign-in.php?email=" . $user_email . "&redirect=https://egyptmart.shop/product-sel-cat.php' style='padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;'>Products/Services</a></td>";
$comment .= "<td style='padding: 5px;'><a href='https://egyptmart.shop/sign-in.php?email=" . $user_email . "&redirect=https://egyptmart.shop/post-buy-req.php' style='padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;'>Buy Requirements</a></td>";
$comment .= "</tr>";
$comment .= "<tr>";
$comment .= "<td style='padding: 5px;'><a href='https://egyptmart.shop/sign-in.php?email=" . $user_email . "&redirect=https://egyptmart.shop/post-sell-offer.php' style='padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;'>Temporary Sale Offer</a></td>";
$comment .= "<td style='padding: 5px;'><a href='https://egyptmart.shop/sign-in.php?email=" . $user_email . "&redirect=https://egyptmart.shop/post-tender.php' style='padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;'>Tenders / Auctions</a></td>";
$comment .= "</tr>";
$comment .= "</table>";
$comment .= "</div>";

// رابط إلغاء الاشتراك
$comment .= "<div style='clear:both'>";
$comment .= "<p style='line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em'>";
$comment .= "<a href='https://egyptmart.shop/sign-in.php?email=" . $user_email . "&redirect=https://egyptmart.shop/why_egyptmart.php' style='font-size: 12px;font-weight: normal;'>Click Here</a> to unsubscribe or tell us your requirements ";
$comment .= "<a href='https://egyptmart.shop/sign-in.php?email=" . $user_email . "&redirect=https://egyptmart.shop/membership_plans.php'>";
$comment .= "<strong style='color: #0f00d0;font-size: 10px;font-weight: 600;'>NOW!</strong></a>";
$comment .= "</p>";
$comment .= "</div>";

// خط فاصل
$comment .= "<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";

// روابط سريعة
$comment .= "<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'>";
$comment .= "<a href='https://egyptmart.shop/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Product & Suppliers</a> | ";
$comment .= "<a href='https://egyptmart.shop/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Sale Offers</a> | ";
$comment .= "<a href='https://egyptmart.shop/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Buy Requests</a> | ";
$comment .= "<a href='https://egyptmart.shop/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Tenders</a> | ";
$comment .= "<a href='https://egyptmart.shop/auctions.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Auction</a>";
$comment .= "</div>";

// معلومات الاتصال
$comment .= "<div style='width:100%;padding-left: 0px;float:left;color:#808080;'>";
$comment .= "<p style='margin:10px 0px 2px;font-size:12px;'>For more assistance, feel free to call us at 201030029097.</p>";
$comment .= "</div><br/><br/>";

// توقيع
$comment .= "<div style='width:100%;padding-left: 0px;text-align:center;color:#808080;'>";
$comment .= "<p style='margin:10px 0px 2px;font-size:12px;'>";
$comment .= "<span style='font-size: 17px; font-weight: 600;'>Warm Regards,</span> <br/>";
$comment .= "<span><b><span style='color: blue;font-size: 14px;'> EgyptMART <span style='color: blue;font-size: 14px;'> Team</span></b></span><br/>";
$comment .= "<span style='color: #da4e1e;font-size: 17px; font-weight: 700;'>We Promote Your Business !</span>";
$comment .= "</p>";
$comment .= "</div>";

$comment .= "</div>";
?>