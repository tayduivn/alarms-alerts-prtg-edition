<?php

/*===============================================*\
|| ############################################# ||
|| # claricom.ca                                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2016 Claricom All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!file_exists('../../config.php')) die('[install.php] config.php not found');
require_once '../../config.php';

// Check if the file is accessed only from a admin if not stop the script from running
if (!JAK_USERID) die('You cannot access this file directly.');

if (!$jakuser->jakAdminaccess($jakuser->getVar("usergroupid"))) die('You cannot access this file directly.');

// Set successfully to zero
$succesfully = 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Uninstallation - Analytic Plugin</title>
	<meta charset="utf-8">
	<meta name="author" content="Claricom (http://www.claricom.ca)" />
	<link rel="stylesheet" href="../../css/stylesheet.css" type="text/css" media="screen" />
</head>
<body>

    <div class="container">
        <div class="row">
        	<div class="col-md-12">
            <h3>Uninstallation - Analytic Plugin</h3>

            <!-- Let's do the uninstall -->
            <?php if (isset($_POST['uninstall']))
            {
                // now get the plugin id for futher use
                $results = $jakdb->query('SELECT id FROM '.DB_PREFIX.'plugins WHERE name = "Analytic"');
                $rows = $results->fetch_assoc();

                if ($rows)
                {
                    $jakdb->query('DELETE FROM '.DB_PREFIX.'plugins WHERE name = "Analytic"');

                    $jakdb->query('DELETE FROM '.DB_PREFIX.'pagesgrid WHERE pluginid = "'.smartsql($rows['id']).'"');

                    $jakdb->query('DELETE FROM '.DB_PREFIX.'pluginhooks WHERE product = "analytic"');

                    $jakdb->query('DELETE FROM '.DB_PREFIX.'setting WHERE product = "analytic"');

                    $jakdb->query('DELETE FROM '.DB_PREFIX.'categories WHERE pluginid = '.smartsql($rows['id']));
                    
                    $jakdb->query('ALTER TABLE '.DB_PREFIX.'usergroup
                                    DROP `analytic`, DROP `analyticpost`');

                    $jakdb->query('DROP TABLE '.DB_PREFIX.'analytic_account');
                    $jakdb->query('DROP TABLE '.DB_PREFIX.'alarm_trigger');

                    $jakdb->query('DROP TRIGGER IF EXISTS '.DB_PREFIX.'device_after_insert');
                    $jakdb->query('DROP TRIGGER IF EXISTS '.DB_PREFIX.'device_after_update');
                    $jakdb->query('DROP TRIGGER IF EXISTS '.DB_PREFIX.'device_after_delete');                    

                }
                $succesfully = 1;
                ?>
                <div class="alert alert-success">Plugin uninstalled successfully</div>
                <?php
            }
            if (!$succesfully)
            {
                ?>
                <form name="company" method="post" action="uninstall.php" enctype="multipart/form-data">
                    <button type="submit" name="uninstall" class="btn btn-danger btn-block">Uninstall Plugin</button>
                </form>
                <?php
            } ?>

        	</div>
    	</div>

    </div><!-- #container -->
</body>
</html>