<?php
/**
 * create the breadcrumb trail
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson Wed Oct 10 07:03:50 2018 -0400 Modified in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$breadcrumb->add(HEADER_TITLE_CATALOG, zen_href_link(FILENAME_DEFAULT));
/**
 * add category names or the manufacturer name to the breadcrumb trail
 */
if (!isset($robotsNoIndex)) $robotsNoIndex = false;
// might need isset($_GET['cPath']) later ... right now need $cPath or breaks breadcrumb from sidebox etc.
if (isset($cPath_array) && isset($cPath)) {
  for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
    $categories_query = "select categories_name
                           from " . TABLE_CATEGORIES_DESCRIPTION . "
                           where categories_id = '" . (int)$cPath_array[$i] . "'
                           and language_id = '" . (int)$_SESSION['languages_id'] . "'";

    $categories = $db->Execute($categories_query);
    if ($categories->RecordCount() > 0) {
      $breadcrumb->add($categories->fields['categories_name'], zen_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
    } elseif(SHOW_CATEGORIES_ALWAYS == 0) {
      // if invalid, set the robots noindex/nofollow for this page
      $robotsNoIndex = true;
      break;
    }
  }
}
/**
 * add get terms (e.g manufacturer, music genre, record company or other user defined selector) to breadcrumb
 */
$sql = "select *
        from " . TABLE_GET_TERMS_TO_FILTER;
$get_terms = $db->execute($sql);
while (!$get_terms->EOF) {
	if (isset($_GET[$get_terms->fields['get_term_name']])) {
		$sql = "select " . $get_terms->fields['get_term_name_field'] . "
		        from " . constant($get_terms->fields['get_term_table']) . "
		        where " . $get_terms->fields['get_term_name'] . " =  " . (int)$_GET[$get_terms->fields['get_term_name']];
		$get_term_breadcrumb = $db->execute($sql);
    if ($get_term_breadcrumb->RecordCount() > 0) {
// BEGIN CEON URI MAPPING 1 of 2
	  // Set the required parameters so that an attempt can be made to map the link to any static URI for the
	  // filtered page
	  $typefilter_parameters = '';
	  
	  if ($get_terms->fields['get_term_name'] != 'manufacturers_id') {
		$typefilter_parameters = 'typefilter=' . str_replace('_id', '', $get_terms->fields['get_term_name']) . '&';
	  }
	  
	  $typefilter_parameters .=
		$get_terms->fields['get_term_name'] . '=' . $_GET[$get_terms->fields['get_term_name']];
	  
      $breadcrumb->add($get_term_breadcrumb->fields[$get_terms->fields['get_term_name_field']],
		zen_href_link(FILENAME_DEFAULT, $typefilter_parameters));
	  /*
// END CEON URI MAPPING 1 of 2
      $breadcrumb->add($get_term_breadcrumb->fields[$get_terms->fields['get_term_name_field']], zen_href_link(FILENAME_DEFAULT, $get_terms->fields['get_term_name'] . "=" . $_GET[$get_terms->fields['get_term_name']]));
// BEGIN CEON URI MAPPING 2 of 2
	  */
// END CEON URI MAPPING 2 of 2
    }
	}
	$get_terms->movenext();
}
/**
 * add the products model to the breadcrumb trail
 */
if (isset($_GET['products_id'])) {
  $productname_query = "select products_name
                   from " . TABLE_PRODUCTS_DESCRIPTION . "
                   where products_id = '" . (int)$_GET['products_id'] . "'
             and language_id = '" . $_SESSION['languages_id'] . "'";

  $productname = $db->Execute($productname_query);

  if ($productname->RecordCount() > 0) {
    $breadcrumb->add($productname->fields['products_name'], zen_href_link(zen_get_info_page($_GET['products_id']), 'cPath=' . $cPath . '&products_id=' . $_GET['products_id']));
  }
}

