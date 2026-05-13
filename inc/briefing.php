<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

global $config;
?>

<main class="container" role="page">
	<h1>About <?=$config["event_name"]?></h1>
<?php if (!empty($config["event_banner"])) : ?>
	<img src="<?=$config["event_banner"]?>" alt="<?=$config["event_name"]?>" class="mb-5 mx-auto d-block rounded img-fluid">
<?php endif; ?>
	<h2>About the event</h2>

	<p><?= nl2br($config["event_about"] ?? "") ?></p>
</main>
