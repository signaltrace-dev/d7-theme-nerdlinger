<?php
// Load all theme includes
$theme_path = drupal_get_path('theme', 'nerdlinger');
$files = file_scan_directory($theme_path . '/preprocess', '/.php/');
foreach ($files as $filepath => $file) {
  require_once($filepath);
}

/**
 * Implements template_preprocess_html().
 */
function nerdlinger_preprocess_html(&$variables) {
}

/**
 * Implements template_preprocess_page.
 */
function nerdlinger_preprocess_page(&$variables) {
  // Add variable for user menu, to be used in topbar
  $user_menu = menu_tree_all_data('menu-researchpipe-user-dropdown');
  $links = _nerdlinger_links(menu_tree_output($user_menu));
  $variables['top_bar_user_menu'] = '<ul class="user-nav right">' . $links . '</ul>';
}

/**
 * Implements template_preprocess_node.
 */
function nerdlinger_preprocess_node(&$variables) {
}

function nerdlinger_css_alter(&$css) {
  /* Remove some default Drupal css */
  $exclude = array(
    'modules/aggregator/aggregator.css' => FALSE,
    'modules/block/block.css' => FALSE,
    'modules/book/book.css' => FALSE,
    'modules/comment/comment.css' => FALSE,
    'modules/dblog/dblog.css' => FALSE,
    'modules/field/theme/field.css' => FALSE,
    'modules/file/file.css' => FALSE,
    'modules/filter/filter.css' => FALSE,
    'modules/forum/forum.css' => FALSE,
    'modules/help/help.css' => FALSE,
    'modules/menu/menu.css' => FALSE,
    'modules/node/node.css' => FALSE,
    'modules/openid/openid.css' => FALSE,
    'modules/poll/poll.css' => FALSE,
    'modules/profile/profile.css' => FALSE,
    'modules/search/search.css' => FALSE,
    'modules/statistics/statistics.css' => FALSE,
    'modules/syslog/syslog.css' => FALSE,
    'modules/system/admin.css' => FALSE,
    'modules/system/maintenance.css' => FALSE,
    'modules/system/system.css' => FALSE,
    'modules/system/system.admin.css' => FALSE,
    'modules/system/system.maintenance.css' => FALSE,
    'modules/system/system.messages.css' => FALSE,
    'modules/system/system.theme.css' => FALSE,
    'modules/system/system.menus.css' => FALSE,
    'modules/taxonomy/taxonomy.css' => FALSE,
    'modules/tracker/tracker.css' => FALSE,
    'modules/update/update.css' => FALSE,
    'modules/user/user.css' => FALSE,
    // Flexslider below
    //'sites/all/libraries/flexslider/flexslider.css' => FALSE,
    drupal_get_path('module', 'views') . '/css/views.css' => FALSE,
  );
  $css = array_diff_key($css, $exclude);
  /* Get rid of some default panel css */
  foreach ($css as $path => $meta) {
    if (strpos($path, 'threecol_33_34_33_stacked') !== FALSE || strpos($path, 'threecol_25_50_25_stacked') !== FALSE) {
      unset($css[$path]);
    }
  }
}

/**
 * Implements hook_theme().
 */
function nerdlinger_theme() {
  $return = array();

  $return['nerdlinger_menu_link'] = array(
    'variables' => array('link' => NULL),
    'function' => 'theme_nerdlinger_menu_link',
  );

  $return['nerdlinger_profile_link'] = array(
    'variables' => array('link' => NULL),
    'function' => 'theme_nerdlinger_profile_link',
  );
  return $return;
}
