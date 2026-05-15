<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

global $config;
$prebookMode = isset($config["prebook"]) && $config["prebook"] == "true";
?>

<div class="modal fade" id="flight" tabindex="-1" role="dialog" data-prebook-mode="<?=($prebookMode ? 'true' : 'false')?>">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="btn-group btn-group-sm mr-2" role="toolbar" id="fltBtnsAdmin">
					<button type="button" class="btn btn-secondary" id="btnFltAdminEdit" data-toggle="collapse" data-target="#fltEdit">Edit</button>
					<button type="button" class="btn btn-danger" id="btnFltAdminDelete">Delete</button>
				</div>
				<h5 class="modal-title" id="fltTitle"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="collapse" id="fltEdit">
					<form id="frmFlightEdit">
						<?php include_once("inc/admin_flightedit.php"); ?>
					</form>
				</div>
				<div class="details">
					<div class="alert alert-primary" id="fltInfobox"></div>
					<div class="boarding-pass" id="fltBoardingPass">
						<div class="pass-main">
							<div class="pass-header">
								<div class="pass-logo" id="fltPassLogo"></div>
								<div class="pass-title">BOARDING PASS</div>
							</div>
							<div class="pass-body">
								<div class="pass-info-row">
									<div class="pass-info-group flex-2">
										<div class="pass-label">FLIGHT</div>
										<div class="pass-value" id="fltPassCallsign"></div>
										<div class="text-muted small" id="fltPassCallsignHuman"></div>
									</div>
									<div class="pass-info-group flex-3">
										<div class="pass-label">FROM / TO</div>
										<div class="pass-value"><span id="fltPassOriginIcao"></span> <i class="fas fa-arrow-right mx-1 small text-muted"></i> <span id="fltPassDestinationIcao"></span></div>
										<div class="text-muted small"><span id="fltPassOriginHuman"></span> / <span id="fltPassDestinationHuman"></span></div>
									</div>
									<div class="pass-info-group flex-3">
										<div class="pass-label">PASSENGER</div>
										<div class="pass-value" id="fltPassPassengerName">AVAILABLE</div>
									</div>
								</div>
								<div class="pass-info-row mt-4">
									<div class="pass-info-group">
										<div class="pass-label">AIRCRAFT</div>
										<div class="pass-value" id="fltPassAircraftIcao"></div>
									</div>
									<div class="pass-info-group">
										<div class="pass-label">DEPARTURE (Z)</div>
										<div class="pass-value" id="fltPassDepartureTime"></div>
									</div>
									<div class="pass-info-group">
										<div class="pass-label">ARRIVAL (Z)</div>
										<div class="pass-value" id="fltPassArrivalTime"></div>
									</div>
									<div class="pass-info-group">
										<div class="pass-label">POSITION</div>
										<div class="pass-value" id="fltPassPosition"></div>
									</div>
								</div>
							</div>
							<div class="pass-footer">
								<div class="pass-qr-group">
									<img id="fltPassQr" class="pass-qr" src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($config["url"] . "/mybookings") ?>" alt="QR Code">
									<div class="pass-event-name"><?= $config["event_name"] ?></div>
								</div>
								<div class="pass-airline-name" id="fltPassAirlineName"></div>
							</div>
						</div>
						<div class="pass-stub">
							<div class="pass-header">
								<div class="pass-title">STUB</div>
							</div>
							<div class="pass-stub-content">
								<div class="pass-label">FLIGHT</div>
								<div class="pass-value" id="fltStubCallsign"></div>
								
								<div class="pass-label mt-3">PASSENGER</div>
								<div class="pass-value text-truncate" style="max-width: 120px;" id="fltStubPassengerName">AVAILABLE</div>
								
								<div class="pass-info-row mt-3">
									<div class="pass-info-group">
										<div class="pass-label">DEPARTURE</div>
										<div class="pass-value" id="fltStubDepartureTime"></div>
									</div>
								</div>
							</div>
							<div class="pass-footer" style="border:none; padding:0; margin:0">
								<img id="fltStubQr" class="pass-qr-small" src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= urlencode($config["url"] . "/mybookings") ?>" alt="QR Code">
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-lg-12">
							<div class="head">Route <span id="fltGcd"></span></div>
							<p class="mt-2">
								<a href="https://www.simbrief.com/system/dispatch.php" target="_blank" class="btn btn-secondary btn-sm" id="btnSimbrief">SimBrief</a>									
								<button type="button" class="btn btn-info btn-sm" id="btnFltPrint" style="display:none"><i class="fas fa-print"></i> Print Boarding Pass</button>
							</p>
						</div>
					</div>


					<div class="flightCollapses" id="flightCollapses">
						<button class="btn btn-block btn-light collapsed" data-target="#fltMap" data-toggle="collapse">Show route on map</button>
						<div class="collapse card card-body" id="fltMap" data-parent="#flightCollapses"><div class="map" id="uiFltMap"></div></div>

						<button class="btn btn-block btn-light collapsed" data-target="#fltTurnovers" data-toggle="collapse" id="btnFltTurnovers">Turnover flight(s)</button>
						<div class="collapse card card-body" id="fltTurnovers" data-parent="#flightCollapses"></div>

						<button id="btnFltBriefing" class="btn btn-block btn-light collapsed" data-target="#fltBriefing" data-toggle="collapse">Flight briefing (weather, flight planning)</button>
						<div class="collapse card card-body" id="fltBriefing" data-parent="#flightCollapses">
<?php if (!empty($config["wx_url"])) : ?>
							<p>
								<button class="btn btn-info btn-sm" id="fltMetarOrigin"></button>
								<button class="btn btn-info btn-sm" id="fltTafOrigin"></button>
								<button class="btn btn-info btn-sm" id="fltMetarDestination"></button>
								<button class="btn btn-info btn-sm" id="fltTafDestination"></button>
							</p>
							<div id="fltWxResult" class="card card-body wxResult"></div>
<?php endif; ?>
							<p>
								<a href="http://rfinder.asalink.net/free" target="_blank" class="btn btn-secondary btn-sm">RouteFinder</a>
							</p>
						</div>
					</div>

					<div class="flighttiles" id="fltBookedBy">
						<div class="row">
							<div class="col-lg-6">
								<div class="head">Flight already booked by</div>
								<div id="fltBookedByName" class="big"></div>
							</div>
							<div class="col-lg-3">
								<div class="head">VID</div>
								<div id="fltBookedByVid" class="big"></div>
							</div>
							<div class="col-lg-3">
								<div class="head">Division</div>
								<div id="fltBookedByDivision" class="pt-1"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="head">Pilot rating</div>
								<div id="fltBookedByRating" class="pt-2"></div>
							</div>
							<div class="col-lg-6">
								<div class="head">Flight has been booked at</div>
								<div id="fltBookedAt" class="big"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div id="fltBtnsLoggedOut" style="display: none">
					<a class="btn btn-primary" href="login">Click here to log in</a>
				</div>
				<div id="fltBtnsDefault" style="display: none">
					<button type="button" class="btn btn-success" id="btnFltBook">Book this flight now!</button>
					<button type="button" class="btn btn-primary" id="btnFltConfirmPrebook">Confirm pre-booking</button>
					<button type="button" class="btn btn-danger" id="btnFltFree">Delete booking</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
				<div id="fltBtnsConfirm" style="display: none">
					<button type="button" class="btn btn-primary" id="btnFltConfirm">I agree, book the flight</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">I don't agree</button>	
				</div>
			</div>
		</div>
	</div>
</div>

<?php
Pages::AddJS("admin_flightedit");
?>