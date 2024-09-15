<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://4q-u4.github.io/
 * @since             1.0.0
 * @package           Tonbola
 *
 * @wordpress-plugin
 * Plugin Name:       Tonbola
 * Plugin URI:        https://4q-u4.github.io/
 * Description:       Tonbola Plugin
 * Version:           1.0.0
 * Author:            Daniel
 * Author URI:        https://4q-u4.github.io//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tonbola
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('TONBOLA_VERSION', '1.0.0');

function activate_tonbola() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-tonbola-activator.php';
    Tonbola_Activator::activate();
}

function deactivate_tonbola() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-tonbola-deactivator.php';
    Tonbola_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_tonbola');
register_deactivation_hook(__FILE__, 'deactivate_tonbola');

require plugin_dir_path(__FILE__) . 'includes/class-tonbola.php';

function run_tonbola() {
    $plugin = new Tonbola();
    $plugin->run();
}
run_tonbola();

// Enqueue admin styles and scripts
function tonbola_enqueue_admin_assets($hook) {
    if ($hook != 'toplevel_page_tonbola') {
        return;
    }
    wp_enqueue_style('tonbola-admin-styles', plugins_url('admin/css/tonbola-admin.css', __FILE__));
    wp_enqueue_script('tonbola-admin-script', plugins_url('admin/js/tonbola-admin.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('tonbola-admin-script', 'tonbola_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tonbola_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'tonbola_enqueue_admin_assets');

// Add Tonbola menu to WordPress admin
function tonbola_menu() {
    add_menu_page(
        'Tonbola',
        'Tonbola',
        'manage_options',
        'tonbola',
        'tonbola_page',
        'dashicons-admin-generic',
        6
    );
}
add_action('admin_menu', 'tonbola_menu');

// Render Tonbola admin page
function tonbola_page() {
    ?>
    <div class="wrap tonbola-admin-page">
        <div class="tonbola-container">
            <h1><b>Tonbola</b></h1>
            <form id="tonbola-form" method="post">
                <label for="cell_number"><?php _e('Cell Number:', 'tonbola'); ?></label>
                <input type="number" id="cell_number" name="cell_number" min="1" max="100" required>
                
                <label for="person_name"><?php _e('Person Name:', 'tonbola'); ?></label>
                <input type="text" id="person_name" name="person_name" required>
                
                <!-- <label for="dropdown"><?php _e('Dropdown:', 'tonbola'); ?></label>
                <select id="dropdown" name="dropdown">
                    <option value="option1"><?php _e('Option 1', 'tonbola'); ?></option>
                    <option value="option2"><?php _e('Option 2', 'tonbola'); ?></option>
                    <option value="option3"><?php _e('Option 3', 'tonbola'); ?></option>
                </select>
                 -->
                <input type="submit" name="submit" value="<?php _e('Submit', 'tonbola'); ?>">
            </form>
            <div class="button-container">
                <!-- <button id="new-button"><?php _e('New', 'tonbola'); ?></button> -->
                <button id="clear-button"><?php _e('Clear Table data', 'tonbola'); ?></button>
            </div>
            <div id="message" class="hidden"></div>
        </div>
    </div>
    <?php
}

// AJAX handler for form submission
function tonbola_submit_form() {
    check_ajax_referer('tonbola_nonce', 'nonce');

    $cell_number = isset($_POST['cell_number']) ? intval($_POST['cell_number']) : 0;
    $person_name = isset($_POST['person_name']) ? sanitize_text_field($_POST['person_name']) : '';

    error_log('Received cell_number: ' . $cell_number);
    error_log('Received person_name: ' . $person_name);

    if ($cell_number >= 1 && $cell_number <= 100) {
        update_option("tonbola_cell_{$cell_number}", $person_name);
        wp_send_json_success(array(
            'message' => __('Data saved successfully!', 'tonbola'),
            'cell_number' => $cell_number,
            'person_name' => $person_name
        ));
    } else {
        wp_send_json_error(array('message' => __('Invalid cell number!', 'tonbola')));
    }
}
add_action('wp_ajax_tonbola_submit_form', 'tonbola_submit_form');
add_action('wp_ajax_nopriv_tonbola_submit_form', 'tonbola_submit_form');

// AJAX handler for clearing data
function tonbola_clear_data() {
    check_ajax_referer('tonbola_nonce', 'nonce');

    for ($i = 1; $i <= 100; $i++) {
        delete_option("tonbola_cell_{$i}");
    }

    wp_send_json_success(array(
        'message' => __('Data cleared successfully', 'tonbola')
    ));
}
add_action('wp_ajax_tonbola_clear_data', 'tonbola_clear_data');

// Convert Western Arabic numerals to Eastern Arabic numerals
function western_to_eastern_arabic($str) {
    $western = array('0','1','2','3','4','5','6','7','8','9');
    $eastern = array('٠','١','٢','٣','٤','٥','٦','٧','٨','٩');
    return str_replace($western, $eastern, $str);
}

// Shortcode function for displaying Tonbola table
function tonbola_shortcode() {
    wp_enqueue_style('tonbola-frontend-styles', plugins_url('public/css/tonbola-public.css', __FILE__));
    wp_enqueue_script('tonbola-frontend-script', plugins_url('public/js/tonbola-public.js', __FILE__), array('jquery'), time(), true);
    wp_localize_script('tonbola-frontend-script', 'tonbola_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tonbola_frontend_nonce')
    ));

    ob_start();
    ?>
    <div class="tonbola-wrapper">
        <div id="tonbola-table-container" style="direction: rtl;">
            <?php echo tonbola_generate_table_html(); ?>
        </div>
        <button id="tonbola-refresh-button"><?php _e('Refresh Table', 'tonbola'); ?></button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('tonbola_table', 'tonbola_shortcode');
function tonbola_generate_table_html() {
    $table_html = '<table>';
    $cell_count = 1;
    for ($i = 0; $i < 7; $i++) {
        $table_html .= '<tr>';
        for ($j = 0; $j < 15; $j++) {
            if ($i == 3 && $j == 5) {
                $table_html .= '<td colspan="5" class="tonbola-cell open-here"><span class="tonbola-number">افتح هنا</span></td>';
                $j += 4;
            } else {
                $person_name = get_option("tonbola_cell_{$cell_count}", '');
                $cell_class = $person_name ? 'tonbola-cell filled' : 'tonbola-cell';
                
                $table_html .= '<td class="' . $cell_class . '">';
                if ($cell_count <= 100) {
                    $table_html .= '<span class="tonbola-number">' . western_to_eastern_arabic($cell_count) . '</span>';
                    if ($person_name) {
                        $table_html .= '<span class="tonbola-name">' . esc_html($person_name) . '</span>';
                    }
                    $cell_count++;
                }
                $table_html .= '</td>';
            }
        }
        $table_html .= '</tr>';
    }
    $table_html .= '</table>';
    return $table_html;
}
// AJAX handler for refreshing the table
function tonbola_refresh_table() {
    check_ajax_referer('tonbola_frontend_nonce', 'nonce');

    $table_html = '<table>';
    $cell_count = 1;
    for ($i = 0; $i < 7; $i++) {
        $table_html .= '<tr>';
        for ($j = 14; $j >= 0; $j--) {
            if ($i == 3 && $j == 9) {
                $table_html .= '<td colspan="5" class="tonbola-cell open-here"><span class="tonbola-number">افتح هنا</span></td>';
                $j -= 4;
            } else {
                $person_name = get_option("tonbola_cell_{$cell_count}", '');
                $cell_class = $person_name ? 'tonbola-cell filled' : 'tonbola-cell';
                
                $table_html .= '<td class="' . $cell_class . '">';
                $table_html .= '<span class="tonbola-number">' . western_to_eastern_arabic($cell_count) . '</span>';
                if ($person_name) {
                    $table_html .= '<span class="tonbola-name">' . esc_html($person_name) . '</span>';
                }
                $table_html .= '</td>';
                $cell_count++;
            }
        }
        $table_html .= '</tr>';
    }
    $table_html .= '</table>';

    wp_send_json_success(array('table_html' => $table_html));
}
add_action('wp_ajax_tonbola_refresh_table', 'tonbola_refresh_table');
add_action('wp_ajax_nopriv_tonbola_refresh_table', 'tonbola_refresh_table');


function tonbola_remove_admin_notices() {
    if (isset($_GET['page']) && $_GET['page'] === 'tonbola') {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}
add_action('admin_head', 'tonbola_remove_admin_notices');