<?php
/**
 * Alert Banner Component
 * Renders notification banners from cached ACF data (see get_active_alerts() in functions.php).
 */

$active_alerts = get_active_alerts();
if ( empty($active_alerts) ) return;
?>

<aside id="custom-notification-tray" class="notification-banner" aria-label="Site Notifications" aria-hidden="true"><?php
	foreach ( $active_alerts as $alert ) : ?>
	<div class="site-banner js-banner-<?php echo esc_attr( $alert['display_frequency'] ); ?>" data-alert-id="alert-<?php echo esc_attr( $alert['alert_id'] ); ?>" <?php echo $alert['aria']; ?> style="background-color:<?php echo esc_attr( $alert['severity_color'] ); ?>;">
		<div class="wrapper wrapper--xl">
			<div class="banner-content">
				<div><?php echo $alert['alert_content']; ?></div><?php
				if ( $alert['alert_button'] ) { ?>
				<a class="button" href="<?php echo esc_url( $alert['alert_button']['url'] ); ?>" target="<?php echo esc_attr( $alert['alert_button']['target'] ); ?>"><?php echo esc_html( $alert['alert_button']['title'] ); ?></a><?php
				} ?>
			</div>
			<button class="alert-close" aria-label="Dismiss alert">
				<span aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M11 9.39628L1.60372 0L0 1.60372L9.39628 11L0 20.3963L1.60372 22L11 12.6037L20.3963 22L22 20.3963L12.6037 11L22 1.60372L20.3963 0L11 9.39628Z" fill="black"/>
					</svg>
				</span>
			</button>
		</div>
	</div><?php
	endforeach; ?>
</aside>
