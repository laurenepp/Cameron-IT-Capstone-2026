<?php
require_once "../utils.php";

/* NOTE:
   Keep the doctor role check the same pattern used by the rest of the API.
*/
$user = require_role("Doctor");

/* NOTE:
   These dates come from doctor.js when you click Load Schedule.
*/
$from = $_GET["from"] ?? "";
$to   = $_GET["to"] ?? "";

/* NOTE:
   Stop early if the page did not send both dates.
*/
if ($from === "" || $to === "") {
    http_response_code(400);
    echo json_encode(["error" => "from/to required"]);
    exit;
}

/* NOTE:
   Expand the selected dates to cover the full day.
*/
$fromDT = $from . " 00:00:00";
$toDT   = $to   . " 23:59:59";

/* NOTE:
   IMPORTANT CHANGE:
   Changed table names to match your schema.
   OLD style used plural names like Appointments / Patients.
   NEW style uses Appointment / Patient.
*/
$sql = "
    SELECT
        a.Appointment_ID,
        a.Scheduled_Start,
        a.Scheduled_End,
        a.Status,
        p.Patient_ID,
        p.First_Name AS Patient_First,
        p.Last_Name  AS Patient_Last
    FROM Appointment a
    INNER JOIN Patient p
        ON a.Patient_ID = p.Patient_ID
    WHERE a.Provider_User_ID = ?
      AND a.Scheduled_Start BETWEEN ? AND ?
    ORDER BY a.Scheduled_Start ASC
";

$stmt = $pdo->prepare($sql);

/* NOTE:
   Doctor user id comes from the logged in doctor session.
*/
$stmt->execute([
    $user["id"],
    $fromDT,
    $toDT
]);

/* NOTE:
   doctor.js expects JSON in this exact shape:
   { appointments: [...] }
*/
echo json_encode([
    "appointments" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);