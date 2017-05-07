<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="form.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="register.js" type="text/javascript"></script>
</head>
    
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
}

?>
    
<body>
<?php

$sanitize = [
    "event_id"
];
function requestToHtml( $key, $sanitize, $validate ) {
    if( !isset( $_REQUEST[$key] ) ) {
        return "";
    }
    $value = $_REQUEST[$key];
    if( $sanitize ) {
        $value = filter
    }
    return htmlspecialchars( $_REQUEST[$key] );
}

if(!$failure) {
    $get_event = $conn->prepare(
        "SELECT title, street, city, state, zip, datetime FROM event WHERE id=?;");
    $get_event->bind_param("i", $id);
    $id = $_REQUEST["event_id"];
    if(!$get_event->execute()) {
        error_log( "Failed to retrieve event with id '" . $id . "':" . $get_event->error );
        $failure = true;
    } else {
        $get_event->bind_result($title, $street, $city, $state, $zip, $datetime);
        if( !$get_event->fetch() ) {
            error_log( "Failed to retrieve event '$id':" . $get_event->error() );
            $failure = true;
        } else {
            echo "<h1>$title</h1>";
            $datetime = DateTime::createFromFormat( "Y-m-d H:i:s", $datetime );
            if($datetime) {
                echo "<div class=\"date\">" . $datetime->format("F jS") . "</div>";
            }
            echo "<a href=\"https://maps.google.com?daddr=" .
                "$street+$city+$state+$zip\">$street $city, $state</a>";
        }
    }
    $get_event->close();
}
?>
    <form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
        <fieldset>
            <input type="hidden" name="event_id"
                   value="<?php echo htmlspecialchars($_REQUEST["event_id"]); ?>" />
            <h2>Trials</h2>
<?php
$total_trials = 0;
$trial_classes = $conn->query("SELECT id,name FROM trial_class ORDER BY id ASC;");
if( $trial_classes->num_rows == 0 ) {
    error_log( "Failed to retrieve trial classes." );
    $failure = true;
} else {
    $trials = $conn->prepare(
        "SELECT id,time FROM trial WHERE event_id=? AND trial_class_id=?;");
    if( !$trials ) {
        error_log( "Failed to prepare trials query:$conn->error" );
        $failure = true;
    } else if( !$trials->bind_param( "ii", $_REQUEST["event_id"], $trial_class_id ) ) {
        error_log( "Failed to bind paremeters:$trials->error" );
        $failure = true;
    }
    while (!$failure && $trial_class = mysqli_fetch_assoc($trial_classes)) {
        $trial_class_id = $trial_class["id"];
        if( !$trials->execute() ) {
            error_log( "Failed to retrieve trials:" . $trials->error );
            $failure = true;
        } else if( !$trials->bind_result( $trial_id, $time ) ) {
            error_log( "Failed to bind results for trial retrieval:$trials->error" );
            $failure = true;
        } else if( !$trials->store_result() ){
            error_log( "Failed to store results for trial retrieval:$trials->error" );
            $failure = true;
        } else if( $trials->num_rows == 0 ) {
            //No trials in this trial class.
        } else {
            echo "<h3>" . $trial_class["name"] . "<h3>";
            while( $trials->fetch() ) {
                echo "<input type=\"checkbox\" id=\"trial_$trial_id\" " .
                    "name=\"trial\" value=\"$trial_id\" >$time</input><br>";
                $total_trials += 1;
            }
        }
    }
    $trials->close();

    if( $total_trials == 0 ) {
        $failure = true;
    }
}
?>
            <h2>Dog Information</h2>
            <h3>Barn Hunt Number</h3>
            <a id="BH">BH-</a>
                <input type="number" name="BarnHunt_Number"
                       <?php echo requestToHtml("BarnHunt_Number"); ?> />
            </input>
            <h3>Dog's Call Name</h3>
            <input type="text" name="call_name"
                   <?php echo requestToHtml("call_name"); ?> />
            <h3>Dog's Full Name</h3>
            <input type="text" name="dog_full_name"
                   <?php echo requestToHtml("dog_full_name"); ?> />
            <h3>Breed</h3>
            <input type="text" name="breed"
                   <?php echo requestToHtml("breed"); ?> />
            <h3>Height in Inches</h3>
            <input type="text" name="height"
                   <?php echo requestToHtml("height"); ?> />
            <input type="hidden" name="height_class"
                   <?php echo requestToHtml("height_class"); ?>>
            <h3>Dog's Date of Birth</h3>
            <input type="date" name="birth_date" class="datepicker"
                   <?php echo requestToHtml("birth_date"); ?> />
            <h3>Dog's Sex</h3>
            <input type="radio" name="sex" value="male" id="sex_male"
                   <?php if( isset($_REQUEST["sex"]) and $_REQUEST["sex"] == "male") echo "checked"; ?> />
                <label for="sex_male">Male</label><br>
            <input type="radio" name="sex" value="female" id="sex_female"
                   <?php if( isset($_REQUEST["sex"]) and $_REQUEST["sex"] == "female") echo "checked"; ?> />
                <label for="sex_female">Female</label><br>
            <?php
            $bitch_in_season = requestToHtml("bitch_in_season");
            if( isset($_REQUEST["sex"]) and $_REQUEST["sex"] == "male") {
                $bitch_in_season = "hidden";
            } else if( isset( $_REQUEST["bitch_in_season"] ) and true == $_REQUEST["bitch_in_season"]){
                $bitch_in_season = "checked";
            } else {
                $bitch_in_season = "";
            }
            ?>
            <input type="checkbox" name="bitch_in_season" id="bitch_in_season"
                   <?php echo $bitch_in_season; ?> />
                <label for="bitch_in_season" <?php echo $bitch_in_season; ?>>
                    Bitch in Season</label>
            <h2>Owner Information</h2>
            <h3>Owner's Full Name</h3>
            <input type="text" name="owner_name"
                   value="<?php echo requestToHtml("owner_name"); ?>" />
            <h3>Email</h3>
            <input type="email" name="email"
                   value="<?php echo requestToHtml("email"); ?>" />
        </fieldset>
        <input type="submit" value="Submit" />
    </form>
</body>
</html>

<?php

$conn->close();
?>