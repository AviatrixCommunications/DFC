<?php
/**
 * Alert Banner Component
 * Renders notification banners from cached ACF data (see get_active_alerts() in functions.php).
 *
 * The tray is rendered OPEN on first paint — no entrance delay or slide-in.
 * The inline script below runs during parse (before first paint) so it can:
 *   1. Remove banners the visitor already dismissed (no flash of a closed alert).
 *   2. Publish the tray height to --dfc-alert-height before the header/content
 *      lay out, so nothing shifts when the page loads.
 * The slide animation now only plays when a visitor dismisses a banner
 * (see js/modules/alert-banner.js).
 */

$active_alerts = dfc_get_active_alerts();
if ( empty($active_alerts) ) return;

$dfc_icon_urgent = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3.5 22 20.5H2L12 3.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 10v4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="17.75" r="1.15" fill="currentColor"/></svg>';
$dfc_icon_info   = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7.75" r="1.15" fill="currentColor"/></svg>';
?>

<aside id="custom-notification-tray" class="notification-banner" aria-label="Site Notifications"><?php
	foreach ( $active_alerts as $alert ) :
		$is_urgent = ( $alert['severity'] ?? '' ) === 'urgent';
		?>
	<div class="site-banner js-banner-<?php echo esc_attr( $alert['display_frequency'] ); ?>" data-alert-id="alert-<?php echo esc_attr( $alert['alert_id'] ); ?>" <?php echo $alert['aria']; ?> style="background-color:<?php echo esc_attr( $alert['severity_color'] ); ?>;">
		<div class="wrapper wrapper--xl">
			<span class="banner-icon" aria-hidden="true"><?php echo $is_urgent ? $dfc_icon_urgent : $dfc_icon_info; ?></span>
			<div class="banner-content">
				<div><?php echo wp_kses_post( $alert['alert_content'] ); ?></div><?php
				if ( $alert['alert_button'] ) {
					$btn_target = $alert['alert_button']['target'] ?? '_self';
					$btn_rel = $btn_target === '_blank' ? ' rel="noopener noreferrer"' : '';
					$btn_sr = $btn_target === '_blank' ? '<span class="u-sr-only"> (opens in new tab)</span>' : '';
					?>
				<a class="button" href="<?php echo esc_url( $alert['alert_button']['url'] ); ?>" target="<?php echo esc_attr( $btn_target ); ?>"<?php echo $btn_rel; ?>><?php echo esc_html( $alert['alert_button']['title'] ); ?><?php echo $btn_sr; ?></a><?php
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
<script>
(function () {
	var tray = document.getElementById('custom-notification-tray');
	if (!tray) { return; }

	// Remove banners already dismissed (permanent "once" dismissals only).
	tray.querySelectorAll('.site-banner').forEach(function (banner) {
		var id = banner.getAttribute('data-alert-id');
		if (id && banner.classList.contains('js-banner-once')) {
			try {
				if (localStorage.getItem('alert_dismissed_' + id)) { banner.remove(); }
			} catch (e) {}
		}
	});

	// Nothing left to show — tidy up so no space is reserved.
	if (!tray.querySelector('.site-banner')) {
		tray.parentNode && tray.parentNode.removeChild(tray);
		if (document.body) { document.body.classList.remove('has-notification-banner'); }
		document.documentElement.style.setProperty('--dfc-alert-height', '0px');
		return;
	}

	// Reserve the correct offset before first paint so nothing jumps in.
	document.documentElement.style.setProperty('--dfc-alert-height', tray.offsetHeight + 'px');
})();
</script>
