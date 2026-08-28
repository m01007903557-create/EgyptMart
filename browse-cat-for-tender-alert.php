<?php
/**
 * File: browse-cat-for-tender-alert.php
 * Version: PHP 8.3
 * Description: تصفح الفئات لإضافة تنبيهات المناقصات - واجهة اختيار الفئات
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// التحقق من وجود مستخدم مسجل دخوله
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit();
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// تحديد ترتيب عرض الفئات
$sql_order = (get_page_settings('25') == 'manual') ? " ORDER BY pc_order, pc_name" : " ORDER BY pc_name";
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة تنبيهات المناقصات</title>
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .bg_border_new {
            height: 675px;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 20px;
            background-color: #fff;
        }
        
        #dvh2 {
            background-color: #FFFFFF;
            height: 670px;
            overflow-y: auto;
        }
        
        .myta {
            padding: 10px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            background-color: #FAF4FF;
            border-bottom: 2px solid #d4b8ff;
        }
        
        .tabclose {
            background-color: #e0e0e0;
            color: #666;
            padding: 8px 15px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
            text-align: center;
        }
        
        .tabopen {
            background-color: #FAF4FF;
            color: #333;
            padding: 8px 15px;
            cursor: pointer;
            border: 1px solid #d4b8ff;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
            text-align: center;
            border-top: 2px solid #a569ff;
        }
        
        .tabborder {
            width: 10px;
        }
        
        .border_bottom {
            border: 1px solid #d4b8ff;
            border-radius: 5px;
            padding: 10px;
            background-color: #fff;
        }
        
        select {
            font-size: 13px;
            font-family: arial;
            height: 180px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px;
        }
        
        select option {
            padding: 3px 5px;
        }
        
        select option:hover {
            background-color: #FAF4FF;
        }
        
        #addSOCatBtn, #addLeadCatBtn {
            background-color: #B90000;
            background: -moz-linear-gradient(top, #B90000 0%, #B90000 8%, #DF0000 54%, #DF0000 100%);
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#B90000), color-stop(8%,#B90000), color-stop(54%,#DF0000), color-stop(100%,#DF0000));
            background: -webkit-linear-gradient(top, #B90000 0%,#B90000 8%,#710000 54%,#B90000 100%);
            background: -o-linear-gradient(top, #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
            background: -ms-linear-gradient(top, #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
            background: linear-gradient(to bottom, #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#B90000', endColorstr='#DF0000',GradientType=0);
            box-shadow: 0pt 1px 5px #AAA;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            color: #FFF;
            border: 1px solid #C10000;
            border-radius: 6px;
            padding: 5px 20px;
            cursor: pointer;
            margin: 5px;
        }
        
        #addSOCatBtn:hover, #addLeadCatBtn:hover {
            background: #DF0000;
        }
        
        #txt_cat_mcat {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
            font-size: 13px;
        }
        
        #txt_cat_mcat:focus {
            border-color: #a569ff;
            outline: none;
            box-shadow: 0 0 5px rgba(165,105,255,0.3);
        }
        
        .blwnew {
            padding: 5px 0;
            color: #E95801;
        }
        
        .setcatnew {
            padding: 5px 0;
        }
        
        .kk {
            font-weight: bold;
            color: #333;
        }
        
        #div1 {
            display: block;
            padding: 5px;
            min-height: 100px;
        }
        
        .category-item {
            display: inline-block;
            background-color: #FAF4FF;
            border: 1px solid #d4b8ff;
            border-radius: 3px;
            padding: 3px 8px;
            margin: 2px;
            font-size: 12px;
        }
        
        .category-item a {
            color: #ff0000;
            text-decoration: none;
            margin-right: 5px;
            font-weight: bold;
        }
        
        .category-item a:hover {
            color: #cc0000;
        }
        
        #loading_scat {
            padding-top: 70px;
            text-align: center;
        }
        
        #scat {
            border: 1px solid #336699;
            background-color: #ffffff;
            overflow: auto;
            height: 165px;
            padding-left: 1px;
            font-size: 13px;
        }
        
        .scat-item {
            padding: 3px 5px;
            border-bottom: 1px solid #eee;
        }
        
        .scat-item:hover {
            background-color: #FAF4FF;
        }
        
        .scat-item input[type="checkbox"] {
            margin-left: 5px;
        }
    </style>
    
    <script type="text/javascript">
    /**
     * إضافة فئة تنبيه
     */
    function addAlertCategory() {
        $.ajax({
            type: "POST",
            url: "ajax-file/addTenderAlertCat.php",
            data: {},
            success: function(data) {
                window.location.reload();
            },
            error: function() {
                alert("حدث خطأ في إضافة الفئة");
            }
        });
    }
    
    /**
     * حذف فئة تنبيه
     * @param {number} id - معرف الفئة
     */
    function delAlertCat(id) {
        if (confirm("هل أنت متأكد من حذف هذه الفئة؟")) {
            $.ajax({
                type: "POST",
                url: "ajax-file/delTenderAlertCat.php",
                data: {id: id},
                success: function(data) {
                    window.location.reload();
                },
                error: function() {
                    alert("حدث خطأ في حذف الفئة");
                }
            });
        }
    }
    
    /**
     * التبديل إلى بحث الفئات
     */
    function searchcat() {
        $("#sc").removeClass("tabclose").addClass("tabopen");
        $("#bc").removeClass("tabopen").addClass("tabclose");
        $("#browse_cat").css("display", "none");
        $("#search_cat").css("display", "block");
    }
    
    /**
     * التبديل إلى تصفح الفئات
     */
    function beowswcat() {
        $("#bc").removeClass("tabclose").addClass("tabopen");
        $("#sc").removeClass("tabopen").addClass("tabclose");
        $("#search_cat").css("display", "none");
        $("#browse_cat").css("display", "block");
    }
    
    /**
     * عرض الفئات الفرعية
     * @param {number} id - معرف الفئة الرئيسية
     */
    function showCategory(id) {
        $.ajax({
            type: "POST",
            url: "ajax-file/showSubcategory.php",
            data: {id: id},
            success: function(data) {
                if (data != '') {
                    $('#grp').html(data);
                    $("#cat_select_area").show();
                }
            },
            error: function() {
                alert("حدث خطأ في تحميل الفئات الفرعية");
            }
        });
    }
    
    /**
     * عرض الفئات الفرعية مع خانات الاختيار
     * @param {number} id - معرف الفئة
     */
    function catajaxFunction(id) {
        $("#display_mcat").css("display", "block");
        $("#scat").css("display", "none");
        $("#loading_scat").css("display", "block");
        
        var type = "tender";
        
        $.ajax({
            type: "POST",
            url: "ajax-file/subcategoryCheckBox.php",
            data: {id: id, type: type},
            success: function(data) {
                $("#scat").html(data);
                $("#loading_scat").css("display", "none");
                $("#scat").css("display", "block");
            },
            error: function() {
                $("#loading_scat").css("display", "none");
                alert("حدث خطأ في تحميل الفئات");
            }
        });
    }
    
    /**
     * إضافة أو حذف فئة فرعية
     * @param {number} id - معرف الفئة
     */
    function scatAddDel(id) {
        if ($('#scat_' + id).is(':checked')) {
            $.ajax({
                type: "POST",
                url: "ajax-file/addTempTenderAlertCat.php",
                data: {id: id},
                success: function(data) {
                    showList();
                },
                error: function() {
                    alert("حدث خطأ في إضافة الفئة");
                }
            });
        } else {
            $.ajax({
                type: "POST",
                url: "ajax-file/delTempTenderAlertCat.php",
                data: {id: id},
                success: function(data) {
                    showList();
                },
                error: function() {
                    alert("حدث خطأ في حذف الفئة");
                }
            });
        }
    }
    
    /**
     * إضافة تنبيه المناقصات
     */
    function addalertlead() {
        $.ajax({
            type: "POST",
            url: "ajax-file/addTenderAlertCat.php",
            data: {},
            success: function(data) {
                window.location.href = 'manage-tender-alert.php';
            },
            error: function() {
                alert("حدث خطأ في إضافة التنبيهات");
            }
        });
    }
    
    /**
     * عرض قائمة الفئات المختارة
     */
    function showList() {
        $.ajax({
            type: "POST",
            url: "ajax-file/showTempTenderAlertCat.php",
            data: {},
            success: function(data) {
                $("#div1").html(data);
            },
            error: function() {
                $("#div1").html('<div style="color:red;">حدث خطأ في تحميل القائمة</div>');
            }
        });
    }
    
    /**
     * إزالة فئة من القائمة المؤقتة
     * @param {number} id - معرف الفئة
     */
    function remove(id) {
        $.ajax({
            type: "POST",
            url: "ajax-file/delTempTenderAlertCat.php",
            data: {id: id},
            success: function(data) {
                showList();
                // إلغاء تحديد checkbox المقابل
                if ($('#scat_' + id).length) {
                    $('#scat_' + id).prop('checked', false);
                }
            },
            error: function() {
                alert("حدث خطأ في حذف الفئة");
            }
        });
    }
    
    /**
     * إضافة فئة مخصصة
     */
    function produtcustomcategory() {
        var keywordsFilter = $("#txt_cat_mcat").val().trim();
        
        if (keywordsFilter == "") {
            alert("الرجاء إدخال كلمات مفتاحية أولاً");
            return false;
        }
        
        $.ajax({
            type: "POST",
            url: "ajax-file/produtcustomcategory.php",
            data: {'keywordsFilter': keywordsFilter, 'type': 'addTempTenderAlertCat'},
            success: function(data) {
                $('#div1').append(data);
                $("#txt_cat_mcat").val('');
            },
            error: function() {
                alert("حدث خطأ في إضافة الفئة");
            }
        });
    }
    
    $(document).ready(function() {
        // تهيئة القائمة
        showList();
    });
    </script>
</head>
<body>
    <div class="bg_border_new" id="dvh1">
        <div style="background-color:#FFFFFF; height:670px" id="dvh2">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td bgcolor="#FAF4FF">
                            <div class="myta">إدارة تفضيلات المناقصات</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <img src="images/zero.gif" height="10" width="1"><br>
            
            <div style="height: 450px;">
                <form style="margin:0px;" name="test" action="" onsubmit="return false">
                    <div>
                        <img src="images/zero.gif" height="14" width="1"><br>

                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                            <tbody>
                                <tr>
                                    <td valign="TOP" width="19">
                                        <img src="images/zero.gif" height="6" width="1"><br>
                                        <img src="images/11.gif" height="15" width="19">
                                    </td>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td class="tabclose" onclick="searchcat()" id="sc" width="152">بحث الفئات</td>
                                                    <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
                                                    <td class="tabopen" onclick="beowswcat()" id="bc" width="155">تصفح الفئات</td>
                                                    <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- قسم تصفح الفئات -->
                        <div id="browse_cat" style="display: block;">
                            <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                                <tbody>
                                    <tr>
                                        <td width="19"><img src="images/zero.gif" height="1" width="19"></td>
                                        <td bgcolor="#FAF4FF">
                                            <div class="border_bottom" align="left">
                                                <img src="images/zero.gif" height="10" width="1"><br>
                                                
                                                <table align="CENTER" border="0" cellpadding="0" cellspacing="0" style="width:500px;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="font-family:arial; font-size:12px; padding-left:3px;" width="100%">
                                                                <span id="grp1">
                                                                    <?php
                                                                    $sql_cat = "SELECT pc_id, pc_name FROM product_category 
                                                                                WHERE pc_parent_id = '0' AND pc_status = '1' 
                                                                                {$sql_order}";
                                                                    $res_cat = mysqli_query($con, $sql_cat);
                                                                    ?>
                                                                    <select size="10" name="mcat" id="mcat" onchange="showCategory(this.value)">
                                                                        <option value="">-- اختر فئة رئيسية --</option>
                                                                        <?php while ($row_cat = mysqli_fetch_object($res_cat)): ?>
                                                                            <option value="<?php echo (int)$row_cat->pc_id; ?>">
                                                                                <?php echo htmlspecialchars($row_cat->pc_name); ?>
                                                                            </option>
                                                                        <?php endwhile; ?>
                                                                    </select>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        
                                                        <tr id="cat_select_area" style="display:none;">
                                                            <td style="font-family:arial; font-size:12px; padding-left:3px;" width="100%">
                                                                <span id="grp1">
                                                                    <select size="10" name="grp" id="grp" onclick="catajaxFunction(this.value);">
                                                                        <option value="">-- اختر فئة --</option>
                                                                    </select>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        
                                                        <tr>
                                                            <td width="100%"><img src="images/zero.gif" height="1" width="5"></td>
                                                        </tr>
                                                        
                                                        <tr>
                                                            <td style="font-family:arial; font-size:12px; padding-left:3px; background:none;" width="100%">
                                                                <br>
                                                                <div style="height:170px">
                                                                    <div class="displayon" id="display_mcat" style="display:none;">
                                                                        <div style="background-color:#ffffff; overflow: auto; height: 170px; padding-left: 1px; font-size: 13px; text-align:left">
                                                                            <div id="loading_scat" align="center" style="padding-top:70px;">
                                                                                <img src="images/indicator.gif" alt="جاري التحميل..." />
                                                                            </div>
                                                                            <div style="border: 1px solid #336699; background-color:#ffffff; overflow: auto; height: 165px; padding-left:1px; font-size: 13px; display:none" id="scat">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <img src="images/zero.gif" height="8" width="1"><br>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- قسم بحث الفئات -->
                        <div id="search_cat" style="display: none; text-align: left;">
                            <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                                <tbody>
                                    <tr>
                                        <td valign="TOP"><img src="images/zero.gif" height="1" width="19"></td>
                                        <td bgcolor="#F8FCFF" valign="TOP" width="100%">
                                            <div class="border_bottom">
                                                <img src="images/zero.gif" height="5" width="1"><br>
                                                <div class="blwnew" style="padding-top:0px; margin-top:0px;">
                                                    <b style="font-size:13px;"><font color="#E95801">أدخل كلمات مفتاحية للبحث عن فئة</font></b>
                                                </div>
                                                
                                                <table border="0" cellpadding="0" cellspacing="0" width="525">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <input role="textbox" class="txt ui-placeholder-input" 
                                                                       name="txt_cat_mcat" id="txt_cat_mcat" 
                                                                       type="text" maxlength="60" size="33" 
                                                                       placeholder="أدخل كلمات البحث...">
                                                            </td>
                                                            <td style="cursor:pointer;">
                                                                <input name="button5" value="إضافة فئة" 
                                                                       onclick="return produtcustomcategory();" 
                                                                       type="button" id="addLeadCatBtn">
                                                            </td>
                                                            <td valign="BOTTOM">
                                                                <div class="blw1">مثال: "كرسي" أو "أثاث"</div>
                                                                <img src="images/zero.gif" height="1" width="240">
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                                <img src="images/zero.gif" height="5" width="1"><br>
                                                
                                                <div style="height:326px;">
                                                    <div id="s_result" style="display:none;">
                                                        <div class="s_text">
                                                            <div style="height:298px; overflow:auto;">
                                                                <span id="head"></span>
                                                                <span id="ajax"></span>
                                                            </div>
                                                            <img src="images/zero.gif" height="8" width="1">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- الفئات المختارة -->
                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                            <tbody>
                                <tr>
                                    <td valign="TOP" width="19">
                                        <img src="images/22.gif" height="15" vspace="3" width="19">
                                    </td>
                                    <td>
                                        <table bgcolor="#FAF4FF" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div style="margin-left:10px; padding-top:4px; padding-bottom:4px; text-align:left">
                                                            <div class="setcatnew">
                                                                <b class="kk">الفئات المختارة</b>
                                                            </div>
                                                            <div style="height:100px; overflow:auto; border:1px solid #d4b8ff; padding:5px; background-color:#fff;">
                                                                <span id="div1"></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        <div align="CENTER">
                                            <img src="images/zero.gif" height="10" width="1"><br>
                                            <input name="confirm1" id="addSOCatBtn" value="تأكيد الفئات" 
                                                   onclick="addalertlead();" type="button">
                                            <br><img src="images/zero.gif" height="10" width="1"><br>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>