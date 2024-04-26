<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "patient_info_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $lastName = $_POST["lastname"];
    $firstName = $_POST["firstname"];
    $middleInitial = $_POST["middlename"];
    $sex = $_POST["sex"];
    $age = $_POST["age"];
    $ageUnit = isset($_POST["ageunit"]) && $_POST["ageunit"] == "months" ? "months" : "years";
    $dobMonth = $_POST["dob_month"];
    $dobDay = $_POST["dob_day"];
    $dobYear = $_POST["dob_year"];
    $race = $_POST["race"];
    $otherRace = isset($_POST["other_race"]) ? $_POST["other_race"] : null;
    $hispanic = $_POST["hispanic"];
    $facilityName = isset($_POST["facility_name"]) ? $_POST["facility_name"] : null;
    $facilityCity = isset($_POST["facility_city"]) ? $_POST["facility_city"] : null;
    $facilityCounty = isset($_POST["facility_county"]) ? $_POST["facility_county"] : null;
    $facilityState = isset($_POST["facility_state"]) ? $_POST["facility_state"] : null;
    $facilityPhone = isset($_POST["facility_phone"]) ? $_POST["facility_phone"] : null;
    $medicalRecordNum = isset($_POST["medicalrecordnum"]) ? $_POST["medicalrecordnum"] : null;
    $addressName = isset($_POST["address_name"]) ? $_POST["address_name"] : null;
    $addressStreet = isset($_POST["address_street"]) ? $_POST["address_street"] : null;
    $addressCity = isset($_POST["address_city"]) ? $_POST["address_city"] : null;
    $addressCounty = isset($_POST["address_county"]) ? $_POST["address_county"] : null;
    $addressState = isset($_POST["address_state"]) ? $_POST["address_state"] : null;
    

    // Construct the date of birth
    $dateOfBirth = $dobYear . "-" . $dobMonth . "-" . $dobDay;

    //Check if the facility name already exists    
    $sql = "SELECT FacilityID FROM Facility WHERE FacilityName = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $facilityName);
    $stmt->execute();
    $stmt->store_result();   
    
    if ($stmt->num_rows > 0) {
        // Facility name already exists, retrieve the existing FacilityID
        $stmt->bind_result($existingFacilityID);
        $stmt->fetch();
    } else {
        // Facility name doesn't exist, set $existingFacilityID to null
        $existingFacilityID = null;
    }
    $stmt->close();

    // Insert patient data into the Patient table
    $sql = "INSERT INTO Patient (MedicalRecordNum, LastName, FirstName, MiddleInitial, Sex, Age, AgeUnit, Race, DateOfBirth, Hispanic)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $medicalRecordNum, $lastName, $firstName, $middleInitial, $sex, $age, $ageUnit, $race, $dateOfBirth, $hispanic);
    $stmt->execute();

    //Get the last inserted MedicalRecordNum
    $lastInsertedMedicalRecordNum = $stmt->insert_id ?: $medicalRecordNum;

    // Insert address data into the Address table
    $sql = "INSERT INTO Address (MedicalRecordNum, FacilityName, Street, City, County, State)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("ssssss", $lastInsertedMedicalRecordNum, $addressName, $addressStreet, $addressCity, $addressCounty, $addressState);
    $stmt->execute();

    // Insert facility data into the Facility table
    $sql = "INSERT INTO Facility (MedicalRecordNum, FacilityName, City, County, State, PhoneNumber)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $lastInsertedMedicalRecordNum, $facilityName, $facilityCity, $facilityCounty, $facilityState, $facilityPhone);
    $stmt->execute();


    /* Insert facility data into the Facility table
    $sql = "INSERT INTO Facility (MedicalRecordNum, FacilityID, FacilityName, City, County, State, PhoneNumber)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($existingFacilityID !== null) {
        // Use the existing FacilityID if the facility name already exists
        $stmt->bind_param("sisssss", $lastInsertedMedicalRecordNum, $existingFacilityID, $facilityName, $facilityCity, $facilityCounty, $facilityState, $facilityPhone);
    } else {
       // Generate a new FacilityID using incrementing ID
        $newFacilityID = $stmt->insert_id; 
        $stmt->bind_param("sssssss", $lastInsertedMedicalRecordNum, $newFacilityID, $facilityName, $facilityCity, $facilityCounty, $facilityState, $facilityPhone);
    }
    //$stmt->execute(); */


    echo "Data saved successfully!";
}

// Close the database connection
$conn->close();
