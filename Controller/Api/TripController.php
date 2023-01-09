<?php
class TripController extends BaseController
{
    
    public function listAction()
    
    {
    
        $strErrorDesc = '';
    
        $requestMethod = $_SERVER["REQUEST_METHOD"];
    
        $arrGetStringParams = $this->getStringParamsG();
    
        if (strtoupper($requestMethod) == 'GET') {
    
            try {
    
                $tripModel = new TripModel();
    
                $intLimit = 10;
    
                if (isset($arrGetStringParams['limit']) && $arrGetStringParams['limit']) {
    
                    $intLimit = $arrGetStringParams['limit'];
    
                }
    
                $arrTrips = $tripModel->getAllFlights($intLimit);
    
                $responseData = json_encode($arrTrips, JSON_PRETTY_PRINT);
    
            } catch (Error $e) {
    
                $strErrorDesc = $e->getMessage().'Something went wrong!';
    
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {

            $strErrorDesc = 'Method not supported';

            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }

        if (!$strErrorDesc) {
            
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {

            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }



    public function flightsAction()
    {

        $strErrorDesc = '';

        $requestMethod = $_SERVER["REQUEST_METHOD"];

        $arrGetStringParams = $this->getStringParamsG();

        // Validate params
        $validateUrl = $this->validateParams($arrGetStringParams);
        // echo $validateUrl;
        if($validateUrl){
        
        
            $strErrorDesc = $validateUrl;
            
            $strErrorHeader = 'HTTP/1.1 400 Bad Request';

            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            
            array('Content-Type: application/json', $strErrorHeader)
            );
        
        }

        // Validate params end

        if (strtoupper($requestMethod) == 'GET') {
            
            try {
            
                $tripModel = new TripModel();
            
                $intLimit = 10;
            
                if (isset($arrGetStringParams['limit']) && $arrGetStringParams['limit']) {
            
                    $intLimit = $arrGetStringParams['limit'];
            
                }
                
                $airport_a = 'YUL'; /// Delete

                if (isset($arrGetStringParams['departure_airport']) && $arrGetStringParams['departure_airport']) {

                    $airport_a = $arrGetStringParams['departure_airport']; 

                }
                
                $airport_b = $arrGetStringParams['arrival_airport'];

                $arrTrips = $tripModel->searchFlights($airport_a, $intLimit); // all the flights from Airport A
                
                $trips = []; // all the trips from A to B for the response
                
                $arrTripsAdd = []; // Auxiliar to store temporaly the flights
                
                $price_total_trip = 0;
                
                $transits = 0;
                
                $direction = 'a_to_b';
                
                array_push($trips, $this->deeperSearch($trips, $arrTrips, $airport_b, $price_total_trip, $arrTripsAdd, $transits, $direction));

                foreach($trips as $key => $value){

                    if(!$value){

                        array_pop($trips);

                    }
                }
                
                if($arrGetStringParams['trip_type'] == 'round-trip'){
                
                    // echo 'ROUND TRIP';
                    $airport_a = $arrGetStringParams['arrival_airport'];   // Invert iarports
                    
                    $airport_b = $arrGetStringParams['departure_airport'];  // invert airposts
                    
                    $arrTrips = $tripModel->searchFlights($airport_a, $intLimit); // all the flights from Airport A
                    
                    $arrTripsAdd = []; // Auxiliar to store temporaly the flights
                    
                    $price_total_trip = 0;
                    
                    $transits = 0;
                    
                    $direction = 'b_to_a';
                    
                    array_push($trips, $this->deeperSearch($trips, $arrTrips, $airport_b, $price_total_trip, $arrTripsAdd, $transits, $direction));
                    
                    foreach($trips as $key => $value){
                    
                        if(!$value){
                    
                            array_pop($trips);
                    
                        }
                    }
                }
                
                if(empty($trips)){
        
                    $strErrorDesc = "No flights found";
                    
                    $strErrorHeader = 'HTTP/1.1 400 Bad Request';
        
                    $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                    
                    array('Content-Type: application/json', $strErrorHeader)
                    );
                
                }else{

                    $responseData = json_encode($trips, JSON_PRETTY_PRINT);
                }

            
            } catch (Error $e) {
            
                $strErrorDesc = $e->getMessage().'Something went wrong!';
            
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            
            }
        } else {
            
            $strErrorDesc = 'Method not supported';
            
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';

        }
        // send output 
        if (!$strErrorDesc) {
            
            $this->sendOutput(
            
                $responseData,
            
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    public function deeperSearch(&$trips, $arrTrips, $airport_b, $price_total_trip, $arrTripsAdd, $transits, $direction)
    {
        $tripModel = new TripModel();

        // echo '------------------------ function deeperSearch ----------------------
        // '; // Delete


        ///////// flights destinations

                foreach($arrTrips as $key => $value){
                    // $price_total_trip = 0;
                    array_push($arrTripsAdd, $value);

                    if($value['arrival_airport'] == $airport_b){

                        $price_total_trip = $price_total_trip + intval($value['price']);

                        array_push($trips, ['direction'=>$direction, 'transits'=>$transits, 'price'=>$price_total_trip, 'flights'=>$arrTripsAdd]);

                            $price_total_trip = 0;

                            reset($arrTripsAdd);

                    }else{

                        $arrTrips_1 = $tripModel->searchFlightsRestricted($value['arrival_airport'], $value['arrival_time'], 10); // all the flights from Airport A

                        if(empty($arrTrips_1)){

                            // echo "No Flight arrives to that airport."; // Delete

                            array_pop($arrTripsAdd);

                        }else{
                            
                            // echo "Flight arrives to another airport."; // Delete

                        }

                        $this->deeperSearch($trips, $arrTrips_1, $airport_b, ($price_total_trip+intval($value['price'])), $arrTripsAdd, $transits+1, $direction);
                    }
                    if($transits == 0){

                        $price_total_trip = 0; // reset price in each 0 transits base, in other case keep it for next flights
                        
                        array_pop($arrTripsAdd);
                    
                    }
                }

                ////// flights destinations end

        // echo '------------------------ function deeperSearch end ----------------------
        // '; // Delete
    }

    public function validateParams($params)
    {
        // echo '------------------------ function ValiteParams----------------------
        // print_r($params); // Delete

        $errorflag = 0;

        $errortxt = '';
        
        $errortxts[1] = 'The params (departure_airport, arrival_airport, departure_date, trip_type) may be written wrong or are missing. ';
        
        $errortxts[2] = 'The return_date is necessary for round trips.';
        
        $errortxts[3] = 'Date format not valid, it must be Y-m-d (ex: 2023-07-13).';
        
        $errortxts[4] = 'Trip_type not selected, value must be round-trip or one way.';
        
        // print_r($errortxts); // Delete
        if ((isset($params['departure_airport']) && $params['departure_airport']) && (isset($params['arrival_airport']) && $params['arrival_airport']) && (isset($params['departure_date']) && $params['departure_date']) && (isset($params['trip_type']) && $params['trip_type'])) {
        
            if(($params['trip_type'] == "round-trip") && (isset($params['return_date']) && $params['return_date'])){
           
                // echo "True return_date validation is round trip."; // delete
                if($this->validateDate($params['return_date'])){
           
                    // echo "return_date is ok."; // Delete
           
                }else{
           
                    // echo "return_date " . $errortxts[3];
                    $errortxt = $errortxts[3];
           
                    $errorflag += 1;
           
                    // echo $errortxts[1];
           
                }
           
            }elseif(($params['trip_type'] == "one-way")){
           
                // echo "True return_date validation is one way."; // delete
           
            }else{
           
                $errorflag += 1;
           
                // echo $errortxts[2];
                $errortxt = $errortxts[4];
            }
        }else{
        
            // echo "false"; // Delete
        
            $errorflag += 1;
        
            // echo $errortxts[1];
            $errortxt = $errortxts[1];
        }

        if($this->validateDate($params['departure_date'])){
            
            // echo "departure_date is ok."; // Delete
        }else{
            
            $errortxt = $errortxts[3];
            
            $errorflag += 1;
            
            // echo $errortxts[1];
        }
        
        // echo '------------------------ function ValiteParams end ----------------------
        // '; // Delete
        
        if($errorflag == 0){
        
            return false;
        }else{
        
            return $errortxt;
        }
        
    }

    public function validateDate($date, $format = 'Y-m-d'){
    
        $d = DateTime::createFromFormat($format, $date);
    
        return $d && $d->format($format) === $date;
    
    }

}