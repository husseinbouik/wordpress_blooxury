<?php
$price_id       = $item->price_id;
$download_files = edd_get_download_files( $item->product_id, $item->price_id );
if ( $block_attributes['hide_empty'] && empty( $download_files ) ) {
	return;
}
$order   = edd_get_order( $item->order_id );
$classes = array(
	'edd-blocks__row',
	'edd-order-item__product',
);
if ( $block_attributes['search'] && edd_is_pro() ) {
	$classes[] = 'edd-pro-search__product';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<div class="edd-blocks__row-label"><?php echo esc_html( $name ); ?></div>

	<?php if ( ! edd_no_redownload() ) : ?>
		<div class="edd-order-item__files">
			<?php
			if ( $download_files ) :
				foreach ( $download_files as $filekey => $file ) :
					$download_url = edd_get_download_file_url( $order, $order->email, $filekey, $item->product_id, $price_id );
					?>
					<div class="edd-order-item__file">
						<a href="<?php echo esc_url( $download_url ); ?>" class="edd-order-item__file-link">
							<?php echo esc_html( edd_get_file_name( $file ) ); ?>
						</a>
					</div>
					<?php
				endforeach;
			else :
				echo esc_html( $block_attributes['nofiles'] );
			endif; // End $download_files
			?>
		</div>
	<?php endif; ?>
</div>
