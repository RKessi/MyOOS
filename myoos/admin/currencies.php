<?php
/* ----------------------------------------------------------------------

   MyOOS [Shopsystem]
   https://www.oos-shop.de

   Copyright (c) 2003 - 2019 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: currencies.php,v 1.45 2002/11/18 20:50:50 dgw_ 
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

define('OOS_VALID_MOD', 'yes');
require 'includes/main.php';

// define our localization functions
require 'includes/functions/function_localization.php';

require 'includes/classes/class_currencies.php';
$currencies = new currencies();

$nPage = (!isset($_GET['page']) || !is_numeric($_GET['page'])) ? 1 : intval($_GET['page']); 
$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (!empty($action)) {
	switch ($action) {
		case 'insert':
		case 'save':
			$currency_id = oos_db_prepare_input($_GET['cID']);

			$title = oos_db_prepare_input($_POST['title']);
			$code = oos_db_prepare_input($_POST['code']);
			$symbol_left = oos_db_prepare_input($_POST['symbol_left']);
			$symbol_right = oos_db_prepare_input($_POST['symbol_right']);
			$decimal_point = oos_db_prepare_input($_POST['decimal_point']);
			$thousands_point = oos_db_prepare_input($_POST['thousands_point']);
			$decimal_places = oos_db_prepare_input($_POST['decimal_places']);
			$currency_value = oos_db_prepare_input($_POST['currency_value']);

			$sql_data_array = array('title' => $title,
                                'code' => $code,
                                'symbol_left' => $symbol_left,
                                'symbol_right' => $symbol_right,
                                'decimal_point' => $decimal_point,
                                'thousands_point' => $thousands_point,
                                'decimal_places' => $decimal_places,
                                'value' => $currency_value);

			if ($action == 'insert') {
				oos_db_perform($oostable['currencies'], $sql_data_array);
				$currency_id = $dbconn->Insert_ID();
			} elseif ($action == 'save') {
				oos_db_perform($oostable['currencies'], $sql_data_array, 'UPDATE', "currencies_id = '" . oos_db_input($currency_id) . "'");
			}

			if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
				$dbconn->Execute("UPDATE " . $oostable['configuration'] . " SET configuration_value = '" . oos_db_input($code) . "' WHERE configuration_key = 'DEFAULT_CURRENCY'");
			}
			oos_redirect_admin(oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $currency_id));
			break;

		case 'deleteconfirm':
			$currencies_id = oos_db_prepare_input($_GET['cID']);

			$currency_result = $dbconn->Execute("SELECT currencies_id FROM " . $oostable['currencies'] . " WHERE code = '" . DEFAULT_CURRENCY . "'");
			$currency = $currency_result->fields;
			if ($currency['currencies_id'] == $currencies_id) {
				$dbconn->Execute("UPDATE " . $oostable['configuration'] . " SET configuration_value = '' WHERE configuration_key = 'DEFAULT_CURRENCY'");
			}

			$dbconn->Execute("DELETE FROM " . $oostable['currencies'] . " WHERE currencies_id = '" . oos_db_input($currencies_id) . "'");

			oos_redirect_admin(oos_href_link_admin($aContents['currencies'], 'page=' . $nPage));
			break;

		case 'update':
			$currency_result = $dbconn->Execute("SELECT currencies_id, code FROM " . $oostable['currencies']);
			while ($currency = $currency_result->fields) {
				$quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
				$rate = $quote_function($currency['code']);

				if (empty($rate) && (oos_is_not_null(CURRENCY_SERVER_BACKUP)) ) {
					$quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
					$rate = $quote_function($currency['code']);
				}
				if (oos_is_not_null($rate)) {
					$dbconn->Execute("UPDATE " . $oostable['currencies'] . " SET value = '" . $rate . "', last_updated = now() WHERE currencies_id = '" . $currency['currencies_id'] . "'");
				}
				// Move that ADOdb pointer!
				$currency_result->MoveNext();
			}
			oos_redirect_admin(oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $_GET['cID']));
			break;

      case 'delete':
			$currencies_id = oos_db_prepare_input($_GET['cID']);

			$currency_result = $dbconn->Execute("SELECT code FROM " . $oostable['currencies'] . " WHERE currencies_id = '" . oos_db_input($currencies_id) . "'");
			$currency = $currency_result->fields;

			$remove_currency = true;
			if ($currency['code'] == DEFAULT_CURRENCY) {
				$remove_currency = false;
				$messageStack->add(ERROR_REMOVE_DEFAULT_CURRENCY, 'error');
			}
			break;
		}
	}
	require 'includes/header.php'; 
?>
<div class="wrapper">
	<!-- Header //-->
	<header class="topnavbar-wrapper">
		<!-- Top Navbar //-->
		<?php require 'includes/menue.php'; ?>
	</header>
	<!-- END Header //-->
	<aside class="aside">
		<!-- Sidebar //-->
		<div class="aside-inner">
			<?php require 'includes/blocks.php'; ?>
		</div>
		<!-- END Sidebar (left) //-->
	</aside>
	
	<!-- Main section //-->
	<section>
		<!-- Page content //-->
		<div class="content-wrapper">
		
			<!-- Breadcrumbs //-->
			<div class="content-heading">
				<div class="col-lg-12">
					<h2><?php echo HEADING_TITLE; ?></h2>
					<ol class="breadcrumb">
						<li class="breadcrumb-item">
							<?php echo '<a href="' . oos_href_link_admin($aContents['default']) . '">' . HEADER_TITLE_TOP . '</a>'; ?>
						</li>
						<li class="breadcrumb-item">
							<?php echo '<a href="' . oos_href_link_admin($aContents['currencies'], 'selected_box=localization') . '">' . BOX_HEADING_LOCALIZATION . '</a>'; ?>
						</li>
						<li class="breadcrumb-item active">
							<strong><?php echo HEADING_TITLE; ?></strong>
						</li>
					</ol>
				</div>
			</div>
			<!-- END Breadcrumbs //-->
			
			<div class="wrapper wrapper-content">
				<div class="row">
					<div class="col-lg-12">	
<!-- body_text //-->
	<div class="table-responsive">
		<table class="table w-100">
          <tr>
            <td valign="top">			
				<table class="table table-striped table-hover w-100">
					<thead class="thead-dark">
						<tr>
							<th><?php echo TABLE_HEADING_CURRENCY_NAME; ?></th>
							<th><?php echo TABLE_HEADING_CURRENCY_CODES; ?></th>
							<th class="text-right"><?php echo TABLE_HEADING_CURRENCY_VALUE; ?></th>
							<th class="text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
						</tr>	
					</thead>
<?php
  $currency_result_raw = "SELECT currencies_id, title, code, symbol_left, symbol_right, decimal_point, 
                                 thousands_point, decimal_places, last_updated, value 
                          FROM " . $oostable['currencies'] . " 
                          ORDER BY title";
  $currency_split = new splitPageResults($nPage, MAX_DISPLAY_SEARCH_RESULTS, $currency_result_raw, $currency_result_numrows);
  $currency_result = $dbconn->Execute($currency_result_raw);
  while ($currency = $currency_result->fields) {
    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $currency['currencies_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($currency);
    }

    if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id)) {
      echo '                  <tr onclick="document.location.href=\'' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr onclick="document.location.href=\'' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $currency['currencies_id']) . '\'">' . "\n";
    }

    if (DEFAULT_CURRENCY == $currency['code']) {
      echo '                <td><b>' . $currency['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td>' . $currency['title'] . '</td>' . "\n";
    }
?>
                <td><?php echo $currency['code']; ?></td>
                <td class="text-right"><?php echo number_format($currency['value'], 8); ?></td>
                <td class="text-right"><?php if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id) ) { echo '<button class="btn btn-info" type="button"><i class="fa fa-check"></i></button>'; } else { echo '<a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $currency['currencies_id']) . '"><button class="btn btn-default" type="button"><i class="fa fa-eye-slash"></i></button></a>'; } ?>&nbsp;</td>
              </tr>
<?php
    // Move that ADOdb pointer!
    $currency_result->MoveNext();
  }

  // Close result set
  $currency_result->Close();
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $currency_split->display_count($currency_result_numrows, MAX_DISPLAY_SEARCH_RESULTS, $nPage, TEXT_DISPLAY_NUMBER_OF_CURRENCIES); ?></td>
                    <td class="smallText" align="right"><?php echo $currency_split->display_links($currency_result_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $nPage); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td><?php if (CURRENCY_SERVER_PRIMARY) { echo '<a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id . '&action=update') . '">' . oos_button('update_currencies', IMAGE_UPDATE_CURRENCIES) . '</a>'; } ?></td>
                    <td class="text-right"><?php echo '<a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id . '&action=new') . '">' . oos_button('new_currency',IMAGE_NEW_CURRENCY) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CURRENCY . '</b>');

      $contents = array('form' => oos_draw_form('id', 'currencies', $aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id . '&action=insert', 'post', FALSE));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_TITLE . '<br />' . oos_draw_input_field('title'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_CODE . '<br />' . oos_draw_input_field('code'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br />' . oos_draw_input_field('symbol_left'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br />' . oos_draw_input_field('symbol_right'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br />' . oos_draw_input_field('decimal_point'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br />' . oos_draw_input_field('thousands_point'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br />' . oos_draw_input_field('decimal_places'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_VALUE . '<br />' . oos_draw_input_field('currency_value'));
      $contents[] = array('text' => '<br />' . oos_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br />' . oos_submit_button('insert', BUTTON_INSERT) . ' <a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $_GET['cID']) . '">' . oos_button('cancel', BUTTON_CANCEL) . '</a>');
      break;

    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</b>');

      $contents = array('form' => oos_draw_form('id', 'currencies', $aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id . '&action=save', 'post', FALSE));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_TITLE . '<br />' . oos_draw_input_field('title', $cInfo->title));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_CODE . '<br />' . oos_draw_input_field('code', $cInfo->code));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br />' . oos_draw_input_field('symbol_left', $cInfo->symbol_left));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br />' . oos_draw_input_field('symbol_right', $cInfo->symbol_right));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br />' . oos_draw_input_field('decimal_point', $cInfo->decimal_point));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br />' . oos_draw_input_field('thousands_point', $cInfo->thousands_point));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br />' . oos_draw_input_field('decimal_places', $cInfo->decimal_places));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_VALUE . '<br />' . oos_draw_input_field('currency_value', $cInfo->value));
      if (DEFAULT_CURRENCY != $cInfo->code) $contents[] = array('text' => '<br />' . oos_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br />' . oos_submit_button('update', IMAGE_UPDATE) . ' <a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id) . '">' . oos_button('cancel', BUTTON_CANCEL) . '</a>');
      break;

    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CURRENCY . '</b>');

      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $cInfo->title . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . (($remove_currency) ? '<a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id . '&action=deleteconfirm') . '">' . oos_button('delete',  BUTTON_DELETE) . '</a>' : '') . ' <a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id) . '">' . oos_button('cancel', BUTTON_CANCEL) . '</a>');
      break;

    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id . '&action=edit') . '">' . oos_button('edit', BUTTON_EDIT) . '</a> <a href="' . oos_href_link_admin($aContents['currencies'], 'page=' . $nPage . '&cID=' . $cInfo->currencies_id . '&action=delete') . '">' . oos_button('delete',  BUTTON_DELETE) . '</a>');
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_TITLE . ' ' . $cInfo->title);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_CODE . ' ' . $cInfo->code);
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . ' ' . $cInfo->symbol_left);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_SYMBOL_RIGHT . ' ' . $cInfo->symbol_right);
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . ' ' . $cInfo->decimal_point);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_THOUSANDS_POINT . ' ' . $cInfo->thousands_point);
        $contents[] = array('text' => TEXT_INFO_CURRENCY_DECIMAL_PLACES . ' ' . $cInfo->decimal_places);
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_LAST_UPDATED . ' ' . oos_date_short($cInfo->last_updated));
        $contents[] = array('text' => TEXT_INFO_CURRENCY_VALUE . ' ' . number_format($cInfo->value, 8));
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_EXAMPLE . '<br />' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code));
      }
      break;
  }

    if ( (oos_is_not_null($heading)) && (oos_is_not_null($contents)) ) {
?>
	<td class="w-25">
		<table class="table table-striped">
<?php
		$box = new box;
		echo $box->infoBox($heading, $contents);  
?>
		</table> 
	</td> 
<?php
  }
?>
          </tr>
        </table>
	</div>
<!-- body_text_eof //-->

				</div>
			</div>
        </div>

		</div>
	</section>
	<!-- Page footer //-->
	<footer>
		<span>&copy; 2019 - <a href="https://www.oos-shop.de" target="_blank" rel="noopener">MyOOS [Shopsystem]</a></span>
	</footer>
</div>

<?php 
	require 'includes/bottom.php';
	require 'includes/nice_exit.php';
?>