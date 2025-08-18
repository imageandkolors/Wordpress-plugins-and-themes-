<?php
/**
 * API Settings
 *
 * @package AliDrop
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
?>
<form method="post" action="options.php">
    <?php
    settings_fields( 'alidrop_api_settings' );
    do_settings_sections( 'alidrop_api_settings' );
    submit_button();
    ?>
</form>
