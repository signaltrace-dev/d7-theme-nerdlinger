<?php
/**
 * @file
 * menu-link.func.php
 */

 /**
  * Implements theme_links() targeting the main menu topbar.
  */
 function nerdlinger_links__topbar_main_menu($variables) {
   $links = menu_tree_output(menu_tree_all_data(variable_get('menu_main_links_source', 'main-menu')));
   $dashboard_link = '';
   foreach($links as $key => $link){
     $href = !empty($link['#href']) ? $link['#href'] : '';

     // Use FontAwesome icon for dashboard link
     if($href == 'dashboard'){
       unset($links[$key]);
       $dashboard_link = l('<i class="fa fa-home"></i>', 'dashboard', array(
         'html' => TRUE,
       ));
       $dashboard_output = array(
         '#prefix' => '<li>',
         '#markup' => $dashboard_link,
         '#suffix' => '</li>',
       );
     }

     // Unset nested links since we don't want dropdowns on the main topbar nav
     if(!empty($links[$key]['#below'])){
       //$links[$key]['#below'] = array();
     }
   }


   $dashboard_output = !empty($dashboard_output) ? drupal_render($dashboard_output) : '';
   $output = $dashboard_output . _nerdlinger_links($links);
   $variables['attributes']['class'][] = 'left';
   return '<ul' . drupal_attributes($variables['attributes']) . '>' . $output . '</ul>';
 }

 function nerdlinger_links__system_secondary_menu($variables) {
   $path = menu_get_active_trail();
   $menu_item = end($path);

   // Use the parent ID (or the link ID) of the current item to get child items
   $parent = !empty($menu_item['plid']) ? $menu_item['plid'] : $menu_item['mlid'];

   // We need to fetch the links ourselves because we need the entire tree.
   $links = menu_tree_output(menu_tree_all_data(variable_get('menu_secondary_links_source', 'user-menu')));
   $secondary_links = array();
   foreach($links as $mlid => $link){
     if($mlid == $parent){
       $secondary_links = $link['#below'];
     }
   }

   $output = _nerdlinger_links($secondary_links);

   return '<ul' . drupal_attributes($variables['attributes']) . '>' . $output . '</ul>';
 }


function nerdlinger_preprocess_menu_tree(&$vars){
}

function _nerdlinger_links($links, $prepend = NULL) {
  $output = '';

  foreach (element_children($links) as $key) {
    $output .= _nerdlinger_render_link($links[$key], $prepend);
  }

  return $output;
}

/**
 * Helper function to recursively render sub-menus.
 *
 * @link array
 *   An array of menu links.
 *
 * @return string
 *   A rendered list of links, with no <ul> or <ol> wrapper.
 *
 * @see _nerdlinger_links()
 */
function _nerdlinger_render_link($link, $prepend = NULL) {
  $path = current_path();

  // We only want to enable dropdowns for specific menus
  $dropdown_enabled = array(
    'menu-researchpipe-user-dropdown',
  );
  $link_menu = !empty($link['#original_link']['menu_name']) ? $link['#original_link']['menu_name'] : NULL;

  // Optional FontAwesome icons to be appended to various links for specific menus
  $icon_mappings = array(
    'menu-researchpipe-user-dropdown' => array(
      'config/site-settings' => 'fa fa-gear',
      'support/report-bug' => 'fa fa-bug',
      'support/suggest-feature' => 'fa fa-comment-o',
      'user/logout' => 'fa fa-sign-out',
    ),
  );

  $output = '';

  $href = !empty($link['#href']) ? $link['#href'] : '';

  // Render top level and make sure we have an actual link.
  if (!empty($href)) {
    // Initially set if our current path is the current top level link
    $is_active = $path == $href;

    $rendered_link = NULL;

    if (!isset($rendered_link)) {
      $icon = !empty($icon_mappings[$link_menu][$href]) ? '<i class="'. $icon_mappings[$link_menu][$href] .'">&nbsp;</i>' : '';

      if($href == 'user'){
        $rendered_link = theme('nerdlinger_profile_link', array('link' => $link));
      }
      else{
        $rendered_link = theme('nerdlinger_menu_link', array('link' => $link, 'icon' => $icon));
      }
    }

    // Test for localization options and apply them if they exist.
    if (isset($link['#localized_options']['attributes']) && is_array($link['#localized_options']['attributes'])) {
      $link['#attributes'] = array_merge_recursive($link['#attributes'], $link['#localized_options']['attributes']);
    }

    // Add class to Node Add links
    if(strpos($href, '/add') !== FALSE){
      $link['#attributes']['class'][] = 'node-add';
    }

    $sub_links = '';
    if (!empty($link['#below'])) {
      // Add dropdown class to parent link if applicable
      if(in_array($link_menu, $dropdown_enabled)){
        $link['#attributes']['class'][] = 'has-dropdown';
      }

      $sub_menu = '';
      // Build sub nav recursively.
      foreach ($link['#below'] as $sub_link) {
        $sub_href = !empty($sub_link['#href']) ? $sub_link['#href'] : NULL;

        // Check to see if the currently active page is in the current menu item's children
        if($sub_href == $path){
          $is_active = TRUE;
        }

        // Only add links as dropdowns if they're specified in dropdown_enabled.
        if (!empty($sub_href) && in_array($link_menu, $dropdown_enabled)) {
          $rendered_sub_link = _nerdlinger_render_link($sub_link, $icon);
          $sub_menu .= $rendered_sub_link;
        }
      }

      // Add dropdown markup to parent link if we want it
      if (!empty($sub_menu) && in_array($link_menu, $dropdown_enabled)) {
        $sub_links = '<ul class="dropdown">' . $sub_menu . '</ul>';
      }
    }

    // Add active class to the li element if the current page is this item or any of its children
    if($is_active){
      $link['#attributes']['class'][] = 'active';
    }

    $output .= '<li' . drupal_attributes($link['#attributes']) . '>' . $rendered_link . $sub_links . '</li>';
  }

  return $output;
}

/**
 * Theme function to render a single top bar menu link.
 */
function theme_nerdlinger_menu_link($variables) {
  $link = $variables['link'];
  $icon = !empty($variables['icon']) ? $variables['icon'] : '';

  $options = array(
    'html' => TRUE,
  );

  $options = array_merge_recursive($options, $link['#localized_options']);

  return l($icon . $link['#title'], $link['#href'], $options);
}

/**
 * Theme function to render a profile link with user picture for the current user.
 */
function theme_nerdlinger_profile_link($variables) {
  $link = !empty($variables['link']) ? $variables['link'] : NULL;
  $href = !empty($link['#href']) ? $link['#href'] : 'user';
  $title = !empty($link['title']) ? $link['title'] : NULL;
  $profile_id = !empty($link['profile_id']) ? $link['profile_id'] : NULL;

  if(empty($profile_id)){
    global $user;
    $title = $user->name;
    $wrapper = entity_metadata_wrapper('user', $user);
    $profile = isset($wrapper->field_my_author_profile) ? $wrapper->field_my_author_profile->value() : NULL;
  }
  else{
    $profile = node_load($profile_id);
  }

  $pwrapper = !empty($profile) ? entity_metadata_wrapper('node', $profile) : NULL;
  $profile_img = '';
  if(!empty($pwrapper)){
    // Replace title with last name and first name
    $fname = $pwrapper->field_first_name->value();
    $lname = $pwrapper->field_last_name->value();
    $title = !empty($fname) && !empty($lname) ? $fname . ' ' . $lname : $title;

    // Get profile image
    $photo = $pwrapper->field_photo->value();
    $profile_img_url = nerdlinger_get_profile_image($photo);
    if(!empty($profile_img_url)){
      $profile_img = '<img class="img-profile" src="' . $profile_img_url . '"/>';
    }

    // Set link to profile page
    $href = '/node/' . $pwrapper->getIdentifier();
  }
  $options = array(
    'html' => TRUE,
  );

  $options = array_merge_recursive($options, $link['#localized_options']);
  return l($profile_img . $title, $href, $options);
}


function nerdlinger_get_profile_image($field_photo){
  $image_file = !empty($field_photo['filename']) ? $field_photo['filename'] : NULL;
  if(empty($image_file)){
    $default_file = research_pipe_get_file_by_name('icon-profile.png');
    if(empty($default_file)){
      // Create default profile photo from module-provided image if not already present
      $default_profile_photo = drupal_get_path('module', 'research_pipe') . '/images/profile/icon-profile.png';
      $file = file_save_data(file_get_contents($default_profile_photo), file_default_scheme().'://'.basename($default_profile_photo));
      $file->uid = 1;
      $default_file = $file;
    }
    if(!empty($default_file)){
      $style_def = image_style_load('profile_photo');
      if(!empty($style_def)){
        // Create default profile photo styled derivative if it does not exist
        $derivative_uri = image_style_path('profile_photo', $default_file->uri);
        image_style_create_derivative($style_def, $default_file->uri, $derivative_uri);
      }
    }

    $image_file = !empty($default_file->uri) ? $default_file->uri : NULL;
  }
  $image_url = image_style_url('profile_photo', $image_file);
  return $image_url;
}
