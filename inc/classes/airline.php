<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

/**
 * Represents one airline
 */
class Airline
{
	private static $airlineList = null;
	private static $logoCache = [];

	private static function LoadAirlineList()
	{
		global $dbNav;
		self::$airlineList = array();
		if ($query = $dbNav->GetSQL()->query("SELECT * FROM airlines")) {
			while ($row = $query->fetch_assoc()) {
				$airline = new Airline($row);
				self::$airlineList[$airline->icao] = $airline;
			}
		}
	}

	/**
	 * Returns an airline found in the database based on its ICAO code, otherwise returns null
	 * @param string $icao
	 * @return Airline
	 */
	public static function Find($icao)
	{
		if (self::$airlineList === null) {
			self::LoadAirlineList();
		}
		if (isset(self::$airlineList[$icao])) {
			return self::$airlineList[$icao];
		}
		return null;
	}
	
	public $icao, $name, $callsign;
	public function __construct($row)
	{
		$this->icao = $row["icao"];
		$this->name = $row["name"];
		$this->callsign = $row["callsign"];
	}
	
	/**
	 * Returns the logo (HTML img) of the airline if file exists.
	 * @return string HTML
	 */
	public function getLogo()
	{
		$imgUrl = "img/airlines/" . $this->icao . ".gif";
		if (!isset(self::$logoCache[$this->icao])) {
			self::$logoCache[$this->icao] = file_exists($imgUrl);
		}
		return self::$logoCache[$this->icao] ? '<img src="' . $imgUrl . '?version=7" alt="' . $this->icao . '" class="img-fluid"> ' : "";
	}
	
	/**
	 * Returns the logo (HTML img) in smaller version of the airline if file exists.
	 * @return string HTML
	 */
	public function getLogoSmall()
	{
		$imgUrl = "img/airlines/" . $this->icao . ".gif";
		if (!isset(self::$logoCache[$this->icao])) {
			self::$logoCache[$this->icao] = file_exists($imgUrl);
		}
		return self::$logoCache[$this->icao] ? '<img src="' . $imgUrl . '" alt="' . $this->icao . '" class="img-fluid" style="width: 40%"> ' : "";
	}
	
	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * Don't think it is currently used...
	 * @return string JSON
	 */
	public function ToJson()
	{
		$apt = (array)$this;
		
		// adding data from functions to the feed
		$data = [
			"logo" => $this->getLogo(),
			"logoSmall" => $this->getLogoSmall(),
		];
		
		return json_encode(array_merge($apt, $data));
	}
}
  