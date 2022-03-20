<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo determine_locale(); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title># TODO: set title</title>
	<script>const ajaxurl = '<?php echo plugin_dir_url( __FILE__ ) . '../ajax.php'; ?>'</script>
	<?php
	foreach( $styleFileList as $handle => $data ) {
		enqueue_style( $data );
	}
	?>
</head>
<body>
<div id="wpbody-content"></div>
