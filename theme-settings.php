<?php
/**
 * @file
 * Theme settings.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function scenery_form_system_theme_settings_alter(&$form, &$form_state) {
  $theme_name = $form['theme']['#value'];
  $path = backdrop_get_path('theme', 'scenery');
  $form['#attached']['css'] = array($path . '/css/scenery-admin.css');

  $form['settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Settings'),
    '#collapsible' => FALSE,
  );
  $form['settings']['scenery'] = array(
    '#type' => 'radios',
    '#title' => t('Select scenery'),
    '#options' => array(
      '1' => 'Dawn at the river',
      '2' => 'Stream contemplation',
      '3' => 'Blue glass stack',
      '4' => 'Understatement',
    ),
    '#default_value' => theme_get_setting('scenery', $theme_name),
    '#description' => t('Additionally to the header image, this also changes some text and background colors.'),
  );
  $form['settings']['max_row_width'] = array(
    '#type' => 'number',
    '#title' => t('Max row content width'),
    '#min' => 980,
    '#max' => 2000,
    '#size' => 5,
    '#field_suffix' => 'px',
    '#default_value' => theme_get_setting('max_row_width', $theme_name),
    '#description' => t('How wide layout container (row) content can get. Note: only the content, the header and footer backgrounds are not affected.'),
  );
  $form['settings']['max_article_width'] = array(
    '#type' => 'number',
    '#title' => t('Max article width'),
    '#min' => 800,
    '#max' => 1200,
    '#size' => 5,
    '#field_suffix' => 'px',
    '#default_value' => theme_get_setting('max_article_width', $theme_name),
    '#description' => t('How wide article or comment content can get. This limit improves text readability on single-column layouts.'),
  );

  $form['customize'] = array(
    '#type' => 'checkbox',
    '#title' => t('Customize your scenery'),
    '#default_value' => theme_get_setting('customize', $theme_name),
  );
  $form['custom'] = array(
    '#type' => 'fieldset',
    '#title' => t('Customize'),
    '#collapsible' => FALSE,
    '#states' => array(
      'invisible' => array(
        ':input[name="customize"]' => array('checked' => FALSE),
      ),
    ),
  );
  // Add an image upload.
  $upload_validators = array(
    'file_validate_extensions' => array('jpg jpeg png gif'),
    'file_validate_image_resolution' => array('3200x1600', '960x300'),
  );
  if (config_get('system.core', 'image_toolkit') == 'gd' && defined('IMAGETYPE_WEBP')) {
    $gd_info = gd_info();
    if (isset($gd_info['WebP Support']) && $gd_info['WebP Support'] == TRUE) {
      $upload_validators['file_validate_extensions'] = array('jpg jpeg png gif webp');
    }
  }
  $upload_description = theme('file_upload_help', array(
    'upload_validators' => $upload_validators,
  ));
  $form['custom']['image'] = array(
    '#type' => 'managed_file',
    '#title' => t('Header background image'),
    '#description' => $upload_description,
    '#default_value' => theme_get_setting('image', $theme_name),
    '#upload_location' => 'public://scenery/',
    '#upload_validators' => $upload_validators,
  );
  $form['custom']['css'] = array(
    '#type' => 'textarea',
    '#title' => t('Custom CSS rules'),
    '#default_value' => theme_get_setting('css', $theme_name),
    '#rows' => 12,
  );
  $preprocess_css = config_get('system.core', 'preprocess_css');
  if ($preprocess_css) {
    $text = t('Note: Aggregation and compression of CSS files is currently turned on. This makes inspection of existing CSS harder for you.');
    $form['custom']['css']['#description'] = $text;
  }
  // Strange... why do I have to set the system function?
  $form['#submit'] = array('_scenery_css_file', 'system_theme_settings_submit');
}

/**
 * Custom callback to save or delete a css file.
 */
function _scenery_css_file($form, $form_state) {
  $theme_name = $form['theme']['#value'];
  $values = $form_state['values'];
  $destination = 'public://' . $theme_name . '-custom.css';
  if (!empty($values['css']) || !empty($values['image'])) {
    $css = "/**\n * Do not edit this file directly, your changes will be lost!\n */\n";
    // Custom image.
    if (!empty($values['image'])) {
      $image = file_load($values['image']);
      if (is_a($image, 'File')) {
        $url = file_create_url($image->uri);
        $css .= ".l-header {\n  background-image: url($url);\n}";
      }
    }
    // Custom CSS.
    if (!empty($values['css'])) {
      $css .= "\n" . strip_tags($values['css']);
    }
    file_unmanaged_save_data($css, $destination, FILE_EXISTS_REPLACE);
  }
  elseif (file_exists($destination)) {
    file_unmanaged_delete($destination);
  }
}
