<?php
/**
 * Admin Settings Page for Toggle Admin Toolbar Plugin
 * Value set is bpm_tat_options a serialized array
 */

// Add a menu for our option page
function bpm_tat_add_page() {
	add_options_page(
		__( 'Toggle Admin Toolbar', 'toggle-admin-toolbar' ),
		__( 'Toggle Admin Toolbar', 'toggle-admin-toolbar' ),
		'manage_options',
		'bpm_tat',
		'bpm_tat_option_page'
	);
}
add_action('admin_menu', 'bpm_tat_add_page');

// Draw the option page
function bpm_tat_option_page() {
  ?>
    <div class="wrap">
        <h2><?php _e('Toggle Admin Toolbar', 'toggle-admin-toolbar'); ?></h2>
        <form action="options.php" method="post">
          <?php
          settings_fields('bpm_tat_options');
          do_settings_sections('bpm_tat');
          submit_button();
          ?>
        </form>
    </div>
  <?php
}

// Register and define the settings
function bpm_tat_admin_init(){
	register_setting(
		'bpm_tat_options',
		'bpm_tat_options',
		'bpm_tat_validate_options'
	);
	add_settings_section(
		'bpm_tat_main',
		__( 'Heading!?', 'toggle-admin-toolbar' ),
		'bpm_tat_section_text',
		'bpm_tat'
	);
	add_settings_field(
		'toggleable',
		__( 'Option Prompt', 'toggle-admin-toolbar' ),
		'bpm_tat_setting_radio_btn',
		'bpm_tat',
		'bpm_tat_main'
	);
}
add_action('admin_init', 'bpm_tat_admin_init');

// Draw the section header
function bpm_tat_section_text() {
	echo '<p>';
	_e( 'Desc.', 'toggle-admin-toolbar');
	echo '</p>';
}

/**
 * Display and fill the form field
 *
 * TODO: Add settings for icon color and other UX improvements that might be requisite for usability.
 * TODO: Shouldn't we use output buffering here?
 *
 */
function bpm_tat_setting_radio_btn() {
 
	$options = get_option( 'bpm_tat_options' );
	if( isset( $options['toggleable'] ) ) {
		$toggleable = ( $options['toggleable'] ? 1 : 0 );
	} else {
		$toggleable = 0;
	}
 
	$html = '';
	$html .= '<fieldset>';
	$html .= '<p>';
	$html .= '<label for="bpm_tat_toggleable_false">';
	$html .=  '<input type="radio" id="bpm_tat_toggleable_false" name="bpm_tat_options[toggleable]" value="0"' . checked( 0, $toggleable, false ) . '/>';
	$html .= __( 'Remove completely', 'toggle-admin-toolbar' );
	$html .= '</label>';
	$html .= '</p>';
	$html .= '<p>';
	$html .= '<label for="bpm_tat_toggleable_true">';
	$html .= '<input type="radio" id="bpm_tat_toggleable_true" name="bpm_tat_options[toggleable]" value="1"' . checked( 1, $toggleable, false ) . '/>';
	$html .= __( 'Show toggle button', 'toggle-admin-toolbar' );
	$html .= '</label>';
	$html .= '</p>';
	$html .= '</fieldset>';
	echo $html;
}

// Validate user input (we want text only)
function bpm_tat_validate_options( $input ) {
	$output = [];
    foreach( $input as $key => $val ):
		if( isset ( $input[$key] ) ):
			$output[$key] = ( $input[$key] ? 1 : 0 );
		endif;
	endforeach;
	return $output;
}