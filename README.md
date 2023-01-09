# api-trip-builder
Requirements:
- PHP 8
- MySQL
- Apache server
Suggestion: Use XAMPP Stack Package to run a local web server and it can be installed in Windows, Linux or Mac 

Installation instructions

1. Build the database with the SQL provided.
2. Unzip the file attached in the root folder for your local web server or webserver. It's usually named as "www" or "htdocs".
3. Edit the inc/config.php with information for you previously set up the local database.

Quick test

If everything was installed correctly you can try to access to the next URL from the browser: http://localhost/flighth_assignment/index.php/trip/list?limit=2 in that way you will see 2 flights stored in the database. In other case, verify the config.php file information. 

About this API

This API was developed as an example to GET information about different flight options based on a list of flights previously stored in a database.
The request is made through the GET method and the information is sent in JSON text format.

Methods

Method "list"
This method shows all the flights available in the database.
Parameter "limit". it allows to restrict the results to show in the request, by (not adding the parameter it is 10), in other case you can set any number.
Examples:
-   http://localhost/flighth_assignment/index.php/trip/list (it will show 10 results)
-   http://localhost/flighth_assignment/index.php/trip/list?limit=2 (it will show 2 results)
-   http://localhost/flighth_assignment/index.php/trip/list?limit=23 (it will show 23 results)

Method "flight"
This Method allow to test the search the flight option from airport A to airport B (one way trip) and from airport B to airport A (round trip).
It is necessary to add the next parameters:
- departure_airport
- arrival_airport
- departure_date
- return_date (option on one-way trips)
- trip_type


Testing data:
Flights are available departing from the next airports codes:
- YUL
- YVR
- LAX
Dates must be set as Y-m-d, example:
- 2023-01-23
- 2023-02-15
Trip type must be set as:
- on-way
- round-trip

Testing examples:
- Round trip from YUL airport to LAX airport on January 09th, 2023 and returning on January 11th, 2023.
http://localhost/flighth_assignment/index.php/trip/flights?departure_airport=YUL&arrival_airport=LAX&departure_date=2023-01-09&return_date=2023-01-11&trip_type=round-trip
- One way trip from YUL airport to YVR airport on January 09th, 2023 and returning on January 11th, 2023.
http://localhost/flighth_assignment/index.php/trip/flights?departure_airport=YUL&arrival_airport=YVR&departure_date=2023-01-09&return_date=2023-01-11&trip_type=one-way
