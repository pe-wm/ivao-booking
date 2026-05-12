<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

/**
 * Represents one airport which can be origin, destination or part of the event
 */
class Airport
{
	 private static $airportList = null;
	//Vamos a cargar toda la tabla de aeropuertos en una lista y así evitamos tantos accesos a BBDD
	private static function LoadAirportList()
    {
        global $dbNav;

        self::$airportList = array();

        if ($query = $dbNav->GetSQL()->query("SELECT * FROM airports")) {
            while ($row = $query->fetch_assoc()) {
                $airport = new Airport($row);
                self::$airportList[$airport->icao] = $airport;
            }
        }
    }
	/**
	 * Returns an airport found in the database based on its ICAO code, otherwise returns null
	 * @param string $icao
	 * @return Airport
	 */
	public static function Find($icao)
    {
        if (self::$airportList === null) {
            self::LoadAirportList();
        }

        if (isset(self::$airportList[$icao])) {
            return self::$airportList[$icao];
        }

        return null;
    }
	
	public $icao, $iata, $country, $latitude, $longitude, $name, $elevation, $type;
	public function __construct($row)
	{
		$this->icao = $row["icao"];
		$this->iata = $row["iata"];
		$this->country = $row["country"];
		$this->latitude = (float)$row["latitude"];
		$this->longitude = (float)$row["longitude"];
		$this->name = $row["name"];
		$this->elevation = (int)$row["elevation"];
		$this->type = $row["type"];
		
		$this->name = str_replace("International Airport", "", $this->name);
		$this->name = str_replace("Airport", "", $this->name);
		$this->name = str_replace("Airfield", "", $this->name);
		$this->name = str_replace("Air Base", "", $this->name);
		$this->name = trim($this->name);
	}
	
	private static $flagCache = [];

	/**
	 * Returns the country flag PNG if exists.
	 * @param int $size = 32
	 * @return string HTML
	 */
	public function getCountryFlag($size = 32)
	{
		$imgUrl = "img/flags/$size/" . $this->country . ".png";
		$cacheKey = $size . "_" . $this->country;

		if (!isset(self::$flagCache[$cacheKey])) {
			self::$flagCache[$cacheKey] = file_exists($imgUrl);
		}

		if (!self::$flagCache[$cacheKey])
			$imgUrl = "img/flags/$size/_unknown.png";
			
		return '<img src="' . $imgUrl . '" alt="' . $this->country . '" data-toggle="tooltip" title="Country: ' . $this->country . '" class="img-fluid"> ';
	}

	/**
	 * Returns the METAR of the airport.
	 * Not used
	 * @return string METAR
	 */
	public function getMetar()
	{
		global $config;
		return file_get_contents($config["wx_url"] . $this->icao . "&sep=true");
	}

	/**
	 * Returns the TAF of the airport.
	 * Not used
	 * @return string TAF
	 */
	public function getTaf()
	{
		global $config;
		return file_get_contents($config["wx_url"] . $this->icao . "&taf=true");
	}
	
	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * METAR and TAF fields are not used because of the high performance load!
	 * @return string JSON
	 */
	public function ToJson()
	{
		$apt = (array)$this;
		
		// adding data from functions to the feed
		$data = [
			"countryFlag24" => $this->getCountryFlag(24),
			"countryFlag32" => $this->getCountryFlag(32),
			"countryFlag48" => $this->getCountryFlag(48),
			/*"metar" => $this->getMetar(),
			"taf" => $this->getTaf(),*/
		];
		
		return json_encode(array_merge($apt, $data));
	}
}
 