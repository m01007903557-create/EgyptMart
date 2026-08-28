<?php
/**
 * File: showCountryList.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إدارة قائمة الدول مع إمكانية التعديل والحذف ورفع الأعلام
 * Country list management with edit, delete and flag upload capabilities
 * 
 * Features:
 * - عرض جميع الدول النشطة
 * - تعديل معلومات الدولة (الاسم، الكود، العملة، رمز الهاتف)
 * - رفع وتغيير علم الدولة
 * - حذف الدول
 * - نموذج منبثق للتعديل
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
check_admin_login();

/**
 * Class CountryListManager
 * 
 * Handles country list display and management
 */
class CountryListManager {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Base path for flag images */
    private string $flagPath = '../images/country_flag/';
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * Get all active countries
     * 
     * @return mysqli_result|false Query result
     */
    public function getActiveCountries() {
        $sql = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name";
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Get country details by ID
     * 
     * @param int $countryId Country ID
     * @return array|null Country details
     */
    public function getCountryDetails(int $countryId): ?array {
        $sql = "SELECT * FROM country WHERE cn_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $countryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Check if flag file exists
     * 
     * @param string $filename Flag filename
     * @return bool True if file exists
     */
    public function flagExists(string $filename): bool {
        return !empty($filename) && file_exists($this->flagPath . $filename);
    }
    
    /**
     * Get flag image HTML
     * 
     * @param string $filename Flag filename
     * @param string $countryName Country name
     * @param int $height Image height
     * @param int $width Image width
     * @return string HTML img tag
     */
    public function getFlagHtml(string $filename, string $countryName, int $height = 16, int $width = 23): string {
        if ($this->flagExists($filename)) {
            return '<img src="' . $this->flagPath . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . '" 
                     alt="' . htmlspecialchars(ucwords($countryName), ENT_QUOTES, 'UTF-8') . '" 
                     align="top" height="' . $height . '" width="' . $width . '"/>';
        }
        
        return '<span class="text-muted">No flag</span>';
    }
    
    /**
     * Format country display text
     * 
     * @param object $country Country object
     * @return string Formatted text
     */
    public function formatCountryText(object $country): string {
        return ucwords($country->cn_name ?? '') . ' - ' . 
               ($country->cn_code ?? '') . ' - ' . 
               ($country->cn_currency ?? '') . ' - ' . 
               ($country->cn_ph ?? '');
    }
    
    /**
     * Generate unique ID for JavaScript
     * 
     * @param int $countryId Country ID
     * @param string $type Type of element
     * @return string Unique ID
     */
    public function getElementId(int $countryId, string $type): string {
        return $type . '_' . $countryId;
    }
}

// Initialize manager
$countryManager = new CountryListManager($con);

// Get all active countries
$countries = $countryManager->getActiveCountries();
$countryCount = $countries ? mysqli_num_rows($countries) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Country Management</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/ace.min.css" />
    <link rel="stylesheet" type="text/css" href="../uploadifive/uploadifive.css">
    
    <!-- jQuery -->
    <script type="text/javascript" src="../js/jquery-1.2.1.min.js"></script>
    
    <!-- Uploadifive -->
    <script src="../uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
    
    <style>
        .backLayer {
            position: fixed;
            background: white;
            z-index: 9999999999999;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            max-width: 800px;
            width: 90%;
        }
        
        .background_overlay {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 999999;
            background: black;
            opacity: 0.4;
        }
        
        .modal-dialog {
            width: 100%;
            margin: 0;
        }
        
        .modal-content {
            border: none;
            border-radius: 5px;
        }
        
        .modal-header {
            padding: 15px 20px;
            background: #f5f5f5;
            border-bottom: 1px solid #e5e5e5;
            border-radius: 5px 5px 0 0;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            background: #f5f5f5;
            border-top: 1px solid #e5e5e5;
            border-radius: 0 0 5px 5px;
        }
        
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .badge-info {
            background-color: #5bc0de;
            color: white;
        }
        
        .badge-success {
            background-color: #5cb85c;
            color: white;
        }
        
        .badge-danger {
            background-color: #d9534f;
            color: white;
        }
        
        .badge:hover {
            opacity: 0.8;
        }
        
        .country-row {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 3px;
            background: #f9f9f9;
        }
        
        .country-row:hover {
            background: #f0f0f0;
        }
        
        .action-buttons {
            white-space: nowrap;
        }
        
        #queue {
            margin-top: 10px;
        }
        
        .uploadifive-queue-item {
            margin-top: 5px;
            padding: 5px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
    </style>
</head>
<body>

<table id="sample-table-2" class="table table-striped table-bordered table-hover">
    <?php if ($countryCount > 0): ?>
        <?php 
        $rowCount = 0;
        while ($country = mysqli_fetch_object($countries)): 
            $displayId = $countryManager->getElementId((int)$country->cn_id, 'display');
            $editId = $countryManager->getElementId((int)$country->cn_id, 'edit');
            $saveId = $countryManager->getElementId((int)$country->cn_id, 'save');
            $delId = $countryManager->getElementId((int)$country->cn_id, 'del');
            $cancelId = $countryManager->getElementId((int)$country->cn_id, 'cancel');
            $formId = 'job_form' . $country->cn_id;
        ?>
        
        <?php if ($rowCount % 2 == 0): ?>
            <tr>
        <?php endif; ?>
        
        <td width="590px;" class="country-row">
            <table style="width:100%;">
                <tr>
                    <td style="width: 86%; border:0px;">
                        <span id="<?php echo $displayId; ?>">
                            <?php 
                            echo $countryManager->getFlagHtml($country->cn_flag ?? '', $country->cn_name ?? '');
                            echo '&nbsp;' . htmlspecialchars($countryManager->formatCountryText($country), ENT_QUOTES, 'UTF-8');
                            ?>
                        </span>
                    </td>
                    <td style="width: 12%; border:0px;" class="action-buttons">
                        <span id="<?php echo $editId; ?>">
                            <a id="id-btn-job<?php echo (int)$country->cn_id; ?>" 
                               role="button" 
                               class="editCun ajax badge badge-info" 
                               title="Edit">
                                <i class="icon-edit"></i>
                            </a>
                        </span>
                        <span id="<?php echo $saveId; ?>" style="display:none;">
                            <a href="javascript:EditCountry(<?php echo (int)$country->cn_id; ?>)" 
                               class="ajax badge badge-success" 
                               title="Update">
                                <i class="icon-check"></i>
                            </a>
                        </span>
                    </td>
                    <td style="width: 4%; border:0px;" class="action-buttons">
                        <span id="<?php echo $delId; ?>">
                            <a href="javascript:DelCountry(<?php echo (int)$country->cn_id; ?>)" 
                               class="badge badge-danger" 
                               title="Delete"
                               onclick="return confirm('Are you sure you want to delete this country?');">
                                <i class="icon-trash"></i>
                            </a>
                        </span>
                        <span id="<?php echo $cancelId; ?>" style="display:none;">
                            <a href="javascript:CancelEditCountry(<?php echo (int)$country->cn_id; ?>)" 
                               class="ajax badge badge-danger" 
                               title="Cancel">
                                <i class="icon-remove"></i>
                            </a>
                        </span>
                    </td>
                </tr>
            </table>
        </td>
        
        <?php if ($rowCount % 2 == 1): ?>
            </tr>
        <?php endif; ?>
        
        <!-- Edit Popup Modal -->
        <div id="<?php echo $formId; ?>" class="backLayer" style="left: 25%; top: 5%; display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="Edit_Country_<?php echo (int)$country->cn_id; ?>" 
                          name="Add_New_Country" 
                          action="" 
                          method="post" 
                          enctype="multipart/form-data">
                        
                        <div class="modal-header">
                            <button type="button" class="close" id="clse_job<?php echo (int)$country->cn_id; ?>">&times;</button>
                            <h4 class="blue bigger">Edit Country: <?php echo htmlspecialchars($country->cn_name ?? '', ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>

                        <div class="modal-body overflow-visible">
                            <div class="row">
                                <div class="col-xs-12 col-sm-5">
                                    <div class="space"></div>
                                    
                                    <input type="hidden" id="cn_id" name="cn_id" value="<?php echo (int)$country->cn_id; ?>"/>

                                    <!-- Uploadifive Script -->
                                    <script type="text/javascript">
                                        jQuery(function() {
                                            jQuery('#file_upload<?php echo (int)$country->cn_id; ?>').uploadifive({
                                                'auto'     : true,
                                                'formData' : {'cn_id' : '<?php echo (int)$country->cn_id; ?>'},
                                                'queueID'  : 'queue',
                                                'debug'    : false,
                                                'method'   : 'post',
                                                'uploadScript' : 'editCountryImg.php',
                                                'onUploadComplete' : function(file, data) {
                                                    showCountryImg(<?php echo (int)$country->cn_id; ?>);
                                                }
                                            });
                                        });
                                    </script>
                                    
                                    <div>
                                        <div id="img_disp_<?php echo (int)$country->cn_id; ?>">
                                            <?php 
                                            echo $countryManager->getFlagHtml($country->cn_flag ?? '', $country->cn_name ?? '', 18, 26);
                                            ?>
                                        </div>
                                        <div id="drop" style="padding-left:10px; float:right;">
                                            <input type="file" id="file_upload<?php echo (int)$country->cn_id; ?>" name="file_upload"/>
                                            <small>Only accepts PNG image.</small>
                                        </div>
                                        <div id="queue"></div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-7">
                                    <div class="form-group">
                                        <label for="form-field-username">Country Name</label>
                                        <div>
                                            <input id="cn_name_<?php echo (int)$country->cn_id; ?>" 
                                                   name="cn_name_<?php echo (int)$country->cn_id; ?>" 
                                                   class="input-large form-control" 
                                                   type="text" 
                                                   placeholder="Country Name" 
                                                   value="<?php echo htmlspecialchars($country->cn_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                    </div>

                                    <div class="space-4"></div>

                                    <div class="form-group">
                                        <label for="form-field-username">Country Code</label>
                                        <div>
                                            <input id="cn_code_<?php echo (int)$country->cn_id; ?>" 
                                                   name="cn_code_<?php echo (int)$country->cn_id; ?>" 
                                                   class="input-large form-control" 
                                                   type="text" 
                                                   placeholder="Country Code (e.g., EG, US)" 
                                                   value="<?php echo htmlspecialchars($country->cn_code ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                    </div>

                                    <div class="space-4"></div>

                                    <div class="form-group">
                                        <label for="form-field-username">Currency Code</label>
                                        <div>
                                            <input id="cn_currency_<?php echo (int)$country->cn_id; ?>" 
                                                   name="cn_currency_<?php echo (int)$country->cn_id; ?>" 
                                                   class="input-medium form-control" 
                                                   type="text" 
                                                   placeholder="Currency Code (e.g., USD, EGP)" 
                                                   value="<?php echo htmlspecialchars($country->cn_currency ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                    </div>

                                    <div class="space-4"></div>

                                    <div class="form-group">
                                        <label for="form-field-first">Phone Code</label>
                                        <div>
                                            <input id="cn_ph_<?php echo (int)$country->cn_id; ?>" 
                                                   name="cn_ph_<?php echo (int)$country->cn_id; ?>" 
                                                   class="input-medium form-control" 
                                                   type="text" 
                                                   placeholder="Phone Code (e.g., +20, +1)" 
                                                   value="<?php echo htmlspecialchars($country->cn_ph ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-sm btn-primary" type="button" onclick="updCountry(<?php echo (int)$country->cn_id; ?>);">
                                <i class="icon-ok"></i>
                                Save Changes
                            </button>
                            <button class="btn btn-sm" id="clse_job<?php echo (int)$country->cn_id; ?>">
                                <i class="icon-remove"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <?php 
        $rowCount++;
        endwhile; 
        
        // Close row if last row is incomplete
        if ($rowCount % 2 == 1): 
        ?>
            <td></td></tr>
        <?php endif; ?>
        
    <?php else: ?>
        <tr>
            <td colspan="2" class="text-center">
                No countries found. Please add countries to the database.
            </td>
        </tr>
    <?php endif; ?>
</table>

<!-- Background Overlay -->
<div class="background_overlay" style="display: none;"></div>

<!-- JavaScript Functions -->
<script type="text/javascript">
    $(document).ready(function() {
        <?php if ($countryCount > 0): ?>
            <?php 
            mysqli_data_seek($countries, 0);
            while ($country = mysqli_fetch_object($countries)): 
            ?>
                // Open popup
                $("#id-btn-job<?php echo (int)$country->cn_id; ?>").click(function() {
                    $("#job_form<?php echo (int)$country->cn_id; ?>").fadeIn(200);
                    $(".background_overlay").fadeIn(200);
                    positionCookiePopup(<?php echo (int)$country->cn_id; ?>);
                });
                
                // Close popup
                $("#clse_job<?php echo (int)$country->cn_id; ?>, .background_overlay").click(function() {
                    $("#job_form<?php echo (int)$country->cn_id; ?>").fadeOut(200);
                    $(".background_overlay").fadeOut(200);
                });
                
            <?php endwhile; ?>
        <?php endif; ?>
    });

    function positionCookiePopup(countryId) {
        var popup = $("#job_form" + countryId);
        if (!popup.is(':visible')) {
            return;
        }
        popup.css({
            left: ($(window).width() - popup.width()) / 2,
            top: ($(window).height() - popup.height()) / 5,
            position: 'fixed'
        });
    }

    $(window).bind('resize', function() {
        <?php if ($countryCount > 0): ?>
            <?php 
            mysqli_data_seek($countries, 0);
            while ($country = mysqli_fetch_object($countries)): 
            ?>
                positionCookiePopup(<?php echo (int)$country->cn_id; ?>);
            <?php endwhile; ?>
        <?php endif; ?>
    });

    function EditCountry(countryId) {
        // Hide display and edit buttons, show save and cancel
        $('#display_' + countryId).hide();
        $('#edit_' + countryId).hide();
        $('#save_' + countryId).show();
        $('#cancel_' + countryId).show();
        
        // Show input fields or perform edit actions
        // This function should be implemented based on your needs
    }

    function CancelEditCountry(countryId) {
        // Show display and edit buttons, hide save and cancel
        $('#display_' + countryId).show();
        $('#edit_' + countryId).show();
        $('#save_' + countryId).hide();
        $('#cancel_' + countryId).hide();
        
        // Hide edit form
        $("#job_form" + countryId).fadeOut(200);
        $(".background_overlay").fadeOut(200);
    }

    function DelCountry(countryId) {
        if (confirm('Are you sure you want to delete this country?')) {
            // Perform delete action
            $.post('deleteCountry.php', {id: countryId}, function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting country: ' + data.error);
                }
            }, 'json').fail(function() {
                alert('Error connecting to server');
            });
        }
    }

    function updCountry(countryId) {
        var cn_name = $('#cn_name_' + countryId).val();
        var cn_code = $('#cn_code_' + countryId).val();
        var cn_currency = $('#cn_currency_' + countryId).val();
        var cn_ph = $('#cn_ph_' + countryId).val();
        
        // Validate inputs
        if (!cn_name.trim()) {
            alert('Country name is required');
            return;
        }
        
        // Perform update action
        $.post('updateCountry.php', {
            id: countryId,
            cn_name: cn_name,
            cn_code: cn_code,
            cn_currency: cn_currency,
            cn_ph: cn_ph
        }, function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating country: ' + data.error);
            }
        }, 'json').fail(function() {
            alert('Error connecting to server');
        });
    }

    function showCountryImg(countryId) {
        // Refresh the displayed image after upload
        $.get('get_country_flag_path.php', {id: countryId}, function(path) {
            $('#img_disp_' + countryId).html('<img src="' + path + '" alt="Flag" height="18" width="26"/>');
        });
    }
</script>

</body>
</html>

<?php ob_end_flush(); ?>