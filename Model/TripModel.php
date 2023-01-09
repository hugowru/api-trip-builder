<?php
    require_once PROJECT_ROOT_PATH . "/Model/Database.php";
    class TripModel extends Database
    {
        public function getAllFlights($limit)
        {
        
            return $this->select("SELECT * FROM flights ORDER BY id_flight ASC LIMIT ?", ["i", $limit]);
        }
        
        public function searchFlights($departure_airport, $limit)
        {
        
            return $this->select("SELECT * FROM flights WHERE departure_airport = '$departure_airport' ORDER BY id_flight ASC LIMIT ?", ["i", $limit]);
        }
        
        public function searchFlightsRestricted($departure_airport, $departure_time, $limit)
        {
        
            return $this->select("SELECT * FROM flights WHERE departure_airport = '$departure_airport' AND departure_time > '$departure_time' ORDER BY id_flight ASC LIMIT ?", ["i", $limit]);
        }
    }