<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
foreach( $scriptFileList as $handle => $data ) {
	enqueue_script( $data );
}
?>
</body>
</html>
