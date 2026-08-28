<?php
/**
 * File: admin-right-con.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: القائمة الفرعية لإدارة المشرفين - جزء من نظام إدارة الموظفين
 * Admin management submenu - Part of employee management system
 * 
 * Features:
 * - قائمة منسدلة لإدارة بيانات المشرف
 * - روابط لتغيير اسم المستخدم وكلمة المرور
 * - متوافق مع أنماط CSS الحالية
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_ADMIN_PANEL') && !isset($_SESSION['admin_logged_in'])) {
    exit('Direct access not allowed');
}

// Get current file name for active highlighting (optional)
$currentPath = $_SERVER['SCRIPT_NAME'] ?? '';
$currentFile = basename($currentPath);
$currentFileBase = pathinfo($currentFile, PATHINFO_FILENAME);

// Define function for active menu highlighting
function isActive(string $page, string $currentFileBase): string {
    return $page === $currentFileBase ? ' class="active"' : '';
}
?>

<div class="bodyRightightCon_inner">
    <!-- Admin Management Submenu -->
    <ul id="menu" class="admin-submenu">
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="icon-user"></i> 
                Manage Admin
                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
                <li<?php echo isActive('change-user', $currentFileBase); ?>>
                    <a href="change-user.php">
                        <i class="icon-key"></i> 
                        Change User Name
                    </a>
                </li>
                <li<?php echo isActive('change-pass', $currentFileBase); ?>>
                    <a href="change-pass.php">
                        <i class="icon-lock"></i> 
                        Change Password
                    </a>
                </li>
                <!-- Change Email is commented out in original -->
                <!-- 
                <li<?php echo isActive('change-email', $currentFileBase); ?>>
                    <a href="change-email.php">
                        <i class="icon-envelope"></i> 
                        Change Email
                    </a>
                </li>
                -->
            </ul>
        </li>
    </ul>
    
    <!-- Clear float -->
    <div class="clr"></div>
</div>

<style>
/* Styles for the admin submenu */
.bodyRightightCon_inner {
    margin-bottom: 20px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #e0e5ec;
}

#menu {
    list-style: none;
    margin: 0;
    padding: 0;
}

#menu .dropdown {
    position: relative;
    display: inline-block;
}

#menu .dropdown > a {
    display: inline-block;
    padding: 10px 20px;
    background: #2c3e50;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 600;
    transition: background 0.3s ease;
}

#menu .dropdown > a:hover {
    background: #1a2632;
}

#menu .dropdown > a i {
    margin-right: 5px;
}

#menu .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    display: none;
    min-width: 200px;
    padding: 5px 0;
    margin: 2px 0 0;
    background: #fff;
    border: 1px solid rgba(0,0,0,0.15);
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.175);
    list-style: none;
}

#menu .dropdown:hover .dropdown-menu,
#menu .dropdown.open .dropdown-menu {
    display: block;
}

#menu .dropdown-menu li {
    margin: 0;
}

#menu .dropdown-menu li a {
    display: block;
    padding: 8px 20px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
}

#menu .dropdown-menu li a:hover {
    background: #f5f5f5;
    color: #2c3e50;
    padding-left: 25px;
}

#menu .dropdown-menu li a i {
    margin-right: 8px;
    color: #7f8c8d;
    width: 16px;
    text-align: center;
}

#menu .dropdown-menu li a:hover i {
    color: #2c3e50;
}

#menu .dropdown-menu li.active a {
    background: #e8f0fe;
    color: #2c3e50;
    font-weight: 600;
    border-left: 3px solid #2c3e50;
}

#menu .dropdown-menu li.active a i {
    color: #2c3e50;
}

.clr {
    clear: both;
    height: 0;
    overflow: hidden;
}

/* Responsive Design */
@media (max-width: 768px) {
    .bodyRightightCon_inner {
        padding: 5px;
    }
    
    #menu .dropdown {
        display: block;
    }
    
    #menu .dropdown > a {
        display: block;
        text-align: center;
    }
    
    #menu .dropdown-menu {
        position: static;
        width: 100%;
        box-shadow: none;
        border: 1px solid #ddd;
        margin-top: 5px;
    }
    
    #menu .dropdown:hover .dropdown-menu {
        display: none;
    }
    
    #menu .dropdown.open .dropdown-menu {
        display: block;
    }
}
</style>

<script>
// Optional JavaScript for better mobile support
document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.querySelectorAll('#menu .dropdown > a');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                var parent = this.parentElement;
                parent.classList.toggle('open');
            }
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            var dropdowns = document.querySelectorAll('#menu .dropdown');
            dropdowns.forEach(function(dropdown) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('open');
                }
            });
        }
    });
});
</script>