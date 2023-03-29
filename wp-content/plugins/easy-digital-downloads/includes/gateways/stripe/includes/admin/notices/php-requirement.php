<?php
/**
 * Notice: php-requirement
 *
 * @package EDD_Stripe\Admin\Notices
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$required_version = 5.6;
$current_version  = phpversion();
?>

<p>
	<strong><?php esc_html_e( 'Credit card payments with Stripe are currently disabled.', 'easy-digital-downloads' ); ?></strong>
</p>

<p>
	<?php
	echo wp_kses(
		sprintf(
			/* translators: %1$s Future PHP version requirement. %2$s Current PHP version. %3$s Opening strong tag, do not translate. %4$s Closing strong tag, do not translate. %5$s Opening anchor tag, do not translate. %6$s Closing anchor tag, do not translate. */
			__( 'Easy Digital Downloads Stripe Payment Gateway requires PHP version %1$s or higher. It looks like you\'re using version %2$s, which means you will need to %3$supgrade your version of PHP to allow the plugin to continue to function%4$s. Newer versions of PHP are both faster and more secure. The version you\'re using %5$sno longer receives security updates%6$s, which is another great reason to update.', 'easy-digital-downloads' ),
			'<code>' . $required_version . '</code>',
			'<code>' . $current_version . '</code>',
			'<strong>',
			'</strong>',
			'<a href="http://php.net/eol.php" rel="noopener noreferrer" target="_blank">',
			'</a>'
		),
		array(
			'code'   => true,
			'strong' => true,
			'a'      => array(
				'href'   => true,
				'rel'    => true,
				'target' => true,
			)
		)
	);
	?>
</p>

<p>
	<button id="edds-php-read-more" class="button button-secondary button-small"><?php esc_html_e( 'Read More', 'easy-digital-downloads' ); ?></button>

	<script>
	document.getElementById( 'edds-php-read-more' ).addEventListener( 'click', function( e ) {
		e.preventDefault();
		var wrapperEl = e.target.parentNode.nextElementSibling;
		wrapperEl.style.display = 'block' === wrapperEl.style.display ? 'none' : 'block';
	} );
	</script>
</p>

<div style="display: none;">

	<p>
		<strong><?php esc_html_e( 'Which version should I upgrade to?', 'easy-digital-downloads' ); ?></strong>
	</p>

	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Future PHP version requirement. */
				__( 'In order to be compatible with future versions of the Stripe payment gateway, you should update your PHP version to at least %1$s; however we recommend using version <code>7.4</code> if possible to receive the full speed and security benefits provided to more modern and fully supported versions of PHP. However, some plugins may not be fully compatible with PHP <code>7.4</code>, so more testing may be required.', 'easy-digital-downloads' ),
				'<code>' . $required_version . '</code>'
			),
			array(
				'code' => true,
			)
		);
		?>
	</p>

	<p>
		<strong><?php esc_html_e( 'Need help upgrading? Ask your web host!', 'easy-digital-downloads' ); ?></strong>
	</p>

	<p>
	<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Many web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. %1$sRead more about updating PHP%2$s.', 'easy-digital-downloads' ),
				'<a href="https://wordpress.org/support/update-php/" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			array(
				'a'    => array(
					'href'   => true,
					'rel'    => true,
					'target' => true,
				)
			)
		);
	?>
	</p>

</div>
