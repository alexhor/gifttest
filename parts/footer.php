<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- stylesheets and scripts -->
<script>const ajaxurl = '<?php echo plugin_dir_url( __FILE__ ) . '../ajax.php'; ?>'</script>
<?php
do_action('wp_enqueue_scripts');
foreach( $scriptFileList as $handle => $data ) {
	enqueue_script( $data );
}
foreach( $styleFileList as $handle => $data ) {
	enqueue_script( $data );
}
?>
</body>
</html>
