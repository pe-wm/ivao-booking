<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */
?>

<main class="container" role="main">
<?php if (Session::LoggedIn()) : ?>
		<div class="greeting">
			Hello, <?=htmlspecialchars(Session::User()->firstname)?>! You are logged in.
		</div>
<?php endif; ?>

	<section class="hero">
		<div class="hero-copy">
			<h1><span><?=htmlspecialchars($config["event_name"] ?? "")?></span></h1>
			<p class="lead">Book your flight, request a private slot and join the event.</p>
			<div class="hero-actions">
				<a class="btn btn-primary" href="flights"><i class="fas fa-plane"></i> Flight booking</a>
				<a class="btn btn-secondary" href="briefing">Briefing</a>
			</div>			
		</div>
		<img src="https://pe.ivao.aero/wordpress/wp-content/uploads/ivao/events/2025/LATAM_MSE_2025.png" alt="LATAM MSE 2025" class="hero-image">
	</section>

	<div class="banner-content">
<?php include_once("contents/banner.html"); ?>
	</div>

</main>
<script>
(function () {
	var el = document.getElementById('heroClock');
	function tick() { el.textContent = new Date().toISOString().substr(11, 8) + ' UTC'; }
	tick();
	setInterval(tick, 1000);
})();
</script>