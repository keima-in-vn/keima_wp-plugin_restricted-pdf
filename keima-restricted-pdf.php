<?php
/**
 * Plugin Name: keima | Restricted PDF
 * Description:  This will add restricted PDF viewer. Just add [keima_pdf_viewer file_path="path/to/pdf"]. The PDF file will be shown by Javascript. The page will not allow Right click, and not allow Print.
 * Version: 1.0.1
 * Plugin URI: 
 * Author: keima.co
 * Author URI: https://www.keima.co/
 * Text Domain: keima-pdf-viewer
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

define( 'KEIMA_RESTRICTED_PDF_FILE', __FILE__ );
define( 'KEIMA_RESTRICTED_PDF_DIR', plugin_dir_path( __FILE__ ) );
define( 'KEIMA_RESTRICTED_PDF_VER', '1.0.0' );

if ( ! class_exists( 'KEIMA_RESTRICTED_PDF' ) ) :

  class KEIMA_RESTRICTED_PDF {

    function __construct() {
      // Do nothing.
    }

    function initialize() {

      add_action( 'plugins_loaded', function () {
        load_plugin_textdomain( 'keima-restricted-pdf', false, 'keima-restricted-pdf/languages/' );
      });

      include_once KEIMA_RESTRICTED_PDF_DIR . 'includes/krp-shortcode.php';
    }
  }

  function keima_restricted_pdf() {
    global $keima_restricted_pdf;

    // Instantiate only once.
    if ( ! isset( $keima_restricted_pdf ) ) {
      $keima_restricted_pdf = new KEIMA_RESTRICTED_PDF();
      $keima_restricted_pdf->initialize();
    }
    return $keima_restricted_pdf;
  }

  // Instantiate.
  keima_restricted_pdf();

endif; // class_exists check
