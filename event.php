<?php
$servername = "localhost";
$username = "admin";
$password = $_POST["password"];
$dbname = "raticals";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$create_event = $conn->prepare(
    "INSERT INTO event (title, street, city, state, zip, datetime) VALUES (?, ?, ?, ?, ?, ?)");
$create_event->bind_param("ssssss", $title, $street, $city, $state, $zip, $datetime);

$title = $_POST["title"];
$street = $_POST["street"];
$city = $_POST["city"];
$state = $_POST["state"];
$zip = $_POST["zip"];
$datetime = $_POST["date"];


if (!$create_event->execute()) {
    error_log( "Event Creation Failed." );
} else {
    $created_event_id = $conn->insert_id;
    
    $create_trial = $conn->prepare(
        "INSERT INTO trial (class, time, event_id) VALUES (?, ?, ?)");
    $create_trial->bind_param("ssi", $class, $time, $event_id);
    $event_id = $created_event_id;
    
    $result = $conn->query("SELECT name FROM trial_class");
    
    if( $result->num_rows == 0 ) {
        error_log( "Failed to retrieve trial classes." );
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $class = $row["name"];
            $trial_count = intval($_POST[$class . "_count"]);
            for($i = 0; $i < $trial_count; $i++) {
                $trial_input_name = $class . "_count" . $i;
                if( !array_key_exists( $trial_input_name, $_POST ) ) {
                    echo( $trial_input_name . "<br>");
                    error_log( "Missing name trial time for class '" . $class . "'." .
                               " Variable '" . $trial_input_name . "' not found." );
                    break;
                } else {
                    $time = $_POST[$trial_input_name];
                    if(!$create_trial->execute()) {
                        error_log( "Failed to create trial:" . $class . " " . $time );
                        break;
                    }
                }
            }
        }
    }
    $create_trial->close();
}

$create_event->close();

$conn->close();
?> 