<?php
$servername = "localhost";
$username = "admin";
$password = $_POST["password"];
$dbname = "raticals";

$conn = new mysqli($servername, $username, $password, $dbname);

$failure = false;

if ($conn->connect_error) {
    error_log( "Connection failed: " . $conn->connect_error );
    $failure = true;
} else if (!$failure && !$conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE)) {
    error_log( "begin transaction failure:" . $conn->error );
    $failure = true;
} else {
    $create_event = $conn->prepare(
        "INSERT INTO event (title, street, city, state, zip, datetime) VALUES (?, ?, ?, ?, ?, ?)");
    $create_event->bind_param("ssssss", $title, $street, $city, $state, $zip, $datetime);

    $title = $_POST["title"];
    $street = $_POST["street"];
    $city = $_POST["city"];
    $state = $_POST["state"];
    $zip = $_POST["zip"];

    $datetime_input = $_POST["date"] . " " . $_POST["time"];
    $datetime_obj = DateTime::createFromFormat( "d/m/Y g:ia", $datetime_input );
    if( !$datetime_obj ) {
        error_log( "Failed to construct DateTime from '" . $datetime_input . "':" . $conn->error );
        $failure = true;
    } else {
        $datetime = $datetime_obj->format("y-m-d G:i:s");
        if (!$create_event->execute()) {
            error_log( "Event Creation Failed:" . $conn->error );
            $failure = true;
        } else {
            $created_event_id = $conn->insert_id;
            
            $create_trial = $conn->prepare(
                "INSERT INTO trial (class, time, event_id) VALUES (?, ?, ?)");
            $create_trial->bind_param("ssi", $class, $time, $event_id);
            $event_id = $created_event_id;
            
            $result = $conn->query("SELECT name FROM trial_class");
            
            if( $result->num_rows == 0 ) {
                error_log( "Failed to retrieve trial classes." );
                $failure = true;
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    $class = $row["name"];
                    $trial_count = intval($_POST[$class . "_count"]);
                    for($i = 0; $i < $trial_count; $i++) {
                        $trial_input_name = $class . "_count" . $i;
                        if( !array_key_exists( $trial_input_name, $_POST ) ) {
                            error_log( "Missing name trial time for class '" . $class . "'." .
                                       " Variable '" . $trial_input_name . "' not found." );
                            $failure = true;
                            break;
                        } else {
                            $time = $_POST[$trial_input_name];
                            if(!$create_trial->execute()) {
                                error_log( "Failed to create trial:" . $class . " " . $time . ":" . $conn->error );
                                $failure = true;
                                break;
                            }
                        }
                    }
                }
            }
            $create_trial->close();
        }
        $create_event->close();
    }
}

if( $failure ) {
    echo "Failed to create new event.";
    $conn->rollback();
} else {
    echo "Event created successfully.";
    $conn->commit();
}

$conn->close();

?> 
