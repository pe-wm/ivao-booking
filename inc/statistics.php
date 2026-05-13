<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

/**
 * Returns a progress bar according to the given parametres
 * @param int $prebooked
 * @param int $booked
 * @param int $all
 * @return string HTML
 */
function progressBar($prebooked, $booked, $all)
{
	if ($all < 1)
	{
		return '
		<div class="progress" style="height: 1.5rem">
		</div>';
	}
	else
	{
		$percentPrebooked = $prebooked / $all * 100;
		$percentBooked = $booked / $all * 100;
		$percentFree = 100 - $percentPrebooked - $percentBooked;
		return '
			<div class="progress" style="height: 1.5rem">
				<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: ' . $percentPrebooked . '%" aria-valuenow="' . $percentPrebooked . '" aria-valuemin="0" aria-valuemax="100">' . ($percentPrebooked > 4 ? round($percentPrebooked, 2) . '%' : '') . '</div>
				<div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: ' . $percentBooked . '%" aria-valuenow="' . $percentBooked . '" aria-valuemin="0" aria-valuemax="100">' . ($percentBooked > 4 ? round($percentBooked, 2) . '%' : '') . '</div>
				<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: ' . $percentFree . '%" aria-valuenow="' . $percentFree . '" aria-valuemin="0" aria-valuemax="100">' . ($percentFree > 4 ? round($percentFree, 2) . '%' : '') . '</div>
			</div>';
	}
}

/**
 * Returns a box of statistics about the given airport
 * if airport = null, it is the all-long statistics
 * @param Airport $airport
 * @return string HTML
 */
function statisticLine($airport = null)
{
	$result = '<div class="airport">';
	if (!$airport)
	{
		$result .= '<h2>Overall statistics</h2>';
		$stat = EventAirport::getStatisticsAll();
		$prebooked = $stat["prebooked"];
		$booked = $stat["booked"];
		$free = $stat["free"];
		$result .= progressBar($prebooked, $booked, $free + $prebooked + $booked);
	}
	else
	{
		$result .= '<h2>';
		
		if ($apt = $airport->getAirport())
			$result .= $airport->getAirport()->getCountryFlag();
			
		$result .= ' <a href="flights#' . $airport->icao . '">' . $airport->icao . '</a> <small>' . $airport->name . '</small></h2>';
		$stat = $airport->getStatistics();
		$prebooked = $stat["prebooked"];
		$booked = $stat["booked"];
		$free = $stat["free"];
		$result .= progressBar($prebooked, $booked, $free + $prebooked + $booked);
	}
	$result .= '
			<div class="row">
				<div class="col-sm-3 h5 text-center">
					Prebooked: <span class="badge badge-warning">' . $prebooked . '</span>
				</div>
				<div class="col-sm-3 h5 text-center">
					Booked: <span class="badge badge-danger">' . $booked . '</span>
				</div>
				<div class="col-sm-3 h5 text-center">
					Free: <span class="badge badge-success">' . $free . '</span>
				</div>
				<div class="col-sm-3 h5 text-center">
					All flights: <span class="badge badge-primary">' . ($free + $prebooked + $booked) . '</span>
				</div>
			</div>
		</div>';
	return $result;
}

?>

<main role="main" class="container">
	<h1>Statistics</h1>

	<?php
	echo statisticLine();

	$apts = EventAirport::GetAll();
	if (count($apts) > 1)
	{
		foreach (EventAirport::GetAll() as $airport)
			echo statisticLine($airport);
	}
	?>	
</main>
