<?php
/* ----------------------------------------------------------------------
   $Id: header.php,v 1.1 2007/06/08 15:20:14 r23 Exp $

   MyOOS [Shopsystem]
   https://www.oos-shop.de

   Copyright (c) 2003 - 2019 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: header.php,v 1.19 2002/04/13 16:11:52 hpdl 
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

/** ensure this file is being included by a parent file */
defined( 'OOS_VALID_MOD' ) or die( 'Direct Access to this location is not allowed.' );


?>
<!doctype html>
<html lang="<?php echo $_SESSION['iso_639_1']; ?>">
<head>
    <meta charset="<?php echo CHARSET; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
	<title><?php echo HEADING_TITLE . ' - ' . TITLE; ?></title>
	<meta http-equiv="expires" content="0" >

	<!-- Bootstrap style  --> 
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/font-awesome.min.css" rel="stylesheet">
	<link href="css/jasny-bootstrap.min.css" rel="stylesheet">
	<link href="css/icons.min.css" rel="stylesheet">	
	<link href="css/myoos.min.css" rel="stylesheet">
	
	<!-- DATETIMEPICKER-->
	<link href="js/plugins/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

	<link href="css/plugins/flag-icon/css/flag-icon.min.css" rel="stylesheet" />	
	<link rel="stylesheet" href="includes/stylesheet.css" type="text/css" >	
</head>
<body>
<?php
if ($messageStack->size > 0) {
	echo $messageStack->output();
}
?>


