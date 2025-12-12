<?php
require_once 'app/init.php';
require_once 'app/controllers/accountcontroller.php';

$controller = new AccountController();

// FIX: Use $controller-> instead of $this-> because we are not inside a class
$controller->requireAdmin();

// CSV Header (Username removed as requested)
$csv = "Faculty ID,Last Name,First Name,Middle Name,Role,Email,Phone Number\n";

// Sample Data
$csv .= "FAC001,Dela Cruz,Juan,P.,Full Time Teacher,juan.delacruz@bpc.edu.ph,09123456789\n";
$csv .= "STAFF001,Garcia,Ana,L.,Guidance Office,ana.garcia@bpc.edu.ph,09171234567\n";

// Send Headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="bpc_user_import_template.csv"');
header('Pragma: no-cache');
header('Expires: 0');

echo $csv;
exit;
?>