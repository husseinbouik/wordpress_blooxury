<footer class="edd-blocks__download-footer">
	<?php
	$args = array(
		'download_id' => get_the_ID(),
		'align'       => $block_attributes['purchase_link_align'],
		'show_price'  => (bool) $block_attributes['show_price'],
	);
	echo EDD\Blocks\Downloads\buy_button( $args );
	?>
</footer>
