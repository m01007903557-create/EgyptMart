<?php
/**
 * File: send_enquiry_popup.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: نموذج إرسال استفسار للمستخدمين (نافذة منبثقة)
 * Send enquiry form popup for users
 * 
 * Features:
 * - إرسال استفسار إلى مستخدم آخر
 * - عرض معلومات المرسل أو المستلم حسب العضوية
 * - عداد الأحرف المتبقية
 * - معالجة AJAX للإرسال
 * - رسائل نجاح/خطأ
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "common.php";

// Check if user is logged in
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    echo '<div class="alert alert-warning">Please login to send enquiry</div>';
    exit;
}

// Get recipient user ID from token
$token = $_GET['id'] ?? '';
$usr_id = !empty($token) ? (int)substr($token, 4) : 0;

if ($usr_id <= 0) {
    echo '<div class="alert alert-danger">Invalid user ID</div>';
    exit;
}

// Get recipient user details
$sql = "SELECT * FROM user WHERE md5(usr_id) = ?";
$stmt = mysqli_prepare($con, $sql);
$recipient = null;

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $recipient = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt);
}

if (!$recipient) {
    echo '<div class="alert alert-danger">User not found</div>';
    exit;
}

// Get sender (current user) details
$sql_own = "SELECT u.*, bf.* FROM user u 
            LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid 
            WHERE u.usr_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql_own);
$sender = null;

if ($stmt) {
    $uid = (int)$_SESSION['uid_indm'];
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $sender = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt);
}

// Determine if sender is premium member (mp_id > 4)
$is_premium = ($sender && isset($sender->usr_mp_id) && $sender->usr_mp_id > 4);

// Format names
$sender_name = trim(($sender->name_prefix ?? '') . ' ' . ($sender->fname ?? '') . ' ' . ($sender->lname ?? ''));
$recipient_name = trim(($recipient->name_prefix ?? '') . ' ' . ($recipient->fname ?? '') . ' ' . ($recipient->lname ?? ''));
$enquiry_subject = "Enquiry from " . $sender_name;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Enquiry</title>
    <link href="css/overlay-v2.css" type="text/css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: transparent;
            direction: ltr;
        }
        
        .ov-base {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .neff2-nw {
            background: #6500CA;
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: bold;
        }
        
        .neff2-nw p {
            margin: 0;
        }
        
        .co-name {
            color: #FFD700;
            margin-right: 10px;
        }
        
        .enn1-nw {
            padding: 20px;
        }
        
        .nef4-nw {
            padding: 0 20px 20px;
        }
        
        .nef10-nw {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 2px solid #6500CA;
            border-radius: 4px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            resize: none;
        }
        
        .nef10-nw:focus {
            outline: none;
            border-color: #8a2be2;
            box-shadow: 0 0 5px rgba(101, 0, 202, 0.3);
        }
        
        .nef9-nw {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        
        fieldset {
            border: 1px solid #6500CA;
            border-radius: 4px;
            padding: 10px;
            margin: 0 20px 20px;
            width: calc(100% - 40px);
        }
        
        legend {
            font-size: 13px;
            color: #017BBC;
            font-weight: bold;
            padding: 0 10px;
        }
        
        .f1-nw ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .f1-nw ul li {
            padding: 3px 0;
            color: #055985;
            font-size: 13px;
        }
        
        .f1-nw ul li:before {
            content: "•";
            color: #6500CA;
            font-weight: bold;
            margin-right: 5px;
        }
        
        .w12 {
            width: calc(100% - 40px);
            margin: 0 20px;
            padding: 10px;
            background: #f1f1f1;
            border-bottom: 1px solid #6500CA;
            font-weight: bold;
            color: #0f5487;
            font-size: 14px;
        }
        
        .text {
            width: calc(100% - 40px);
            margin: 0 20px 20px;
            padding: 15px;
            background: #f1f1f1;
            border: 4px double #6500CA;
            border-radius: 4px;
            line-height: 1.6;
        }
        
        .snd-enq {
            background: #6500CA;
            color: white;
            border: 1px solid #6500CA;
            border-radius: 6px;
            padding: 8px 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 1px 5px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }
        
        .snd-enq:hover {
            background: #7b1fa2;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.4);
        }
        
        .snd-enq:active {
            transform: translateY(0);
        }
        
        .clr-nw {
            clear: both;
        }
        
        #loading, #succ_result, #err_result {
            text-align: center;
            padding: 10px;
            font-weight: bold;
        }
        
        #succ_result {
            color: #009700;
        }
        
        #err_result {
            color: #F00;
        }
        
        .err-msg {
            color: #F00;
            padding: 10px 20px;
            background: #ffeeee;
            border-bottom: 1px solid #ffcccc;
        }
        
        .char-count {
            font-weight: bold;
            color: #6500CA;
        }
        
        /* RTL Support */
        [dir="rtl"] .f1-nw ul li:before {
            margin-left: 5px;
            margin-right: 0;
        }
    </style>
    
    <script type="text/javascript">
        $(document).ready(function() {
            // Character counter
            $('#msg_message').on('keyup', function(e) {
                var maxLength = 2000;
                var text = $(this).val();
                var length = text.length;
                
                if (length > maxLength) {
                    $(this).val(text.substring(0, maxLength));
                    length = maxLength;
                }
                
                var remaining = maxLength - length;
                $("#charCount").text(remaining);
                
                // Change color when getting close to limit
                if (remaining < 50) {
                    $("#charCount").css('color', '#F00');
                } else if (remaining < 200) {
                    $("#charCount").css('color', '#FFA500');
                } else {
                    $("#charCount").css('color', '#6500CA');
                }
            });
        });
        
        function sendEnquiry() {
            var msg_message = document.getElementById('msg_message');
            
            if (!msg_message.value || msg_message.value.trim() === '') {
                alert('Kindly describe your enquiry.');
                msg_message.focus();
                return false;
            }
            
            if (msg_message.value.length < 50) {
                alert('Enquiry must be at least 50 characters long.');
                msg_message.focus();
                return false;
            }
            
            // Disable input and hide submit button
            msg_message.setAttribute('readonly', 'readonly');
            document.getElementById('b_sub').style.display = 'none';
            document.getElementById('loading').style.display = 'block';
            
            $.post("ajax-file/sendMessage.php", {
                msg_from: $('#msg_from').val(),
                msg_to: $('#msg_to').val(),
                msg_subject: $('#msg_subject').val(),
                msg_message: msg_message.value
            }, function(data) {
                setTimeout(function() {
                    if (data == 1) {
                        $('#loading').hide();
                        $('#succ_result').show();
                    } else {
                        $('#loading').hide();
                        $('#err_result').show();
                        
                        // Re-enable input
                        msg_message.removeAttribute('readonly');
                        $('#b_sub').show();
                    }
                }, 500);
            }).fail(function() {
                setTimeout(function() {
                    $('#loading').hide();
                    $('#err_result').show();
                    
                    // Re-enable input
                    msg_message.removeAttribute('readonly');
                    $('#b_sub').show();
                }, 500);
            });
        }
    </script>
</head>
<body>
    <div class="ov-base">
        <!-- Header -->
        <div class="neff2-nw">
            <p>Send Enquiry: <span class="co-name"><?php echo htmlspecialchars($recipient_name, ENT_QUOTES, 'UTF-8'); ?></span></p>
        </div>
        
        <!-- Error message container -->
        <div class="bo k9 err-msg" id="errmsg" style="display: none;"></div>
        
        <!-- Form -->
        <form name="dataform" style="margin:0; padding:0;" method="post">
            
            <input type="hidden" id="msg_from" name="msg_from" value="<?php echo (int)($_SESSION['uid_indm'] ?? 0); ?>">
            <input type="hidden" id="msg_to" name="msg_to" value="<?php echo (int)$recipient->usr_id; ?>">
            <input type="hidden" id="msg_subject" name="msg_subject" value="<?php echo htmlspecialchars($enquiry_subject, ENT_QUOTES, 'UTF-8'); ?>">
            
            <!-- Message textarea -->
            <div class="enn1-nw nef4-nw">
                <textarea id="msg_message" name="msg_message" class="nef10-nw" tabindex="1" 
                          placeholder="Describe your enquiry in detail..."></textarea>
                
                <div class="nef9-nw nef12-nw" style="text-align:right; width:99%;">
                    Remaining Characters: <b><strong id="charCount" class="char-count">2000</strong></b>
                    <div class="m2"></div>
                </div>
            </div>
            
            <!-- Guidelines fieldset -->
            <fieldset>
                <legend><strong>Describe your requirement</strong></legend>
                <div class="f1-nw" style="color:#055985;">
                    <ul>
                        <li class="li-1">Product requirement</li>
                        <li class="li-1">Specifications needed</li>
                        <li class="li-1">Packaging & delivery</li>
                        <li class="li-1">Your company details etc.</li>
                    </ul>
                </div>
            </fieldset>
            
            <div class="clr-nw" style="margin-bottom:2px;"></div>
            
            <?php if ($is_premium): ?>
                <!-- Premium member view - shows recipient info -->
                <div>
                    <div class="w12" align="LEFT">
                        <b>Buyer information:</b>
                    </div>
                    <div class="text" align="LEFT">
                        <div style="clear:both;"></div>
                        <div id="yourcontactinfo">
                            <div class="text" style="padding-top:5px;" align="LEFT">
                                <?php echo htmlspecialchars($recipient_name, ENT_QUOTES, 'UTF-8'); ?>
                                <br>
                                <?php if (!empty($recipient->country) && $recipient->country != '0'): ?>
                                    <?php echo htmlspecialchars(get_country_name((int)$recipient->country), ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                                <br>
                                Email: <?php echo htmlspecialchars($recipient->email ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!empty($recipient->mobile1) && $recipient->mobile1 != '0'): ?>
                                    <br>
                                    Mobile / Cell Phone: +(<?php echo htmlspecialchars($recipient->country_ph_code ?? '', ENT_QUOTES, 'UTF-8'); ?>)-<?php echo htmlspecialchars($recipient->mobile1, ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="font-size:12px; margin-left:0px; padding:0px 0pt 10px 15px;" align="LEFT">
                        <br>
                    </div>
                </div>
            <?php else: ?>
                <!-- Regular member view - shows sender info -->
                <div>
                    <div class="w12" align="LEFT">
                        <b>Contact information:</b>
                    </div>
                    <div class="text" align="LEFT">
                        <div style="clear:both;"></div>
                        <div id="yourcontactinfo">
                            <div class="text" style="padding-top:5px;" align="LEFT">
                                <?php echo htmlspecialchars($sender_name, ENT_QUOTES, 'UTF-8'); ?>
                                <br>
                                <?php if (!empty($sender->country) && $sender->country != '0'): ?>
                                    <?php echo htmlspecialchars(get_country_name((int)$sender->country), ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                                <br>
                                Email: <?php echo htmlspecialchars($sender->email ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!empty($sender->mobile1) && $sender->mobile1 != '0'): ?>
                                    <br>
                                    Mobile / Cell Phone: +(<?php echo htmlspecialchars($sender->country_ph_code ?? '', ENT_QUOTES, 'UTF-8'); ?>)-<?php echo htmlspecialchars($sender->mobile1, ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div style="font-size:12px; margin-left:0px; padding:0px 0pt 10px 15px;" align="LEFT">
                        <br>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="clr-nw"></div>
            
            <!-- Submit buttons and status messages -->
            <div id="nu_frm">
                <div class="nef4-nw" align="center">
                    <div style="display: block;" id="b_sub">
                        <input name="submit_member" id="button" value="Send Enquiry" 
                               class="snd-enq" type="button" onclick="sendEnquiry();"/>
                    </div>
                    
                    <div id="loading" style="display:none; padding-left:5px; color:#1045B0; padding-top:16px;" 
                         class="g9 bo off">
                        <img class="loading" src="images/loading-small.gif" alt="loading" height="16" width="16">
                        <b> Please Wait...</b>
                    </div>
                    
                    <div id="succ_result" style="display:none; padding-left:5px; color:#009700; padding-top:16px;" 
                         class="g9 bo off">
                        Enquiry Sent Successfully
                    </div>
                    
                    <div id="err_result" style="display:none; padding-left:5px; color:#F00; padding-top:16px;" 
                         class="g9 bo off">
                        Error sending enquiry. Please try again later.
                    </div>
                </div>
            </div>
        </form>
        
        <div class="clr-nw"></div>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>