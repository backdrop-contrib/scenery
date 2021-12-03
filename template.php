<?php
/**
 * Custom php code for the Scenery theme.
 */

/**
 * Implements template_preprocess_page().
 */
function scenery_preprocess_page(&$variables) {
  // Add Open Sans font.
  backdrop_add_library('system', 'opensans', TRUE);

  // Admin bar with position fixed conflicts with our sticky menu.
  $admin_bar_sticky = config_get('admin_bar.settings', 'position_fixed');
  if ($admin_bar_sticky) {
    $variables['html_attributes']['class'][] = 'admin-bar-sticky';
  }
  // Gets removed via js.
  $variables['html_attributes']['class'][] = 'no-jscript';

  global $theme;
  $settings = config_get($theme . '.settings');

  // The config file does not exist initially.
  if (empty($settings)) {
    $settings = array(
      'scenery' => '1',
      'customize' => FALSE,
    );
  }
  // Add selected scenery.
  $scenery_file = backdrop_get_path('theme', $theme) . '/css/scenery-' . $settings['scenery'] . '.css';
  backdrop_add_css($scenery_file, array(
    'every_page' => TRUE,
    'group' => CSS_THEME,
  ));

  // Add our custom CSS file, if any.
  if ($settings['customize'] == TRUE) {
    $filepath = "public://$theme-custom.css";
    if (file_exists($filepath)) {
      backdrop_add_css($filepath, array(
        'every_page' => TRUE,
        'group' => CSS_THEME,
      ));
    }
  }
}

/**
 * Implements hook_form_BASE_FORM_ID_alter().
 */
function scenery_form_node_form_alter(&$form, &$form_state, $form_id) {
  if (!empty($form['title']['#default_value'])) {
    unset($form['title']['#attributes']['autofocus']);
  }
}

/**
 * Implements hook_css_alter().
 */
function scenery_css_alter(&$css) {
  unset($css['core/modules/system/css/system.theme.css']);
  unset($css['core/modules/node/css/node.preview.css']);
}

/**
 * Implements hook_ckeditor_settings_alter().
 *
 * Dynamically add css based on theme settings, mainly because of text colors.
 */
function scenery_ckeditor_settings_alter(&$settings, $format) {
  global $base_url, $base_path, $theme;
  $path = backdrop_get_path('theme', $theme);

  $scenery = theme_get_setting('scenery', $theme);
  if ($scenery) {
    $stylesheet = $path . '/css/scenery-' . $scenery . '.css';
    if (file_exists($stylesheet)) {
      $settings['contentsCss'][] = $base_path . $stylesheet;
    }
  }
  else {
    $default = $path . '/css/scenery-1.css';
    if (file_exists($default)) {
      $settings['contentsCss'][] = $base_path . $default;
    }
  }
}

/**
 * Implements theme_menu_local_task().
 */
function scenery_menu_local_task($variables) {
  $link = $variables['element']['#link'];
  $link_text = $link['title'];

  if (!empty($variables['element']['#active'])) {
    // Add text to indicate active tab for non-visual users.
    $active = '<span class="element-invisible">' . t('(active tab)') . '</span>';

    // If the link does not contain HTML already, check_plain() it now.
    // After we set 'html'=TRUE the link will not be sanitized by l().
    if (empty($link['localized_options']['html'])) {
      $link['title'] = check_plain($link['title']);
    }
    $link['localized_options']['html'] = TRUE;
    $link_text = t('!local-task-title!active', array('!local-task-title' => $link['title'], '!active' => $active));
  }
  $action = t($link['title'], array(), array('langcode' => 'en'));
  $path = explode('/', $link['path']);
  $options = $link['localized_options'];
  $options['attributes'] = array(
    'class' => array(end($path)),
  );
  $html_link = l($link_text, $link['href'], $options);

  return '<li' . (!empty($variables['element']['#active']) ? ' class="active"' : '') . '>' . $html_link . "</li>\n";
}

/**
 * Implements hook_comment_view_alter().
 */
function scenery_comment_view_alter(&$build) {
  // Save some space by using dropbuttons.
  $build['dropbutton'] = array();
  if ($build['#view_mode'] == 'full' && isset($build['links'])) {
    $build['dropbutton'] = array(
      '#type' => 'dropbutton',
      '#links' => array_reverse($build['links']['comment']['#links']),
      '#prefix' => '<div class="comment-linkwrapper">',
      '#suffix' => '</div>',
    );
    unset($build['links']['comment']['#links']);
  }
}

/**
 * Implements template_preprocess_node().
 */
function scenery_preprocess_node(&$variables) {
  if ($variables['status'] == NODE_NOT_PUBLISHED) {
    $name = node_type_get_name($variables['type']);
    $variables['title_suffix']['unpublished_indicator'] = array(
      '#type' => 'markup',
      '#markup' => '<div class="unpublished-indicator">' . t('This @type is unpublished.', array('@type' => $name)) . '</div>',
    );
  }
}

/**
 * Implements theme_comment_post_forbidden().
 *
 * Returns HTML for a "you can't post comments" notice, the most useless info
 * ever and it's so verbose that it usually breaks layouts.
 */
function scenery_comment_post_forbidden($variables) {
  return '<small>' . t("You can't post comments") . '</small>';
}
