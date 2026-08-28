<?php
/**
 * File Name: myprofile.php
 * PHP Version: 8.3
 * Description: صفحة إدارة موضوعات وبروفايل الشركة - نسخة مطورة ومتوافقة مع PHP 8.3
 */

declare(strict_types=1);

require_once 'common.php';

// التحقق من الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود المستخدم في الجلسة
if (empty($_SESSION['uid_indm'] ?? null)) {
    header('Location: sign-in.php');
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
if (!isset($con) || !$con) {
    die('Database connection error');
}

// استبدال mysql_* بـ MySQLi
$sql_wc = "SELECT * FROM website_content WHERE wc_usr_id = ?";
$stmt_wc = mysqli_prepare($con, $sql_wc);
mysqli_stmt_bind_param($stmt_wc, 'i', $uid);
mysqli_stmt_execute($stmt_wc);
$result_wc = mysqli_stmt_get_result($stmt_wc);
$row_wc = mysqli_fetch_object($result_wc);
mysqli_stmt_close($stmt_wc);

if (!$row_wc) {
    // إذا لم يكن هناك محتوى موقع، قم بإنشاء سجل افتراضي
    $insert_wc = "INSERT INTO website_content (wc_usr_id, wc_status) VALUES (?, '1')";
    $stmt_insert = mysqli_prepare($con, $insert_wc);
    mysqli_stmt_bind_param($stmt_insert, 'i', $uid);
    mysqli_stmt_execute($stmt_insert);
    $wc_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt_insert);
    
    $row_wc = new stdClass();
    $row_wc->wc_id = $wc_id;
}

/**
 * Class AddSaleroom
 * كلاس إدارة وإضافة موضوعات صفحة "من نحن"
 */
class AddSaleroom
{
    private string $msg = '';
    private int $abtus_ph_id;
    private string $abtus_desc;
    private int $abtus_wc_id;
    private int $uid;
    private mysqli $db;

    public function __construct(
        int $abtus_ph_id,
        string $abtus_desc,
        int $abtus_wc_id,
        int $uid,
        mysqli $db
    ) {
        $this->db = $db;
        $this->abtus_ph_id = $abtus_ph_id;
        $this->abtus_desc = $this->sanitizeInput($abtus_desc);
        $this->abtus_wc_id = $abtus_wc_id;
        $this->uid = $uid;

        $_SESSION['abtus_ph_id'] = $this->abtus_ph_id;
        $_SESSION['abtus_desc'] = $this->abtus_desc;
    }

    /**
     * تنظيف المدخلات النصية
     */
    private function sanitizeInput(string $input): string
    {
        return trim($input);
    }

    /**
     * التحقق من صحة البيانات
     */
    public function isValid(): bool
    {
        $valid = true;
        $totaldesc = strlen($this->abtus_desc);

        // التحقق من وجود العنوان مسبقاً
        $sql_chk = "SELECT COUNT(*) FROM about_us WHERE abtus_ph_id = ? AND abtus_wc_id = ?";
        $stmt_chk = $this->db->prepare($sql_chk);
        $stmt_chk->bind_param('ii', $this->abtus_ph_id, $this->abtus_wc_id);
        $stmt_chk->execute();
        $stmt_chk->bind_result($count);
        $stmt_chk->fetch();
        $stmt_chk->close();

        if ($this->abtus_ph_id === 0) {
            $this->msg = '<font color="#CC0000">Please check that Profile Heading cannot be blank.</font>';
            $valid = false;
        } elseif ($count > 0) {
            $this->msg = '<font color="#CC0000">This title is already in use.</font>';
            $valid = false;
        } elseif (empty($this->abtus_desc)) {
            $this->msg = '<font color="#CC0000">Please check that Profile Description cannot be blank.</font>';
            $valid = false;
        } elseif ($totaldesc > 4000) {
            $this->msg = '<font color="#CC0000">Please check that Profile Description cannot have more than 4000 characters.</font>';
            $valid = false;
        }

        return $valid;
    }

    /**
     * إضافة الموضوع إلى قاعدة البيانات
     */
    public function add(): void
    {
        // جلب الصورة المؤقتة
        $sql_tmp = "SELECT tmabs_images FROM temp_about_us WHERE tmabs_usrid = ?";
        $stmt_tmp = $this->db->prepare($sql_tmp);
        $stmt_tmp->bind_param('i', $this->uid);
        $stmt_tmp->execute();
        $result_tmp = $stmt_tmp->get_result();
        $tmpimagerow = $result_tmp->fetch_object();
        $stmt_tmp->close();

        $image = $tmpimagerow->tmabs_images ?? '';

        // إدراج الموضوع
        $sql_insert = "INSERT INTO about_us 
                       (abtus_wc_id, abtus_ph_id, abtus_image, abtus_desc, abtus_date) 
                       VALUES (?, ?, ?, ?, NOW())";
        
        $stmt_insert = $this->db->prepare($sql_insert);
        $stmt_insert->bind_param('iiss', 
            $this->abtus_wc_id,
            $this->abtus_ph_id,
            $image,
            $this->abtus_desc
        );
        $stmt_insert->execute();
        $stmt_insert->close();

        // حذف الصورة المؤقتة
        $sql_del = "DELETE FROM temp_about_us WHERE tmabs_usrid = ?";
        $stmt_del = $this->db->prepare($sql_del);
        $stmt_del->bind_param('i', $this->uid);
        $stmt_del->execute();
        $stmt_del->close();

        unset($_SESSION['abtus_ph_id']);
        unset($_SESSION['abtus_desc']);
    }

    public function getMessage(): string
    {
        return $this->msg;
    }
}

// معالجة الرسائل المخزنة في الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// استرجاع القيم من الجلسة
$abtus_ph_id = (int)($_SESSION['abtus_ph_id'] ?? 0);
$abtus_desc = $_SESSION['abtus_desc'] ?? '';
unset($_SESSION['abtus_ph_id'], $_SESSION['abtus_desc']);

// التحقق من العضوية المميزة
function checkPremiumMembership(int $uid, mysqli $con): bool {
    $sql = "SELECT usr_mp_id FROM user_member WHERE usr_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $stmt->bind_result($mp_id);
    $stmt->fetch();
    $stmt->close();
    return $mp_id >= 3;
}

// معالجة إضافة موضوع جديد
if (isset($_POST['btnAdd'])) {
    if (!checkPremiumMembership($uid, $con)) {
        echo '<script>
            alert("You have to subscribe to premium membership to establish Vendor Page");
            window.location.href = "membership_plans.php";
        </script>';
        exit;
    }

    $abtus_ph_id = (int)trim($_POST['abtus_ph_id'] ?? 0);
    $abtus_desc = trim($_POST['abtus_desc'] ?? '');
    $abtus_wc_id = (int)trim($_POST['abtus_wc_id'] ?? 0);

    $_SESSION['abtus_ph_id'] = $abtus_ph_id;
    $_SESSION['abtus_desc'] = $abtus_desc;

    $adn = new AddSaleroom($abtus_ph_id, $abtus_desc, $abtus_wc_id, $uid, $con);

    if ($adn->isValid()) {
        $adn->add();
        $_SESSION['msg'] = '<font color="#009900">تم إضافة الموضوع بنجاح</font>';
    } else {
        $_SESSION['msg'] = $adn->getMessage();
    }

    header("Location: myprofile.php");
    exit;
}

// جلب الموضوعات الحالية
$sql_about = "SELECT a.*, p.ph_title 
              FROM about_us a
              JOIN profile_heading_arabyos p ON a.abtus_ph_id = p.ph_id
              WHERE a.abtus_wc_id = ?
              ORDER BY a.abtus_order";
$stmt_about = $con->prepare($sql_about);
$stmt_about->bind_param('i', $row_wc->wc_id);
$stmt_about->execute();
$result_about = $stmt_about->get_result();

$about_items = [];
while ($row = $result_about->fetch_assoc()) {
    $about_items[] = [
        'id' => (int)$row['abtus_id'],
        'ph_id' => (int)$row['abtus_ph_id'],
        'ph_title' => htmlspecialchars($row['ph_title'] ?? '', ENT_QUOTES, 'UTF-8'),
        'image' => $row['abtus_image'] ? htmlspecialchars($row['abtus_image'], ENT_QUOTES, 'UTF-8') : null,
        'desc' => $row['abtus_desc'] ?? '',
        'order' => (int)$row['abtus_order']
    ];
}
$totalabt = count($about_items);
$stmt_about->close();

// جلب عناوين الموضوعات للقوائم المنسدلة
$sql_headings = "SELECT ph_id, ph_title FROM profile_heading_arabyos WHERE ph_status = '1' ORDER BY ph_title";
$result_headings = $con->query($sql_headings);
$headings = [];
while ($row = $result_headings->fetch_assoc()) {
    $headings[] = [
        'id' => (int)$row['ph_id'],
        'title' => htmlspecialchars($row['ph_title'], ENT_QUOTES, 'UTF-8')
    ];
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? 'إدارة ملف الشركة'); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/about-us.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    <link href="css/colorbox.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">

    <script src="js/jquery.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
</head>
<body>

<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
    <br>
    <div class="bt">
        <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1">
    </div>

    <?php include "includes/header_menu.php"; ?>
    
    <!-- القائمة الجانبية -->
    <?php include 'includes/left_menu.php'; ?>
    
    <!-- المحتوى الرئيسي -->
    <div class="w56 f1 p2b p14 blr" style="width:80%; height:100%;">
        <div></div>
        <div class="c3"></div>
        
        <div>
            <div id="chg_name" class="f1 chng_a">
                <h1 class="f1" id="cpf_name">موضوعات وبروفايل وأخبار الشركة</h1>
            </div>
            <p id="pf_change" style="display:none; float:left; margin-top:0px"></p>
            <p class="f2 mt11 cnt_1" id="prof_cnt"></p>
            <div class="c3"></div>
        </div>
        
        <div class="clb px"></div>
        
        <div class="" style="margin-top:4px;">
            <p class="aml"></p>
            <div id="re_link" class="utab">
                <span style="font-size: 12px;">:إملأ أخبار تجارية عن الشركة وموضوعات تهم المشتريين</span>
                <?php if ($totalabt > 0): ?>
                <a href="myprofileorder.php" class="f2 fw prf" style="display:block;" id="rearr_link" title="Rearrange">رتب الموضوعات</a>
                <?php endif; ?>
                <a style="display: block;" class="f2 fw apr1" id="edit_add" onclick="formopend('add');" href="myprofile.php#form_tst1" title="Add About Us">إضف موضوعات للنشر</a>
            </div>
            <div class="c3"></div>
            <div class="c3"></div>
        </div>
        
        <?php foreach ($about_items as $abtrow): ?>
        <!-- عرض الموضوع -->
        <div id="list_abt<?php echo $abtrow['id']; ?>" class="mt_7 ap4 p8 s mse abtListdv">
            <div class="c3"></div>
            
            <script>
            $(document).ready(function(){
                $(".ajax").colorbox();
                $(".inline").colorbox({inline:true, width:"50%"});
                $("#click").click(function(){ 
                    $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
                    return false;
                });
            });
            </script>
            
            <table width="100%">
                <tr>
                    <td style="vertical-align:top; width:125px;">
                        <?php if ($abtrow['image']): ?>
                        <div class="f1" style="width:125px">
                            <div class="f1 ap3" id="base_p_image_1671511" align="center">
                                <img src="upload/myprofile/<?php echo $abtrow['image']; ?>" id="img_small_form_1671511" width="125" height="93">
                            </div>
                            <a href="aboutzoomimage.php?token=<?php echo rand(1000,9999) . md5((string)$abtrow['id']); ?>" class="ajax" style="cursor:pointer;">
                                <div class="z bnr f2 mrgzoom">&nbsp;</div>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="f1" style="width:125px">
                            <div class="f1 ap3" id="base_p_image_1671511" align="center">
                                <img src="images/noimage.jpg" id="img_small_form_1671511" width="125" height="125">
                            </div>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="vertical-align:top;">
                        <div class="f1 ap5 wrd-brk awpf">
                            <h2 class="mb5 itm_clr" id="base_title_1671511"><?php echo $abtrow['ph_title']; ?></h2>
                            <div id="base_desc_hd<?php echo $abtrow['id']; ?>" style="margin-right:20px; color: #222222; display:none;">
                                <?php echo nl2br(htmlspecialchars($abtrow['desc'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                            <div id="base_desc_sd<?php echo $abtrow['id']; ?>" style="margin-right:20px; color: #222222;">
                                <?php echo htmlspecialchars(substr($abtrow['desc'], 0, 296), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <?php if (strlen($abtrow['desc']) > 296): ?>
                            <a style="padding-right:20px; float:right; font-size:12.5px; text-align:center; text-decoration:underline; cursor:pointer;" 
                               id="less_hd<?php echo $abtrow['id']; ?>" onClick="showdesc(<?php echo $abtrow['id']; ?>)">
                                View Complete Details
                            </a>
                            <?php endif; ?>
                            <span id="less_sd<?php echo $abtrow['id']; ?>" style="display:none;">
                                <a style="padding-right:20px; float:right; font-size:12.5px; text-align:center; text-decoration:underline; cursor:pointer;" 
                                   onClick="hidedesc(<?php echo $abtrow['id']; ?>)">
                                    Less
                                </a>
                            </span>
                        </div>
                        
                        <div style="width: 100px; margin-left: 20px; margin-top: 100px;" class="f1">
                            <span style="*margin-bottom:5px" class="link1 cpr">
                                <a onclick="showedit(<?php echo $abtrow['id']; ?>);" class="edi bnr dl_pf" id="edit_1" 
                                   style="*float:none; display:block; padding-bottom: 4px;" title="أعد التحرير">Edit</a>
                            </span>
                            <a id="delp_1671511" onclick="showdeloption(<?php echo $abtrow['id']; ?>)" 
                               class="del bnr dl_pf" style="cursor:pointer;" title="إحذف">Delete</a>
                        </div>
                    </td>
                </tr>
            </table>
            
            <div class="c3"></div>
            
            <div class="info bnr dn" id="dcon<?php echo $abtrow['id']; ?>" style="display:none;">
                <div style="width:125px;" class="f2">
                    <a id="yesp_1671495" onclick="delmprofile(<?php echo $abtrow['id']; ?>)" class="yn" style="cursor:pointer;">Yes</a>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <a id="nop_1671495" onclick="hidedeloption(<?php echo $abtrow['id']; ?>)" class="yn" style="cursor:pointer;">No</a>
                </div>
                Do you really want to delete this record?
            </div>
        </div>
        
        <!-- نموذج تعديل الموضوع -->
        <div id="edit_abt<?php echo $abtrow['id']; ?>" style="display:none;" class="ap4 aef mt_7 abouteditdv">
            <div style="margin-right:2px; width:56px; float:right;" class="dis">
                <a style="cursor:pointer;" onclick="hidedit(<?php echo $abtrow['id']; ?>);">Close [x]</a>
            </div>
            
            <form action="" name="dataform" method="post">
                <table id="mysaveid" border="0" cellpadding="4" cellspacing="0" width="100%">
                    <tr>
                        <td colspan="4" align="left" valign="top">
                            <span class="label" style="font-weight:bold"></span>
                            <div id="updatetmsg<?php echo $abtrow['id']; ?>"><?php echo $msg; ?></div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td rowspan="3" valign="top" width="135">
                            <div>
                                <p style="display: none;" id="old_img_form" class="ap3" align="center"></p>
                                <div style="display: block; z-index: 0;" id="old_img_f" class="ap3">
                                    <div id="img_gdb"></div>
                                    <iframe src='update-aboutus-image.php?abtid=<?php echo $abtrow['id']; ?>' 
                                            border="0" framespacing="0" allowtransparency="true" scrolling="no" 
                                            width="125" frameborder="0" height="125">
                                    </iframe>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0" align="left">
                            <span class="label" style="font-weight:bold"><span>*</span> Profile Heading:</span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding:0" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td>
                                                    <div id="mandropEdit">
                                                        <select name="abtusheading<?php echo $abtrow['id']; ?>" 
                                                                id="abtusheading<?php echo $abtrow['id']; ?>" 
                                                                class="a_f rf" style="width:276px">
                                                            <option value="">Select Heading</option>
                                                            <?php foreach ($headings as $hdrow): ?>
                                                            <option value="<?php echo $hdrow['id']; ?>" 
                                                                <?php echo $abtrow['ph_id'] == $hdrow['id'] ? 'selected' : ''; ?>>
                                                                <?php echo $hdrow['title']; ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div id="selectEdit2"></div>
                                                    <div></div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>&nbsp;</td>
                        <td align="right" valign="bottom">
                            <input style="margin-right:1px" id="save_button" 
                                   onclick="mysave(<?php echo $abtrow['id']; ?>);" 
                                   value="Save" class="saps mt5" type="button">
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding-top:0px; padding-bottom:0px">
                            <div id="delete_smallimg_popup" style="display:none; margin-left:37px; padding-left:15px;" class="z2">
                                <a href="javascript:delete_smallimg()" style="text-decoration:none; text-align: center;">
                                    <font size="1px"><b>remove</b></font>
                                </a>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td colspan="4" align="left" valign="top">
                            <span class="label" style="font-weight:bold"><span>*</span> Profile Description:</span>
                            <textarea name="abtusdesc<?php echo $abtrow['id']; ?>" 
                                      rows="10" cols="80" 
                                      id="abtusdesc<?php echo $abtrow['id']; ?>" 
                                      class="a_f rf" 
                                      style="width: 100%; height: 200px; display: block;" 
                                      onKeyUp="allcount(<?php echo $abtrow['id']; ?>);"><?php echo htmlspecialchars($abtrow['desc'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <span class="mceEditor defaultSkin" id="p_desc1_parent"></span>
                            <div class="max f11 tlx">
                                <font id="Charcount1" color="#ff8000">
                                    <span id="act<?php echo $abtrow['id']; ?>">0</span> character (maximum of 4000)
                                </font>&nbsp;character(s)
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php endforeach; ?>
        
        <!-- نموذج إضافة موضوع جديد -->
        <div id="form_tst1" style="display:<?php echo ($totalabt <= 0 || $msg != '') ? 'block' : 'none'; ?>;">
            <div id="profile" class="aef ap4 mt_7" align="center">
                <div style="margin-right:2px; width:56px; float:right;" class="dis">
                    <a href="javascript:formclose();">Close [x]</a>
                </div>
                
                <form name="ad_profile" method="post" action="" onsubmit="return check_data();">
                    <div>
                        <table align="center" border="0" cellpadding="4" cellspacing="0" width="100%">
                            <tr>
                                <td colspan="4" align="left" valign="top">
                                    <span class="label" style="font-weight:bold"></span>
                                    <div id="message"><?php echo $msg; ?></div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td rowspan="3" valign="top" width="135">
                                    <div>
                                        <p id="old_img_form0" class="ap3" style="display:none" align="center"></p>
                                        
                                        <script type="text/javascript">
                                        function list_photo() {
                                            $.get("list_photo.php", {'uid' : <?php echo $uid; ?>}, function(data){ 
                                                $('#list_photo').html(data); 
                                            });
                                        }

                                        function DelTempImage(imid) {
                                            $.get("del_temp_image.php", {imid: imid}, function(data){
                                                list_photo();
                                            });
                                        }

                                        $(function() {
                                            $('#file_upload').uploadifive({
                                                'auto'         : true,
                                                'formData'     : {'uid' : '<?php echo $uid; ?>'},
                                                'queueID'      : 'queue',
                                                'debug'        : true,
                                                'method'       : 'post',
                                                'buttonClass'  : 'input_textFiled2',
                                                'buttonText'   : 'Upload',
                                                'uploadScript' : 'upload-image.php',
                                                'onUploadComplete' : function(file, data) {
                                                    list_photo();
                                                }
                                            });
                                        });
                                        </script>
                                        
                                        <div style="display: block; z-index: 0;" id="old_img_f" class="ap3">
                                            <iframe src="upload-aboutus-image.php" border="0" framespacing="0" 
                                                    allowtransparency="true" scrolling="no" width="125" frameborder="0" height="125">
                                            </iframe>
                                        </div>
                                    </div>
                                </td>
                                
                                <td style="padding:0" align="left">
                                    <span class="label" style="font-weight:bold"><span>*</span>&nbsp;Profile Heading</span>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style="padding:0" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td>
                                                            <select name="abtus_ph_id" id="abtus_ph_id" 
                                                                    class="a_f rf" onchange="check_dual_title('ad_profile');" style="width:276px">
                                                                <option value="">Select Heading</option>
                                                                <?php foreach ($headings as $hdrow): ?>
                                                                <option value="<?php echo $hdrow['id']; ?>" 
                                                                    <?php echo $abtus_ph_id == $hdrow['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo $hdrow['title']; ?>
                                                                </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div id="select2"></div>
                                                            <div>
                                                                <input name="p_sub_title" size="25" id="text1" 
                                                                       style="display:none; width:175px;" 
                                                                       class="a_f rf ml8" type="text">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <input value="Select Heading" id="idtxt" name="p_title" type="hidden">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <tr>
                                <td>&nbsp;</td>
                                <td align="right" valign="bottom">
                                    <input name="abtus_wc_id" id="abtus_wc_id" value="<?php echo (int)$row_wc->wc_id; ?>" type="hidden">
                                    <span id="pf_save" style="display:none; float:right; margin-left:15px; margin-top:14px;">
                                        <img src="images/loading.gif" alt="" border="0" width="16" height="11">
                                    </span>
                                    <input name="btnAdd" value="Save" class="saps mt5" id="btnAdd" type="submit">
                                </td>
                            </tr>
                            
                            <tr>
                                <td style="padding-top:0px; padding-bottom:0px">
                                    <div class="z2" style="display: none; margin-left: 37px; padding-left: 15px;" id="delete_smallimg">
                                        <a style="text-decoration:none; text-align: center;" href="javascript:delete_smallimg()">
                                            <font size="1px"><b>remove</b></font>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td colspan="4" align="left" valign="top">
                                    <span class="label" style="font-weight:bold"><span>*</span>&nbsp;Profile Description:</span>
                                    <textarea name="abtus_desc" rows="10" cols="80" 
                                              id="abtus_desc" class="a_f rf" 
                                              style="width: 100%; height: 200px; display: block;" 
                                              onKeyUp="descount();"><?php echo htmlspecialchars($abtus_desc, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <div class="max f11 tlx">
                                        <font id="Charcount" color="#ff8000">
                                            <span id="cn">0</span> character (maximum of 4000)
                                        </font>&nbsp;character(s)
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <div class="clb"></div>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
    
    <div class="c3">&nbsp;</div>
</div>

<!-- تذييل الصفحة -->
<?php include 'includes/footer.php'; ?>

<script>
/**
 * دوال JavaScript - متوافقة مع PHP 8.3
 */

function descount() {
    var cnt = $("#abtus_desc").val().length;		
    $("#cn").html(cnt);
}

function formopend() {
    $("#form_tst1").show();	
}

function showdeloption(id) {
    $("#dcon" + id).slideDown('slow');
}

function hidedeloption(id) {
    $("#dcon" + id).slideUp('slow');
}

function delmprofile(id) {
    $.get("ajax-file/delmprofile.php", {id: id}, function(data){
        location.reload();
    });		
}

function formclose() {
    $("#form_tst1").hide();	
}

function showdesc(id) {
    $("#base_desc_hd" + id).show();	
    $("#less_sd" + id).show();
    $("#base_desc_sd" + id).hide();	
    $("#less_hd" + id).hide();
}

function hidedesc(id) {
    $("#base_desc_hd" + id).hide();	
    $("#less_sd" + id).hide();
    $("#base_desc_sd" + id).show();	
    $("#less_hd" + id).show();		
}

function showedit(id) {	
    $(".abouteditdv").hide();
    $(".abtListdv").show();

    $("#edit_abt" + id).show();
    $("#list_abt" + id).hide();
    $("#form_tst1").hide();
}

function hidedit(id) {
    $("#edit_abt" + id).hide();
    $("#list_abt" + id).show();
}

function allcount(id) {
    var cnt = $("#abtusdesc" + id).val().length;		
    $("#act" + id).html(cnt);
}

function mysave(id) {
    var abtusheading = $("#abtusheading" + id).val();	  
    var abtusdesc = $("#abtusdesc" + id).val();
    
    if (abtusheading === "") {
        $("#updatetmsg" + id).html('Please check that Profile Heading cannot be blank');
        $("#updatetmsg" + id).css("color","red");
    } else if (abtusdesc === "") {
        $("#updatetmsg" + id).html('Please check that Profile Description cannot be blank.');
        $("#updatetmsg" + id).css("color","red");
    } else {
        $.get("ajax-file/about-us-edit.php", {
            id: id,
            abtusheading: abtusheading,
            abtusdesc: abtusdesc
        }, function(data){
            var d = data.split('||');
            if (d[0] != " ") {
                $("#updatetmsg" + id).html(d[0]);
                $("#updatetmsg" + id).css("color","red");
            } else {
                $("#updatetmsg" + id).html(d[1]);
                $("#updatetmsg" + id).css("color","green");
                location.reload();	
            }
        });		
    }
}

function check_dual_title(formName) {
    // يمكن إضافة منطق التحقق هنا
}
</script>
</body>
</html>