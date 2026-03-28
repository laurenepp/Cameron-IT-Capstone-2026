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
  echo json_encode(["error" => "from/to required (YYYY-MM-DD)"]);
  exit;
}

/* NOTE:
   Expand the selected dates to cover the full day.
*/
$fromDT = $from . " 00:00:00";
$toDT   = $to   . " 23:59:59";

/* NOTE:
   Keep the main query layout and response shape so schedule loading
   still works the same way as main.
*/
$stmt = $pdo->prepare("
  SELECT
    a.Appointment_ID,
    a.Scheduled_Start,
    a.Scheduled_End,
    a.Status,
    p.Patient_ID,
    p.First_Name AS Patient_First,
    p.Last_Name AS Patient_Last
  FROM Appointment a
  JOIN Patient p ON a.Patient_ID = p.Patient_ID
  WHERE a.Provider_User_ID = ?
    AND a.Scheduled_Start BETWEEN ? AND ?
  ORDER BY a.Scheduled_Start ASC
");

$stmt->execute([$user["id"], $fromDT, $toDT]);

/* NOTE:
   doctor.js expects JSON in this exact shape:
   { appointments: [...] }
*/
echo json_encode([
  "appointments" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);