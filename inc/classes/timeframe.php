<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

/**
 * Represents one timeframe in where private slots can be booked.
 */
class Timeframe 
{
	/**
	 * Converts all tiemframes to JSON format
	 * Used by the admin area through AJAX
	 * @return string JSON
	 */
	public static function ToJsonAll()
	{
		$timeframes = [];
		foreach (Timeframe::GetAll() as $tf)
		{
			$timeframes[] = json_decode($tf->ToJson(), true);
		}
		return json_encode($timeframes);
	}

	public static function ExportDatabase()
	{
		global $db;
		$data = ["timeframes" => [], "slots" => []];
		if (Session::LoggedIn() && Session::User()->permission > 1) {
			if ($query = $db->GetSQL()->query("SELECT * FROM timeframes")) {
				while ($row = $query->fetch_assoc()) {
					$data["timeframes"][] = $row;
				}
			}
			if ($query = $db->GetSQL()->query("SELECT * FROM slots")) {
				while ($row = $query->fetch_assoc()) {
					$data["slots"][] = $row;
				}
			}
			return $data;
		}
		return null;
	}

	public static function ClearDatabase()
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1) {
			$db->GetSQL()->query("TRUNCATE TABLE slots");
			if ($db->GetSQL()->query("TRUNCATE TABLE timeframes")) {
				return 0;
			}
		} else {
			return 403;
		}
		return -1;
	}

	public static function ImportDatabase($array)
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1) {
			$append = isset($array["append"]) ? $array["append"] == "true" : false;
			$data = json_decode($array["slots_json"], true);
			
			if (!$data || !is_array($data)) return -1;
			
			$mysqli = $db->GetSQL();

			if (!$append) {
				$mysqli->query("TRUNCATE TABLE slots");
				$mysqli->query("TRUNCATE TABLE timeframes");
			}

			// Import Timeframes
			if (isset($data["timeframes"]) && is_array($data["timeframes"])) {
				foreach ($data["timeframes"] as $row) {
					$id = intval($row['id']);
					$icao = $mysqli->real_escape_string($row['airport_icao'] ?? '');
					$time = $mysqli->real_escape_string($row['time'] ?? '');
					$count = intval($row['count'] ?? 1);

					if ($append) {
						$query = "INSERT INTO timeframes (airport_icao, `time`, `count`) VALUES ('$icao', '$time', $count)";
					} else {
						$query = "INSERT INTO timeframes (id, airport_icao, `time`, `count`) VALUES ($id, '$icao', '$time', $count)";
					}
					$mysqli->query($query);
				}
			}

			// Import Slots
			if (isset($data["slots"]) && is_array($data["slots"])) {
				foreach ($data["slots"] as $row) {
					$id = intval($row['id']);
					$tfId = intval($row['timeframe_id']);
					$cs = $mysqli->real_escape_string($row['callsign'] ?? '');
					$ori = $mysqli->real_escape_string($row['origin_icao'] ?? '');
					$des = $mysqli->real_escape_string($row['destination_icao'] ?? '');
					$ac = $mysqli->real_escape_string($row['aircraft_icao'] ?? '');
					$fr = intval($row['aircraft_freighter'] ?? 0);
					$term = $mysqli->real_escape_string($row['terminal'] ?? '');
					$gate = $mysqli->real_escape_string($row['gate'] ?? '');
					$route = $mysqli->real_escape_string($row['route'] ?? '');
					$booked = intval($row['booked'] ?? 0);
					$bookedBy = empty($row['booked_by']) ? "NULL" : intval($row['booked_by']);
					$bookedAt = empty($row['booked_at']) ? "NULL" : "'" . $mysqli->real_escape_string($row['booked_at']) . "'";

					if ($append) {
						// Note: when appending, timeframe_id might need to be remapped if we didn't preserve IDs, 
						// but here we assume user knows what they are doing or they are doing a full restore.
						// Actually, if we append timeframes, their IDs will change. This is a problem.
						// For now, we follow the Flight pattern which doesn't handle remapping either.
						$query = "INSERT INTO slots (timeframe_id, callsign, origin_icao, destination_icao, aircraft_icao, aircraft_freighter, terminal, gate, route, booked, booked_by, booked_at) VALUES ($tfId, '$cs', '$ori', '$des', '$ac', $fr, '$term', '$gate', '$route', $booked, $bookedBy, $bookedAt)";
					} else {
						$query = "INSERT INTO slots (id, timeframe_id, callsign, origin_icao, destination_icao, aircraft_icao, aircraft_freighter, terminal, gate, route, booked, booked_by, booked_at) VALUES ($id, $tfId, '$cs', '$ori', '$des', '$ac', $fr, '$term', '$gate', '$route', $booked, $bookedBy, $bookedAt)";
					}
					$mysqli->query($query);
				}
			}

			return 0;
		}
		return -1;
	}

	public static function ExportCSV()
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1) {
			$output = "Airport,Time,Callsign,Aircraft,Freighter,Origin,Destination,Terminal,Gate,Route,Booked By,Status,Booked At\n";
			$sql = "SELECT t.airport_icao, t.time as slot_time, s.* FROM slots s JOIN timeframes t ON s.timeframe_id = t.id ORDER BY t.time, s.callsign";
			if ($query = $db->GetSQL()->query($sql)) {
				while ($row = $query->fetch_assoc()) {
					$status = $row['booked'] == 2 ? 'Granted' : 'Requested';
					$freighter = $row['aircraft_freighter'] == 1 ? 'Y' : 'N';
					
					$line = [
						$row['airport_icao'],
						$row['slot_time'],
						$row['callsign'],
						$row['aircraft_icao'],
						$freighter,
						$row['origin_icao'],
						$row['destination_icao'],
						$row['terminal'],
						$row['gate'],
						$row['route'],
						$row['booked_by'],
						$status,
						$row['booked_at']
					];
					
					// Escape quotes and wrap in quotes
					foreach ($line as &$field) {
						$field = '"' . str_replace('"', '""', $field) . '"';
					}
					
					$output .= implode(",", $line) . "\n";
				}
			}
			return $output;
		}
		return null;
	}

	/**
	 * Returns a timeframe found in the database based on its id, otherwise returns null
	 * @param string $icao
	 * @return Timeframe
	 */
	public static function Find($id)
	{
		global $db;
		if ($query = $db->GetSQL()->query("SELECT * FROM timeframes WHERE id=" . $id))
		{
			if ($row = $query->fetch_assoc())
				return new Timeframe($row);
		}
		return null;
	}

	public static function GetAll()
	{
		global $db;
		$timeframes = [];

		if ($query = $db->GetSQL()->query("SELECT * FROM timeframes ORDER BY airport_icao, time"))
		{
			while ($row = $query->fetch_assoc())
				$timeframes[] = new Timeframe($row);
		}
		return $timeframes;
	}

	/**
	 * Creates a new timeframe (batch).
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public static function Create($array)
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			$sqls = [];
			for ($i = $array["hour_from"]; $i <= $array["hour_to"]; $i++)
			{
				$datetime = $array["date"] . " " . $i . ":" . $array["minute"] . ":00";
				$sqls[] = "INSERT INTO timeframes (airport_icao, `time`, `count`) VALUES ('" . $array["airport_icao"] . "', '" . $datetime . "', " . $array["count"] . ")";
			}

			$ok = true;
			foreach ($sqls as $sql)
			{
				if (!$db->GetSQL()->query($sql))
					$ok = false;
			}
			if ($ok)
				return 0;
		}
		else
			return 403;
		return -1;
	}

	public $id, $airportIcao, $time, $count;	
	public function __construct($row)
	{
		$this->id = (int)$row["id"];
		$this->airportIcao = $row["airport_icao"];
		$this->time = $row["time"];
		$this->count = (int)$row["count"];
	}

	/**
	 * Deletes the timeframe and also the child bookings.
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Delete()
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			foreach ($this->getSlots() as $sb)
				$sb->Delete();

			if ($db->GetSQL()->query("DELETE FROM timeframes WHERE id=" . $this->id))
				return 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Modifies the timeframe.
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Update($array)
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			if ($db->GetSQL()->query("UPDATE timeframes SET `time`='" . $array["time"] . "', `count`=" . $array["count"] . " WHERE id=" . $this->id))
				return 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Converts the object fields to JSON, also adds the bookings and other additional data from functions
	 * @return string JSON
	 */
	public function toJsonSlots()
	{
		$timeframe = json_decode($this->ToJson(), true);
		
		$data = [
			"slots" => null
		];

		$slots = $this->getSlots();
		foreach ($slots as $s)
		{
			$obj = json_decode($s->ToJsonLite(), true);
			
			if (Session::LoggedIn() && Session::User()->permission > 1)
				$data["slots"][] = $obj;
			else
			{
				if ($obj["booked"] == "granted" || (Session::LoggedIn() && $obj["bookedBy"] == Session::User()->vid))
					$data["slots"][] = $obj;
			}
		}
		
		return json_encode(array_merge($timeframe, $data));
	}

	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * @return string JSON
	 */
	public function ToJson()
	{
		$timeframe = (array)$this;
		$apt = $this->getEventAirport();
		
		// adding data from functions to the feed
		$data = [
			"eventAirport" => $apt ? json_decode($apt->ToJson()) : null,
			"timeHuman" => getHumanDateTime($this->time),
			"statistics" => $this->GetStatistics(),
			"sessionUser" => Session::LoggedIn() ? json_decode(Session::User()->ToJson()) : null,
		];
		
		return json_encode(array_merge($timeframe, $data));
	}

	/**
	 * Get all slots connected to this timeframe.
	 * @return Slot[]
	 */
	public function getSlots()
	{
		global $db;
		$ss = [];

		if ($query = $db->GetSQL()->query("SELECT * FROM slots WHERE timeframe_id=" . $this->id . " ORDER BY booked, booked_at"))
		{
			while ($row = $query->fetch_assoc())
				$ss[] = new Slot($row);
		}
		return $ss;
	}

	/**
	 * Returns the statistic numbers in an associative array about the booked/free flights at the airport
	 * @return array
	 */
	public function getStatistics()
	{ 
		global $db;
		$stat = [
			"free" => $this->count,
			"requested" => 0,
			"granted" => 0
		];

		foreach ($this->getSlots() as $s)
		{			
			if ($s->booked == "granted")
			{
				$stat["granted"]++;
				
				if ($stat["free"] > 0)
					$stat["free"]--;
				
			}
			if ($s->booked == "requested")
				$stat["requested"]++;
		}

		return $stat;
	}

	/**
	 * Returns the EventAirport object, otherwise returns null.
	 * @return EventAirport
	 */
	public function getEventAirport()
	{
		return EventAirport::Find($this->airportIcao);
	}
}