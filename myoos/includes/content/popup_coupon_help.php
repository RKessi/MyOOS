<?php
/* ----------------------------------------------------------------------

   MyOOS [Shopsystem]
   https://www.oos-shop.de

   Copyright (c) 2003 - 2019 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: popup_coupon_help.php,v 1.1.2.5 2003/05/02 01:43:29 wilt
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

  require_once MYOOS_INCLUDE_PATH . '/includes/languages/' . $sLanguage . '/gv_popup_coupon_help.php';

  $text_coupon_help = $aLang['text_coupon_help_header'];

  if (isset($_GET['cID'])) {
    $cid = intval($_GET['cID']);

    $couponstable = $oostable['coupons'];
    $sql = "SELECT coupon_amount, coupon_type, coupon_amount, coupon_minimum_order,
                   coupon_start_date, coupon_expire_date
            FROM $couponstable
            WHERE coupon_id = '" . oos_db_input($cid) . "'";
    $coupon_result = $dbconn->Execute($sql);
    $coupon = $coupon_result->fields;

    $coupons_descriptiontable = $oostable['coupons_description'];
    $sql = "SELECT coupon_name, coupon_description
            FROM " . $coupons_descriptiontable . "
            WHERE coupon_id = '" . oos_db_input($cid) . "'
              AND coupon_languages_id = '" . intval($nLanguageID) . "'";
    $coupon_desc_result = $dbconn->Execute($sql);
    $coupon_desc = $coupon_desc_result->fields;

    $text_coupon_help .= sprintf($aLang['text_coupon_help_name'], $coupon_desc['coupon_name']);
    if (oos_is_not_null($coupon_desc['coupon_description'])) $text_coupon_help .= sprintf($aLang['text_coupon_help_desc'], $coupon_desc['coupon_description']);
    $coupon_amount = $coupon['coupon_amount'];

    switch ($coupon['coupon_type']) {
      case 'F':
        $text_coupon_help .= sprintf($aLang['text_coupon_help_fixed'], $oCurrencies->format($coupon['coupon_amount']));
        break;

      case 'P':
        $text_coupon_help .= sprintf($aLang['text_coupon_help_fixed'], number_format($coupon['coupon_amount'],2). '%');
        break;

      case 'S':
        $text_coupon_help .= $aLang['text_coupon_help_freeship'];
        break;

      default:
    }
    if ($coupon['coupon_minimum_order'] > 0 ) $text_coupon_help .= sprintf($aLang['text_coupon_help_minorder'], $oCurrencies->format($coupon['coupon_minimum_order']));

    $text_coupon_help .= sprintf($aLang['text_coupon_help_date'], oos_date_short($coupon['coupon_start_date']),oos_date_short($coupon['coupon_expire_date'])); 
    $text_coupon_help .= '<strong>' . $aLang['text_coupon_help_restrict'] . '</strong>';
    $text_coupon_help .= '<br /><br />' . $aLang['text_coupon_help_categories'];

    $couponstable = $oostable['coupons'];
    $sql  = "SELECT restrict_to_categories
             FROM $couponstable
             WHERE coupon_id = '" . oos_db_input($cid) . "'";
    $coupon_get = $dbconn->Execute($sql);
    $get_result = $coupon_get->fields;

    $cat_ids = explode("[,]", $get_result['restrict_to_categories']);
    for ($i = 0; $i < count($cat_ids); $i++) {

      $categoriestable = $oostable['categories'];
      $categories_descriptiontable = $oostable['categories_description'];
      $sql = "SELECT c.categories_id, c.categories_status, cd.categories_name
              FROM $categoriestable c,
                   $categories_descriptiontable cd
              WHERE c.categories_status = '2'
                AND c.categories_id = cd.categories_id 
                AND cd.categories_languages_id = '" . intval($nLanguageID) . "'
                AND cd.categories_id = '" . oos_db_input($cat_ids[$i]) . "'";
      $result = $dbconn->Execute($sql);
      if ($row = $result->fields) {
        $cats .= '<br />' . $row["categories_name"];
      } 
    }
    if ($cats == '') $cats = '<br />NONE';

    $text_coupon_help .= $cats;
    $text_coupon_help .= '<br /><br />' .  $aLang['text_coupon_help_products'];

    $couponstable = $oostable['coupons'];
    $sql = "SELECT restrict_to_products
            FROM $couponstable
            WHERE coupon_id='" . oos_db_input($cid) . "'";
    $coupon_get = $dbconn->Execute($sql);
    $get_result = $coupon_get->fields;

    $pr_ids = explode("[,]", $get_result['restrict_to_products']);
    for ($i = 0; $i < count($pr_ids); $i++) {

      $productstable = $oostable['products'];
      $products_descriptiontable = $oostable['products_description'];
      $sql = "SELECT p.products_id, p.products_status, pd.products_name
              FROM $productstable p, 
                   $products_descriptiontable pd
              WHERE p.products_setting = '2' 
                AND p.products_id = '" . oos_db_input($pr_ids[$i]) . "'
                AND pd.products_id = p.products_id 
                AND pd.products_languages_id = '" . intval($nLanguageID) . "'";
      $result = $dbconn->Execute($sql);
      if ($row = $result->fields) {
        $prods .= '<br />' . $row["products_name"];
      }
    }
    if ($prods=='') $prods = '<br />NONE';
    $text_coupon_help .= $prods;
  } else {
    $cid = 0;
  }

  $aTemplate['popup_help'] = $sTheme . '/system/popup_help.html';

  //smarty
  require_once MYOOS_INCLUDE_PATH . '/includes/classes/class_template.php';
 $smarty = new myOOS_Smarty();

  $smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
  $cid += 0;
  $help_cache_id = $sTheme . '|popup|coupon|' . $cid . '|' . $sLanguage;

  if (!$smarty->isCached($aTemplate['popup_help'], $help_cache_id )) {

    // assign Smarty variables;
    $smarty->assign('oos_base', OOS_HTTPS_SERVER . OOS_SHOP);
    $smarty->assign('lang', $aLang);
    $smarty->assign('heading_titel', $aLang['heading_coupon_help']);
    $smarty->assign('help_text', $text_coupon_help);
    $smarty->assign('theme_image', 'themes/' . $sTheme . '/images');
    $smarty->assign('theme_css', 'themes/' . $sTheme);
  }

  // display the template
  $smarty->display($aTemplate['popup_help'], $help_cache_id);

