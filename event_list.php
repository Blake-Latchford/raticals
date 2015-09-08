<?php
$servername = "localhost";
$username = "web";
$password = "tMQtxs6iiHaIy61M";
$dbname = "raticals";

$conn = new mysqli($servername, $username, $password, $dbname);

$failure = false;

if ($conn->connect_error) {
    error_log( "Connection failed: " . $conn->connect_error );
    $failure = true;
} else {
    $result = $conn->query( "SELECT id, title, street, city, state, zip, datetime FROM event" );
    if($result->num_rows == 0) {
        error_log( "Failed to query event table:" . $conn->error );
        $failure = true;
    } else {
        $event_string = "<div class=\"event\">";
        while( $row = $result->fetch_assoc() ) {
            $datetime = DateTime::createFromFormat( "Y-m-d H:i:s", $row["datetime"] );
            if( !$datetime ) {
                error_log( "Failed to convert datetime '" . $row["datetime"] );
                $failure = true;
            } else {
                $event_string .= "<h1>" . $row["title"] . "</h1>" .
                    "<div>" . $datetime->format( "F jS" ) . "</div>" .
                    "<form action=\"register.php\">" .
                    "<input type=hidden name=\"event_id\" value=\"" . $row["id"] . "\" \\>" .
                    "<input type=\"submit\" value=\"Register\" \\>" .
                    "</form>";
            }
        }
        $event_string .= "</div>";
        echo $event_string;
    }
}

$conn->close();

?> 
