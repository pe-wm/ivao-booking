<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

global $config;

function slotsTable($airport)
{
	$result = '<div class="table-responsive">
		<table class="table table-hover table-sm table-striped">
			<thead>
				<tr>
					<th>Date & time</th>
					<th>Booking</th>
				</tr>
			</thead>
			<tbody>';

	$timeframes = $airport->getTimeframes();
	if (empty($timeframes))
	{
		$result .= '<tr><td colspan="2" style="font-style: italic; text-align: center">(no available timeframes)</td></tr>';
	}
	else
	{
		foreach ($timeframes as $tf)
		{
			$result .= '<tr>';
			$result .= '	<td>' . getHumanDateTime($tf->time) . '</td>';

			$stats = $tf->getStatistics();
			if ($stats["free"] > 0)
				$result .= '	<td><button class="btn btn-sm btn-success btn-block" onclick="getTimeframe(' . $tf->id . ')"><i class="fas fa-gavel"></i> Request now!</button></td>';	
			else
				$result .= '	<td><button class="btn btn-sm btn-danger btn-block" onclick="getTimeframe(' . $tf->id . ')"><i class="fas fa-times"></i> Not available</button></td>';	
			$result .= '</tr>';
		}
	}

	$result .= '</tbody>
		</table>
	</div>';
	return $result;
}

// getting all airports which are participating in the event
$apts = EventAirport::GetAll();

echo '<main role="main" class="container">';
echo '<h1>Request a slot!</h1>';

if (count($apts) > 0)
{
	echo '<div class="row">';

	echo '<div class="col-lg-4">';
	foreach ($apts as $apt)
	{
		echo '<div class="airport" id="' . $apt->icao . '">';
		if ($airport = $apt->getAirport())
			echo '<h2>' . $airport->getCountryFlag(32) . '<span data-toggle="tooltip" title="' . $apt->name . '">' . $apt->icao . '</span> Fly in/out</h2>';
		else
			echo '<h2><span data-toggle="tooltip" title="' . $apt->name . '">' . $apt->icao . '</span></h2>';

		echo slotsTable($apt);
		
		echo '</div>';
	}
	echo '</div>';
?>
	<div class="col-lg-8">
		<div class="collapse" id="timeframe">
			<div class="card" style="margin-bottom: 2rem">
				<h5 class="card-header"><span id="timeframeTitle"></span>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeTimeframe()">
						<span aria-hidden="true">&times;</span>
					</button>
				</h5>
				<div class="card-body">
					<div class="alert alert-success" id="timeframeStatus"></div>
					<div class="table-responsive">
						<table class="table table-hover table-sm table-striped" id="tblSlots">
							<thead>
								<tr>
									<th>Callsign</th>
									<th>Aircraft</th>
									<th>Origin</th>
									<th>Destination</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
							</tbody>						
						</table>
					</div>

<?php if (Session::LoggedIn()) : ?>
					<div class="card card-body" id="slotRequest">
						<h5>Request a slot</h5>
						<form id="frmSlotRequest">
							<input type="hidden" id="slotIcao">
							<input type="hidden" id="timeframeId">
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Callsign:</label>
								<div class="col-sm-10">
									<input class="form-control input-uppercase" id="txtSrCallsign" type="text" placeholder="ICAO callsign, e.g. AUA714C" required maxlength="10">											
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Airports:</label>
								<div class="col-sm-10">
									<div class="form-row">									
										<div class="col">
											<input class="form-control input-uppercase" id="txtSrOriginIcao" type="text" placeholder="ICAO of origin" required maxlength="4">
										</div>
										<div class="col">
											<input class="form-control input-uppercase" id="txtSrDestinationIcao" type="text" placeholder="ICAO of destination" required maxlength="4">
										</div>										
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Aircraft:</label>
								<div class="col-sm-10">
									<div class="form-row">									
										<div class="col">
											<input class="form-control input-uppercase" id="txtSrAircraftIcao" type="text" placeholder="ICAO identifier, e.g. B738" required maxlength="4">
										</div>
										<div class="col">
											<div class="form-check" style="margin-top: 0.4rem">
												<input class="form-check-input" type="checkbox" id="chkSrFreighter">
												<label class="form-check-label" for="chkSrFreighter">freighter aircraft</label>
											</div>
										</div>										
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Route:</label>
								<div class="col-sm-10">
									<input class="form-control input-uppercase" type="text" id="txtSrRoute" required placeholder="e.g. ADAMA Z647 ANEXA">
								</div>
							</div>
							
							<button class="btn btn-info" type="submit">Send slot request</button>
						</form>
					</div>
<?php else : ?>
						You must be <a href="login"><strong>logged on</strong></a> to request a slot!
<?php endif; ?>
				</div>
			</div>
		</div>
		
		<div class="text-justify">
			<h3>Instructions for slot bookings</h3>

			<p>If you want to participate in the event, you need to have a reserved slot. Also note that to report the point for the medal you need to have a reserved slot, including several advantages that you will discover in our social networks and forum where you can find all the information about the event (https://forum.ivao.aero/threads/latam-mse.378087/). </p>
			<p>Requesting a slot does not mean it is yours instantly. Our Events staff will review and evaluate your application. To know the result, you will have to check it on the website as "Granted to [Your VID]", in case it is not possible we will contact you.</p>
			<p>To request a slot, click on the button next to the desired time slot. Please note that we can announce more than one possible slot for the same time slot. The red button means that the time slot is full, no more requests can be submitted.</p>

			<p>If you have any questions or problems, please contact IVAO PERU Events staff via our email address (pe-events@ivao.aero) with the subject [SLOTS LATAM MSE].</p>			
		</div>
	</div>
<?php
}
else
	echo '<div class="alert alert-info">There are no airports participating on the event currently. Please check back regularly!</div>';

echo '</main>';

include_once("inc/modal_slot.php");

?>