<div class="wrap">
	<form class="ptb-flush-form" action="" method="post">

		<?php settings_errors( 'ptb-troubleshoot' ); ?>

		<div class="card">
			<h3 class="title"><?php _e( 'Flush Permlinks', 'ptb' ); ?></h3>
			<p><?php _e( 'If you are experiencing 404 error after changing the post type slug, click "Flush Permalinks" to refresh the permalinks in WordPress.', 'ptb' ); ?></p>
			<p><a href="<?php echo add_query_arg( [ 'action' => 'ptb-flush', 'nonce' => wp_create_nonce( 'ptb_flush_nonce' ) ] ); ?>" class="button button-primary"><?php _e( 'Flush Permlinks', 'ptb' ); ?></a></p>
		</div>

		<?php do_action( 'ptb_troubleshoot' ); ?>

	</form>
</div>