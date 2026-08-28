<?php
declare(strict_types=1);

if (!isset($_SESSION)) {
    session_start();
}

require_once "../common.php";

// استخدام الدالة الصحيحة للتحقق من تسجيل الدخول
check_admin_login();
require_once "../lib/pagination.php";



// Check if user is logged in


/**
 * Class CountryStatesManager
 * 
 * Handles country and state management operations
 */
class CountryStatesManager {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
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
     * @return array List of countries
     */
    public function getActiveCountries(): array {
        $countries = [];
        $sql = "SELECT cn_id, cn_name FROM country WHERE cn_status = 1 ORDER BY cn_name";
        $result = mysqli_query($this->db, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $countries[] = [
                    'id' => (int)$row['cn_id'],
                    'name' => $row['cn_name']
                ];
            }
        }
        
        return $countries;
    }
    
    /**
     * Get states for a country
     * 
     * @param int $countryId Country ID
     * @return array List of states
     */
    public function getStatesByCountry(int $countryId): array {
        $states = [];
        
        $sql = "SELECT st_id, st_name FROM states WHERE st_cn_id = ? AND st_status = 1 ORDER BY st_name";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $countryId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $states[] = [
                    'id' => (int)$row['st_id'],
                    'name' => $row['st_name']
                ];
            }
            mysqli_stmt_close($stmt);
        }
        
        return $states;
    }
    
    /**
     * Add a new state
     * 
     * @param int $countryId Country ID
     * @param string $stateName State name
     * @return bool Success status
     */
    public function addState(int $countryId, string $stateName): bool {
        if ($countryId <= 0 || empty(trim($stateName))) {
            return false;
        }
        
        $sql = "INSERT INTO states (st_cn_id, st_name, st_status) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "is", $countryId, $stateName);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Update state name
     * 
     * @param int $stateId State ID
     * @param string $stateName New state name
     * @return bool Success status
     */
    public function updateState(int $stateId, string $stateName): bool {
        if ($stateId <= 0 || empty(trim($stateName))) {
            return false;
        }
        
        $sql = "UPDATE states SET st_name = ? WHERE st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $stateName, $stateId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Delete state (soft delete - set status to 0)
     * 
     * @param int $stateId State ID
     * @return bool Success status
     */
    public function deleteState(int $stateId): bool {
        if ($stateId <= 0) {
            return false;
        }
        
        $sql = "UPDATE states SET st_status = 0 WHERE st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $stateId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Get country name by ID
     * 
     * @param int $countryId Country ID
     * @return string|null Country name
     */
    public function getCountryName(int $countryId): ?string {
        $sql = "SELECT cn_name FROM country WHERE cn_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $countryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['cn_name'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
}

// Initialize manager
$manager = new CountryStatesManager($con);
$countries = $manager->getActiveCountries();
?>

<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php" ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="setting-view.php">Manage Settings</a>
                    </li>
                    <li class="active">Country & States Manager</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Countries & States
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Select a country to manage its states
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <!-- Country Selection -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label no-padding-right">Select Country:</label>
                            <div class="col-sm-8">
                                <select name="cun" id="cun" class="chosen-select" onchange="ShowState(this.value)" style="width:300px;">
                                    <option value="0">- Select Country -</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?php echo (int)$country['id']; ?>">
                                            <?php echo htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <br>
                        
                        <!-- States List Container -->
                        <div id="states_list">
                            <div class="alert alert-info">
                                <i class="icon-hand-right"></i> Please select a country to view its states.
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <br clear="all" />
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript Functions for State Management -->
<script type="text/javascript">
    function ShowState(cid) {
        if (cid == 0) {
            $('#states_list').html('<div class="alert alert-info"><i class="icon-hand-right"></i> Please select a country to view its states.</div>');
            return;
        }
        
        $.get("states_show.php", {cid: cid}, function(data) {
            $('#states_list').html(data);
        }).fail(function() {
            $('#states_list').html('<div class="alert alert-danger">Failed to load states. Please try again.</div>');
        });
    }
    
    function DelState(hid) {
        if (confirm("Are you sure you want to delete this state?")) {
            $.get("del_states.php", {hid: hid}, function(data) {
                ShowState(data);
            }).fail(function() {
                alert('Failed to delete state. Please try again.');
            });
        }
    }
    
    function addState() {
        var states_add = $('input#states_add').val().trim();
        if (states_add != "") {
            var cun = $('select#cun').val();
            if (cun == 0) {
                alert("Please select a country first.");
                return;
            }
            
            $.get("states_add.php", {
                states_add: states_add,
                cun: cun
            }, function(data) {
                ShowState(cun);
                $('input#states_add').val('');
                CanState();
            }).fail(function() {
                alert('Failed to add state. Please try again.');
            });
        } else {
            alert("Please enter a state name.");
        }
    }
    
    function CanState() {
        $('#save_link').show("fast");
        $('#save_add').hide("fast");
        $('#input_add').hide("fast");
        $('#cancel_add').hide("fast");
    }
    
    function ShowaddState() {
        $('#save_link').hide("fast");
        $('#save_add').show("fast");
        $('#input_add').show("fast");
        $('#cancel_add').show("fast");
    }
    
    function ShowEditState(hid) {
        $('#display_' + hid).hide("fast");
        $('#edit_' + hid).hide("fast");
        $('#save_' + hid).show("fast");
        $('#input_' + hid).show("fast");
    }
    
    function EditState(hid) {
        var states_inp = $('input#states_' + hid).val().trim();
        if (states_inp != "") {
            $.get("states_edit.php", {
                hid: hid,
                states_inp: states_inp
            }, function(data) {
                $('#display_' + hid).html(data);
                $('#display_' + hid).show("fast");
                $('#edit_' + hid).show("fast");
                $('#save_' + hid).hide("fast");
                $('#input_' + hid).hide("fast");
            }).fail(function() {
                alert('Failed to update state. Please try again.');
            });
        } else {
            alert("Please enter a state name.");
        }
    }
</script>

<!-- JavaScript includes and initialization -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>

<!--[if lte IE 8]>
<script src="assets/js/excanvas.min.js"></script>
<![endif]-->

<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="assets/js/chosen.jquery.min.js"></script>
<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
<script src="assets/js/date-time/moment.min.js"></script>
<script src="assets/js/date-time/daterangepicker.min.js"></script>
<script src="assets/js/bootstrap-colorpicker.min.js"></script>
<script src="assets/js/jquery.knob.min.js"></script>
<script src="assets/js/jquery.autosize.min.js"></script>
<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="assets/js/jquery.maskedinput.min.js"></script>
<script src="assets/js/bootstrap-tag.min.js"></script>

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize chosen selects
        $(".chosen-select").chosen({width: '300px'});
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // If country is selected in URL, load states
        var urlParams = new URLSearchParams(window.location.search);
        var countryId = urlParams.get('country');
        if (countryId) {
            $('#cun').val(countryId).trigger('chosen:updated');
            ShowState(countryId);
        }
    });
</script>

<style>
    .btn {
        margin-right: 5px;
    }
    .action-icons {
        white-space: nowrap;
    }
    .state-row {
        border: 1px solid #ddd;
        margin-bottom: 5px;
        padding: 8px;
        border-radius: 3px;
        background: #f9f9f9;
    }
    .state-row:hover {
        background: #f0f0f0;
    }
    .add-state-row {
        background: #e8f4f8;
        border: 1px dashed #5bc0de;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 3px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>