<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<span class="hp-vendor__online-badge 
<?php
if ( $online_status ) :
	?>
	hp-vendor__online-badge--active<?php endif; ?>" title="<?php echo esc_attr( $online_label ); ?>"></span>
