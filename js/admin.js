$("#tabAirportsLink").on("shown.bs.tab", function () {
	aCloseAirport();
	aGetAirports();
});
$("#tabUsersLink").on("shown.bs.tab", function () {
	aCloseUser();
	aGetUsers();
});
$("#tabNewFlightLink").on("shown.bs.tab", function () { aNewFlight(); });
$("#tabTimeframesLink").on("shown.bs.tab", function () {
	aCloseTimeframe();
	aGetTimeframes();
});
$("#tabContentsLink").on("shown.bs.tab", function () {
	aCloseContent();
	aGetContents();
});

$("#frmGeneral").submit(function (e) {
	e.preventDefault();

	// if URL does not start with http, assign it
	if (!$("#txtDivisionWeb").val().startsWith("http"))
		$("#txtDivisionWeb").val("http://" + $("#txtDivisionWeb").val());

	var eventName = $("#txtEventName").val();
	var eventAbout = $("#txtEventAbout").val();
	var eventBanner = $("#txtEventBanner").val();
	var mode = $("#selMode").val();
	var prebook = $("#chkPrebook").is(":checked") ? "true" : "false";
	var divisionName = $("#txtDivisionName").val();
	var divisionWeb = $("#txtDivisionWeb").val();
	var divisionEmail = $("#txtDivisionEmail").val();
	var divisionFacebook = $("#txtDivisionDiscord").val();
	var divisionTwitter = $("#txtDivisionInstagram").val();
	var wxUrl = $("#txtWxUrl").val();
	var dateStart = $("#dtpEventStart").datetimepicker("viewDate").format("YYYY-MM-DD HH:mm:00");
	var dateEnd = $("#dtpEventEnd").datetimepicker("viewDate").format("YYYY-MM-DD HH:mm:00");

	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "admin", "action": "updateGeneral", "event_name": eventName, "event_about": eventAbout, "event_banner": eventBanner, "mode": mode, "prebook": prebook, "division_name": divisionName, "division_web": divisionWeb, "division_email": divisionEmail, "division_facebook": divisionFacebook, "division_twitter": divisionTwitter, "wx_url": wxUrl, "date_start": dateStart, "date_end": dateEnd },
		success: function (data) {
			if (data && data.error == 0) {
				toast({
					title: "The settings have been saved!",
					type: "success",
				});
			}
			else
				notification(data);
		},
	});
});

function aGetAirports() {
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "eventairports", "action": "getall" },
		success: function (data) {
			var content = "";
			for (var i = 0; i < data.length; i++) {
				content += '<tr style="cursor: pointer" onclick="aGetAirport(' + data[i].id + ')" id="airport' + data[i].id + '">';

				if (data[i].airport)
					content += '<td>' + data[i].airport.countryFlag24 + data[i].icao + '</td>';
				else
					content += '<td>' + data[i].icao + '</td>';

				content += '<td>' + data[i].name + '</td>';
				content += '<td>' + data[i].order + '</td>';
				content += '<td>' + (data[i].enabled == 1 ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>') + '</td>';
				content += '</tr>';
			}
			$("#tblAirports").find("tbody").html(content);
		}
	});
}

function aGetAirport(id) {
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "eventairports", "id": id },
		success: function (data) {
			$("#lblAirport").html("Edit airport");
			$("#aptId").val(data.id);
			$("#txtAirportIcao").val(data.icao);
			$("#txtAirportName").val(data.name);
			$("#numAirportOrder").val(Number(data.order));
			$("#chkAirportEnabled").prop("checked", data.enabled == 1);
			$("#btnAirportDelete").show()
				.attr("onClick", "aDeleteAirport(" + data.id + ")");
			$("#editAirport").collapse("show");
			scroll("#editAirport");
		}
	});
}

function aNewAirport() {
	$("#aptId").val(-1);
	$("#lblAirport").html("Add new airport");
	$("#txtAirportIcao").val(null);
	$("#txtAirportName").val(null);
	$("#numAirportOrder").val(0);
	$("#chkAirportEnabled").prop("checked", "true");
	$("#btnAirportDelete").hide();
	$("#editAirport").collapse("show");
	scroll("#editAirport");
}

function aDeleteAirport(id) {
	swal2({
		title: "Are you sure you want to delete this airport?",
		type: "warning",
		showCancelButton: true,
		cancelButtonText: "No, don't delete",
		confirmButtonText: "Yes, delete it"
	}).then((result) => {
		if (result.value) {
			$.ajax({
				cache: false,
				type: "POST",
				url: "json",
				data: { "type": "eventairports", "id": id, "action": "delete" },
				success: function (data) {
					if (data && data.error == 0) {
						toast({
							title: "The airport has been deleted!",
							type: "success",
						});
						aCloseAirport();
						aGetAirports();
					}
					else
						notification(data);
				}
			});
		}
	});
}

$("#frmAirportEdit").submit(function (e) {
	e.preventDefault();
	var id = $("#aptId").val();
	var name = $("#txtAirportName").val();
	var icao = $("#txtAirportIcao").val().toUpperCase();
	var order = $("#numAirportOrder").val();
	var enabled = $("#chkAirportEnabled").is(":checked") ? 1 : 0;
	var type = $("#txtAirportType").val();
	var latitude = $("#txtAirportLatitude").val();
	var longitude = $("#txtAirportLongitude").val();
	var altitude = $("#txtAirportAltitude").val();
	var country = $("#txtAirportCountry").val();
	var iata = $("#txtAirportIata").val();
	if (id == -1) {
		$.ajax({
			cache: false,
			type: "POST",
			url: "json",
			data: { "type": "eventairports", "action": "create", "icao": icao, "name": name, "order": order, "enabled": enabled, "tipo": type, "latitude": latitude, "longitude": longitude, "altitude": altitude, "country": country, "iata": iata },
			success: function (data) {
				if (data && data.error == 0) {
					toast({
						title: "The airport has been added!",
						type: "success",
					});
					aCloseAirport();
					aGetAirports();
				}
				else
					notification(data);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.error("AJAX Error:", textStatus, errorThrown);
				console.log("Server response:", jqXHR.responseText);
			}
		});
	}
	else {
		$.ajax({
			cache: false,
			type: "POST",
			url: "json",
			data: { "type": "eventairports", "id": id, "action": "update", "icao": icao, "name": name, "order": order, "enabled": enabled },
			success: function (data) {
				if (data && data.error == 0) {
					toast({
						title: "The airport has been modified!",
						type: "success",
					});
					aCloseAirport();
					aGetAirports();
				}
				else
					notification(data);
			}
		});
	}
});

function aCloseAirport() {
	$("#editAirport").collapse("hide");
	scroll("body");
}

function aGetUsers() {
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "users", "action": "getall" },
		success: function (data) {
			var content = "";
			for (var i = 0; i < data.length; i++) {
				content += '<tr style="cursor: pointer" onclick="aGetUser(' + data[i].id + ')" id="user' + data[i].id + '">';
				content += '<td>' + data[i].vid + '</td>';
				content += '<td>' + data[i].firstname + ' ' + data[i].lastname + '</td>';
				content += '<td>' + data[i].divisionBadge + '</td>';
				content += '<td>' + (data[i].email.length < 1 ? "(not set)" : data[i].email) + '</td>';

				switch (Number(data[i].permission)) {
					case 0:
						content += '<td>banned</td>';
						break;
					case 1:
						content += '<td>normal</td>';
						break;
					case 2:
						content += '<td>administrator</td>';
						break;
				}

				var loginDate = data[i].last_login ? data[i].last_login.split(" ")[0] : 'Never';
				content += '<td class="text-nowrap">' + loginDate + '</td>';

				content += '</tr>';
			}
			$("#tblUsers").find("tbody").html(content);
		}
	});
}

function aGetUser(id) {
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "users", "id": id },
		success: function (data) {
			$("#lblUser").html("Edit user");
			$("#userId").val(data.id);
			$("#numUserVid").val(Number(data.vid));
			$("#txtUserFirstname").val(data.firstname);
			$("#txtUserLastname").val(data.lastname);
			$("#txtUserDivision").val(data.division);
			$("#txtUserEmail").val(data.email);
			$("#chkUserPrivacy").prop("checked", data.privacy == 1);
			$("#selUserPermission").val(data.permission);
			$("#btnUserDelete").show()
				.attr("onClick", "aDeleteUser(" + data.id + ")");

			// populating booked flights
			$("#userFlights").collapse("hide");
			var content = '<div class="list-group">';
			if (data.flights.length > 0) {
				$.each(data.flights, function () {
					content += '<a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="getFlight(' + this.id + ')">';
					content += (this.airline ? this.airline.logo : "") + '<strong>' + this.callsign + '</strong> ' + this.originIcao + ' – ' + this.destinationIcao + '<span class="float-right">';
					switch (this.booked) {
						case 'free':
							content += '<span class="badge badge-success">Free</span>';
							break;
						case 'prebooked':
							content += '<span class="badge badge-warning">Prebooked</span>';
							break;
						case 'booked':
							content += '<span class="badge badge-danger">Booked</span>';
							break;
					}
					content += '</span></a>';
				});
			}
			else
				content += '<a href="javascript:void(0)" class="list-group-item list-group-item-action" style="font-style: italic; text-align: center">(no booked flights yet)</a>';
			content += '</div>';
			$("#userFlights").html(content);

			// populating slots
			$("#userSlots").collapse("hide");
			var content = '<div class="list-group">';
			if (data.slots.length > 0) {
				$.each(data.slots, function () {
					content += '<a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="getSlot(' + this.id + ')">';
					content += (this.airline ? this.airline.logo : "") + '<strong>' + this.callsign + '</strong> ' + this.originIcao + ' – ' + this.destinationIcao + '<span class="float-right">';
					switch (this.booked) {
						case 'requested':
							content += '<span class="badge badge-warning">Requested</span>';
							break;
						case 'granted':
							content += '<span class="badge badge-danger">Granted</span>';
							break;
					}
					content += '</span></a>';
				});
			}
			else
				content += '<a href="javascript:void(0)" class="list-group-item list-group-item-action" style="font-style: italic; text-align: center">(no private slots yet)</a>';
			content += '</div>';
			$("#userSlots").html(content);

			$("#editUser").collapse("show");
			scroll("#editUser");
		}
	});
}

function aNewUser() {
	$("#lblUser").html("Add new user");
	$("#userId").val(Number(-1));
	$("#numUserVid").val(null);
	$("#txtUserFirstname").val(null);
	$("#txtUserLastname").val(null);
	$("#txtUserDivision").val(null);
	$("#txtUserEmail").val(null);
	$("#chkUserPrivacy").prop("checked", false);
	$("#selUserPermission").val(1);
	$("#btnUserDelete").hide();
	$("#editUser").collapse("show");
	scroll("#editUser");
}

function aCloseUser() {
	$("#editUser").collapse("hide");
	scroll("body");
}

$("#frmUserEdit").submit(function (e) {
	e.preventDefault();
	var id = $("#userId").val();
	var vid = $("#numUserVid").val();
	var firstname = $("#txtUserFirstname").val();
	var lastname = $("#txtUserLastname").val();
	var division = $("#txtUserDivision").val().toUpperCase();
	var email = $("#txtUserEmail").val();
	var permission = $("#selUserPermission").val();
	var privacy = $("#chkUserPrivacy").is(":checked");

	if (id == -1) {
		$.ajax({
			cache: false,
			type: "POST",
			url: "json",
			data: { "type": "users", "action": "create", "vid": vid, "firstname": firstname, "lastname": lastname, "division": division, "email": email, "permission": permission, "privacy": privacy },
			success: function (data) {
				if (data && data.error == 0) {
					toast({
						title: "The user has been created!",
						type: "success",
					});
					aCloseUser();
					aGetUsers();
				}
				else
					notification(data);
			}
		});
	}
	else {
		$.ajax({
			cache: false,
			type: "POST",
			url: "json",
			data: { "type": "users", "id": id, "action": "update", "vid": vid, "firstname": firstname, "lastname": lastname, "division": division, "email": email, "permission": permission, "privacy": privacy },
			success: function (data) {
				if (data && data.error == 0) {
					toast({
						title: "The user has been modified!",
						type: "success",
					});
					aCloseUser();
					aGetUsers();
				}
				else
					notification(data);
			}
		});
	}
});

function aDeleteUser(id) {
	swal2({
		title: "Are you sure you want to delete this user?",
		type: "warning",
		confirmButtonText: "Yes, delete it",
		showCancelButton: true,
		cancelButtonText: "No, don't delete",
	}).then((result) => {
		if (result.value) {
			$.ajax({
				cache: false,
				type: "POST",
				url: "json",
				data: { "type": "users", "id": id, "action": "delete" },
				success: function (data) {
					if (data && data.error == 0) {
						toast({
							title: "The user has been deleted!",
							type: "success",
						});
						aCloseUser();
						aGetUsers();
					}
					else
						notification(data);
				}
			});
		}
	});
}

function aNewFlight() {
	$("#fltId").val(-1);
	$("#txtFltFlightNumber").val(null);
	$("#txtFltCallsign").val(null);
	$("#txtFltOriginIcao").val(null);
	$("#txtFltDestinationIcao").val(null);
	$("#txtFltAircraftIcao").val(null);
	$("#chkFltFreighter").prop("checked", false);
	$("#txtFltTerminal").val(null);
	$("#txtFltGate").val(null);
	$("#txtFltRoute").val(null);
	$("#selFltStatus").val(0);
	$("#numFltBookedBy").val(0);
	$("#selFltStatus").trigger("change");
	$("#dtpFltDeparture").datetimepicker("clear");
	$("#dtpFltArrival").datetimepicker("clear");
}

function aGetTimeframes() {
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "timeframes", "action": "getall" },
		success: function (data) {
			var content = "";
			var pending = 0;
			for (var i = 0; i < data.length; i++) {
				pending += data[i].statistics.requested;
				content += '<tr style="cursor: pointer" onclick="aGetTimeframe(' + data[i].id + ')" id="slot' + data[i].id + '">';

				if (apt = data[i].eventAirport)
					content += '<td>' + (apt.airport ? apt.airport.countryFlag24 : '') + apt.icao + ' <small class="text-muted">' + apt.name + '</small></td>';
				else
					content += '<td>' + data[i].airportIcao + '</td>';

				content += '<td>' + data[i].timeHuman + '</td>';
				content += '<td>';
				content += '<span class="badge badge-success">Free: ' + data[i].statistics.free + '</span> <span class="badge badge-danger"> Granted: ' + data[i].statistics.granted + '</span> ';
				if (data[i].statistics.requested > 0)
					content += '<span class="badge badge-warning">Pending: ' + data[i].statistics.requested + '</span>';
				content += '</td>';
				content += '</tr>';
			}
			$("#tblTimeframes").find("tbody").html(content);

			if (pending > 0) {
				if (pending == 1)
					$("#slotAlert").html("You have <strong>one</strong> request waiting for evaluation!");
				else
					$("#slotAlert").html("You have <strong>" + pending + "</strong> requests waiting for evaluation!");
				$("#slotAlert").show();
			}
			else
				$("#slotAlert").hide();


			// populating airport selector
			$.ajax({
				cache: false,
				type: "POST",
				url: "json",
				data: { "type": "eventairports", "action": "getall" },
				success: function (data) {
					var content = "";
					$.each(data, function () {
						content += '<option value="' + this.icao + '">' + this.icao + ' - ' + this.name + (!this.enabled ? " (disabled)" : "") + '</option>';
					});
					$("#selTfNewAirport").html(content);
				}
			});
		}
	});
}

function aCloseTimeframe() {
	$("#timeframe").collapse("hide");
	$("#timeframeEdit").collapse("hide");
	$("#timeframeNew").collapse("hide");
	scroll("body");
}

function aCloseTimeframeEdit() {
	$("#timeframeEdit").collapse("hide");
	scroll("body");
}

function aCloseTimeframeNew() {
	$("#timeframeNew").collapse("hide");
	scroll("body");
}

function aNewTimeframe() {
	$("#timeframe").collapse("hide");
	$("#timeframeNew").collapse("show");
	scroll("#timeframeNew");
}

$("#numTfNewHourFrom").on("change", function () {
	var from = Number($("#numTfNewHourFrom").val());
	var to = Number($("#numTfNewHourTo").val());

	$("#numTfNewHourTo").prop("min", from);
	if (to <= from)
		$("#numTfNewHourTo").val(from);
});


function aGetTimeframe(id) {
	$("#timeframeNew").collapse("hide");
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "timeframes", "id": id },
		success: function (data) {
			$("#timeframeId").val(id);

			if (data.eventAirport && data.eventAirport.airport)
				$("#timeframeTitle").html("Private slots at " + data.timeHuman + " at " + data.eventAirport.airport.countryFlag24 + data.eventAirport.icao);
			else
				$("#timeframeTitle").html("Private slots at " + data.timeHuman + " at " + data.aircraftIcao);

			if (data.eventAirport)
				$("#txtTfEditAirport").val(data.airportIcao + " - " + data.eventAirport.name);
			else
				$("#txtTfEditAirport").val(data.airportIcao);

			$("#dtpTfEditDate").datetimepicker("date", moment(data.time));
			$("#numTfEditCount").val(Number(data.count));
			$("#btnTfDelete").attr("onclick", "aDeleteTimeframe(" + id + ")");

			var tbl = "";
			if (data.slots) {
				for (var i = 0; i < data.slots.length; i++) {
					var row = data.slots[i];
					tbl += '<tr>';

					if (airline = row.airline)
						tbl += '<td data-toggle="tooltip" title="' + airline.name + '">' + airline.logoSmall + row.callsign + '</td>';
					else
						tbl += '<td>' + row.callsign + '</td>';

					tbl += '<td data-toggle="tooltip" title="' + (row.aircraftName ? row.aircraftName : "") + '">' + row.aircraftIcao + (row.aircraftFreighter ? "/F" : "") + '</td>';

					if (orig = row.originAirport)
						tbl += '<td>' + orig.countryFlag24 + '<span data-toggle="tooltip" title="' + orig.name + '">' + orig.icao + '</span></td>';
					else
						tbl += '<td>' + row.originIcao + '</td>';

					if (dest = row.destinationAirport)
						tbl += '<td>' + dest.countryFlag24 + '<span data-toggle="tooltip" title="' + dest.name + '">' + dest.icao + '</span></td>';
					else
						tbl += '<td>' + row.destinationIcao + '</td>';

					tbl += '<td>' + row.bookedAtHuman + '</td>';

					if (row.booked == "requested")
						tbl += '<td><button class="btn btn-warning btn-sm btn-block" onclick="getSlot(' + row.id + ')"><i class="far fa-hand-paper"></i> Requested by <strong>' + row.bookedBy + '</strong></button></td>';
					else
						tbl += '<td><button class="btn btn-danger btn-sm btn-block" onclick="getSlot(' + row.id + ')"><i class="far fa-handshake"></i> Granted to <strong>' + row.bookedBy + '</strong></button></td>';
					tbl += '</tr>';
				}
			}
			else
				tbl += '<tr><td colspan="6" style="font-style: italic; text-align: center">(no slots yet)</td></tr>';
			$("#tblSlots tbody").html(tbl);

			$("#timeframe").collapse("show");
			scroll("#timeframe");
		}
	});
}

$("#frmTimeframeNew").submit(function (e) {
	e.preventDefault();
	var airportIcao = $("#selTfNewAirport").val();
	var date = $("#dtpTfNewDate").datetimepicker("viewDate").format("YYYY-MM-DD");
	var hourFrom = $("#numTfNewHourFrom").val();
	var hourTo = $("#numTfNewHourTo").val();
	var minute = $("#numTfNewMinute").val();
	var count = $("#numTfNewCount").val();

	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "timeframes", "action": "create", "airport_icao": airportIcao, "date": date, "hour_from": hourFrom, "hour_to": hourTo, "minute": minute, "count": count },
		success: function (data) {
			if (data && data.error == 0) {
				toast({
					title: "The timeframes have been created!",
					type: "success",
				});
				aGetTimeframes();
				aCloseTimeframeNew();
			}
			else
				notification(data);
		}
	});
});

function aDeleteTimeframe(id) {
	swal2({
		title: "Are you sure you want to delete this timeframe?",
		text: "All child flights will also be removed.",
		type: "warning",
		showCancelButton: true,
		cancelButtonText: "No, don't delete",
		confirmButtonText: "Yes, delete it"
	}).then((result) => {
		if (result.value) {
			$.ajax({
				cache: false,
				type: "POST",
				url: "json",
				data: { "type": "timeframes", "id": id, "action": "delete" },
				success: function (data) {
					if (data && data.error == 0) {
						toast({
							title: "The timeframe has been deleted!",
							type: "success",
						});
						aCloseTimeframe();
						aGetTimeframes();
					}
					else
						notification(data);
				}
			});
		}
	});
}

$("#frmTimeframeEdit").submit(function (e) {
	e.preventDefault();
	var id = $("#timeframeId").val();
	var count = $("#numTfEditCount").val();
	var time = $("#dtpTfEditDate").datetimepicker("viewDate").format("YYYY-MM-DD HH:mm:00");

	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "timeframes", "id": id, "action": "update", "count": count, "time": time },
		success: function (data) {
			if (data && data.error == 0) {
				toast({
					title: "The timeframe has been updated!",
					type: "success",
				});
				aCloseTimeframeEdit();
				aGetTimeframe(id);
				aGetTimeframes();
			}
			else
				notification(data);
		},
	});
});

$("#btnAdminEmailFltConfirmation").on("click", function () {
	swal2({
		title: "Are you sure you want to send the mails?",
		text: "It might take for a while.",
		type: "question",
		showCancelButton: true,
		cancelButtonText: "No, rather don't",
		confirmButtonText: "Yes, send 'em"
	}).then((result) => {
		if (result.value) {
			$.ajax({
				cache: false,
				type: "POST",
				url: "json",
				data: { "type": "email", "action": "sendFlightConfirmations" },
				success: function (data) {
					if (data && data.error == 0) {
						toast({
							title: "The confirmation emails have been sent.",
							type: "success",
						});
					}
					else if (data && data.error == 1) {
						toast({
							title: "The confirmation emails have been sent with one or more errors.",
							type: "warning",
						});
					}
					else
						notification(data);
				}
			});
		}
	});
});

$("#frmAdminEmail").submit(function (e) {
	e.preventDefault();
	var recipientsCode = $("#selAdminEmailRecipients").val();
	var subject = $("#txtAdminEmailSubject").val();
	var message = CKEDITOR.instances.txtAdminEmailMessage.getData();

	if (recipientsCode >= 1 && recipientsCode <= 4) {
		if (message.length > 0) {
			swal2({
				title: "Are you sure you want to send the mails?",
				text: "It might take for a while.",
				type: "question",
				showCancelButton: true,
				cancelButtonText: "No, rather don't",
				confirmButtonText: "Yes, send 'em"
			}).then((result) => {
				if (result.value) {
					$.ajax({
						cache: false,
						type: "POST",
						url: "json",
						data: { "type": "email", "action": "sendFreeText", "subject": subject, "message": message, "recipients_code": recipientsCode },
						success: function (data) {
							if (data && data.error == 0) {
								toast({
									title: "The emails have been sent.",
									type: "success",
								});
							}
							else if (data && data.error == 1) {
								toast({
									title: "The emails have been sent with one or more errors.",
									type: "warning",
								});
							}
							else
								notification(data);
						}
					});
				}
			});
		}
		else
			toast({
				title: "The text of email cannot be empty.",
				type: "error",
			});
	}
	else
		toast({
			title: "The recipient group must be selected!",
			type: "error",
		});
});

function adminExportFlights() {
	$.ajax({
		cache: false,
		type: "POST",
		url: "json",
		data: { "type": "admin", "action": "exportFlights" },
		success: function (data) {
			if (data && data.error == 0 && data.data) {
				var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(data.data, null, 2));
				var downloadAnchorNode = document.createElement('a');
				downloadAnchorNode.setAttribute("href", dataStr);
				downloadAnchorNode.setAttribute("download", "flights_export.json");
				document.body.appendChild(downloadAnchorNode);
				downloadAnchorNode.click();
				downloadAnchorNode.remove();
			} else {
				toast({
					title: "Error exporting flights.",
					type: "error",
				});
			}
		}
	});
}

function adminClearFlights() {
	swal2({
		title: "Are you sure?",
		text: "This will delete all flights from the database. It cannot be undone.",
		type: "warning",
		showCancelButton: true,
		confirmButtonText: "Yes, delete them all"
	}).then((result) => {
		if (result.value) {
			swal2({
				title: "Are you absolutely sure?",
				text: "Double-checking! This will really delete all flights.",
				type: "error",
				showCancelButton: true,
				confirmButtonText: "I'm sure, clear DB!"
			}).then((result2) => {
				if (result2.value) {
					$.ajax({
						cache: false,
						type: "POST",
						url: "json",
						data: { "type": "admin", "action": "clearFlights" },
						success: function (data) {
							if (data && data.error == 0) {
								toast({
									title: "All flights cleared successfully.",
									type: "success",
								});
							} else {
								toast({
									title: "Error clearing flights.",
									type: "error",
								});
							}
						}
					});
				}
			});
		}
	});
}

function adminImportFlights() {
	var fileInput = document.getElementById("fileImportFlights");
	if (fileInput.files.length === 0) {
		toast({ title: "Please select a JSON file first.", type: "warning" });
		return;
	}

	var file = fileInput.files[0];
	var reader = new FileReader();

	reader.onload = function(e) {
		var content = e.target.result;
		try {
			// Validate it's proper JSON
			JSON.parse(content);
			
			var appendStr = "true";
			var doImport = function() {
				$.ajax({
					cache: false,
					type: "POST",
					url: "json",
					data: { "type": "admin", "action": "importFlights", "flights_json": content, "append": appendStr },
					success: function (data) {
						if (data && data.error == 0) {
							toast({
								title: "Flights imported successfully.",
								type: "success",
							});
							fileInput.value = "";
						} else {
							toast({
								title: "Error importing flights.",
								type: "error",
							});
						}
					}
				});
			};

			swal2({
				title: "Clear before import?",
				text: "Do you want to clear the existing flights before importing?",
				type: "question",
				showCancelButton: true,
				confirmButtonText: "Yes, clear first",
				cancelButtonText: "No, just append"
			}).then((result) => {
				if (result.value) {
					// Yes, clear first
					appendStr = "false";
					swal2({
						title: "Are you sure?",
						text: "This will delete all current flights before importing. Continue?",
						type: "warning",
						showCancelButton: true,
						confirmButtonText: "Yes, clear and import"
					}).then((clearResult) => {
						if (clearResult.value) doImport();
					});
				} else if (result.dismiss === 'cancel' || (swal2.DismissReason && result.dismiss === swal2.DismissReason.cancel)) {
					// No, just append
					appendStr = "true";
					doImport();
				}
			});
			
		} catch (e) {
			toast({ title: "Invalid JSON file.", type: "error" });
		}
	};
	reader.readAsText(file);
}

// User Filter
$("#txtUserFilter").on("keyup", function() {
	var value = $(this).val().toLowerCase();
	$("#tblUsers tbody tr").filter(function() {
		var vid = $(this).find("td:eq(0)").text().toLowerCase();
		var name = $(this).find("td:eq(1)").text().toLowerCase();
		var email = $(this).find("td:eq(3)").text().toLowerCase();
		
		$(this).toggle(
			vid.indexOf(value) > -1 || 
			name.indexOf(value) > -1 || 
			email.indexOf(value) > -1
		);
	});
});
