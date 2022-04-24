<?php
/**
 * Plugin Name: Toggle Admin Toolbar
 * Plugin URI: http://boilingpotmedia.com
 * Description: Adds options to toggle admin menu visibility.
 * Version: 0.1.0
 * Author: James Valeii
 * Author URI: http://jamesvaleii.com/
 * Text Domain: toggle-admin-toolbar
 *
 * @package toggle-admin-toolbar
 */

if ( ! defined( 'ABSPATH' ) ) exit;

register_deactivation_hook(__FILE__, ['Toggle_Admin_Toolbar', 'bpm_tat_add_deactivation_actions']);

/**
 * Toggle Admin Toolbar Plugin Class.
 *
 * @since 0.1.0
 */
class Toggle_Admin_Toolbar
{
  
  /**
   * Constructor to set up plugin.
   *
   * @since 0.1.0
   */
  public function __construct()
  {
    $this->bpm_tat_add_hooks();
  }
  
  /**
   * Should admin bar be removed from the DOM or hidden temporarily.
   *
   * @since 0.1.0
   *
   * @return bool
   */
  public function bpm_tat_should_admin_bar_toggle(): bool
  {
    // bool false if unset or [ 'toggleable' => int 0 ]
    $tgble = get_option('bpm_tat_options');
    if ( $tgble && isset( $tgble['toggleable'] )):
      // use database value
      $tgble = $tgble['toggleable'];
    endif;
    $tgble = apply_filters( 'bpm_tat_toggleable', $tgble );
    return (bool) $tgble;
}
  
  /**
   * Add hooks.
   *
   * @since 0.1.0
   *
   * @hooked bpm_tat_button_add_to_admin_menu
   * @hooked styles
   * @hooked script
   */
  public function bpm_tat_add_hooks()
  {
    $this->bpm_tat_add_settings_page();
    if( ! is_admin() ):
      add_action('admin_bar_menu', [$this, 'bpm_tat_button_add_to_admin_menu'], 1);
      $this->bpm_tat_add_scripts();
      $this->bpm_tat_add_styles();
    endif;
  }

  /**
   * Add a settings link to "links for this plugin" on the plugin page.
   *
   * Add to the $links array, an element that contains the html markup
   * for the settings page for this link.
   *
   * @since 0.1.0
   *
   * @param array of string $links each of which is the markup for a link.
   * @return array of strings, each of which is the markup for a link with additional link
   */
  public function bpm_tat_add_plugins_page_links($links): array
  {
    $links[] = '<a href="'. admin_url('options-general.php?page=bpm_tat') .'">'. __('Settings') .'</a>';
    return $links;
  }

  /**
   * Add menu item to WordPress' front end admin bar
   *
   * By calling admin_bar_menu we can add menu items to the WordPress Admin bar.
   *
   * @since 0.1.0
   *
   * @param object of the $admin_bar that will be modified
   *
   * @return void
   */
  public function bpm_tat_button_add_to_admin_menu($admin_bar)
  {
    /**
     * Configure new menu item
     *
     * See add_node() wp-includes/class-wp-admin-bar.php
     *
     *  @type string $id     ID of the item.
     *  @type string $title  Title of the node.
     *  @type string $parent Optional. ID of the parent node.
     *  @type string $href   Optional. Link for the item.
     *  @type bool   $group  Optional. Whether the node is a group. Default false.
     *  @type array  $meta   Metadata including the following keys: 'html', 'class', 'rel', 'lang', 'dir', 'onclick', 'target', 'title', 'tabindex'. Default empty.
     *
     */
    $args = [
      'id' => 'tat-button',
      'parent' => 'top-secondary',
      'href' => '#',
      'title' => 'X',
      'meta' => [
        'class' => __('tat-button'),
        'title' => __('Click to remove the admin toolbar. Toolbar will reappear on refresh.'),
        'onclick' => __('bpm_tat_remove();'),
      ],
    ];

    if( $this->bpm_tat_should_admin_bar_toggle() ):
        $args['title'] = '☰';
        $args['meta']['title'] = __('Click to minimize the admin toolbar.');
        $args['meta']['onclick'] = ('bpm_tat_toggle();');
    endif;

    $admin_bar->add_menu( $args );

  }

  /**
   * Create Settings Page.
   *
   * The settings page is created in lib/admin-settings.php.
   * We include a check that this file exists, so we can
   * run this plugin with only this primary file; this
   * allows using this single file as a "mu-plugins" plugin.
   *
   * @since 0.1.0
   */
  public function bpm_tat_add_settings_page()
  {
    $plugin_dir_path = plugin_dir_path(__FILE__);
    $plugin_basename = plugin_basename(__FILE__);

    if ( file_exists( realpath( $plugin_dir_path.'/lib/admin-settings.php' ) ) ) {

      // Create admin settings screen.
      require_once( realpath( $plugin_dir_path.'/lib/admin-settings.php' ) );

      // Add Settings link on Plugin Page.
      add_filter('plugin_action_links_'.$plugin_basename, [$this, 'bpm_tat_add_plugins_page_links']);
    }
  }

  /**
   * Include some Javascript
   *
   * @since 0.1.0
   *
   * @return void
   */
  public function bpm_tat_add_scripts()
  {
    ob_start(); ?>
      function bpm_tat_make_btn() {

        const tatBtn = document.createElement('a');
        tatBtn.style.visibility = 'hidden';
        tatBtn.id = 'restoreAdminToolbar';
        tatBtn.title = 'Maximize admin toolbar';
        tatBtn.href = '#';

        const tatIcon = document.createTextNode('☰');
        tatBtn.appendChild(tatIcon);

        document.body.insertBefore(tatBtn, document.getElementById('wpadminbar'));
      }

      bpm_tat_make_btn();

      function bpm_tat_remove() {
        const wpadbr = document.getElementById('wpadminbar');
        wpadbr.style.display = 'none';
        document.documentElement.style.setProperty('margin-top', '0px', 'important');
      }

      function bpm_tat_toggle() {

        bpm_tat_remove();

        const tatBtn = document.getElementById('restoreAdminToolbar');
        tatBtn.style.visibility = 'visible';

        tatBtn.onclick = function () {

          const wpadbr = document.getElementById('wpadminbar');
          wpadbr.style.display = 'block';
          document.documentElement.style.setProperty('margin-top', '32px', 'important');

          this.style.visibility = 'hidden';

        };
      }
    <?php
    $tat_scripts = ob_get_clean();
    if ( !wp_script_is('tat_scripts') ):
      wp_register_script('tat_scripts', false, [], '0.1.0', 1);
      wp_enqueue_script('tat_scripts');
    endif;
    wp_add_inline_script('tat_scripts', $tat_scripts);
  }
  
  /**
   * Include some CSS
   *
   * @since 0.1.0
   *
   * @return void
   */
  public function bpm_tat_add_styles()
  {
    $color = get_option('bpm_tat_options') ? get_option('bpm_tat_options')['color'] : '#FFFFFF'; // bool false if unset or [ 'color' => string #?????? ]
    ob_start();
    ?>
    #restoreAdminToolbar {
      position: absolute;
      z-index: 9999999;
      color: <?php echo sanitize_hex_color( $color ); ?>;
      text-decoration: none;
      text-align: center;
      right: 0;
      top: 0;
      text-shadow: none;
      text-transform: none;
      letter-spacing: normal;
      font-size: 13px;
      font-weight: 400;
      font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;
      line-height: 2.46153846;
      padding: 0 8px;
    }
    <?php
    $tat_styles = ob_get_clean();
    if ( !wp_style_is('tat_styles') ):
      wp_register_style('tat_styles', FALSE);
      wp_enqueue_style('tat_styles');
    endif;
    wp_add_inline_style('tat_styles', $tat_styles);
  }
  
  /**
   * On plugin deactivation clean up.
   *
   * Remove the plugin option, where settings are stored
   *
   * @since 0.1.0
   */
  public static function bpm_tat_add_deactivation_actions()
  {
    delete_option('bpm_tat_options');
  }
  
}

new Toggle_Admin_Toolbar;