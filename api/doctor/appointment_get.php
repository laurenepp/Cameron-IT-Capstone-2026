<?php
require_once "../utils.php";

/* NOTE:
   Keep the same doctor role protection pattern.
*/
$user = require_role("Doctor");

/* NOTE:
   doctor.js sends appointmentId in the query string when Open is clicked.
*/
$appointmentId = (int)($_GET["appointmentId"] ?? 0);

/* NOTE:
   Stop early if no appointment id was sent.
*/
if ($appointmentId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "appointmentId required"]);
    exit;
}

/* NOTE:
   IMPORTANT CHANGE:
   Changed table names to match your schema.
   OLD style used plural table names.
   NEW style uses Appointment and Patient.
*/
$sql = "
    SELECT
        a.Appointment_ID,
        a.Patient_ID,
        a.Provider_User_ID,
        a.Scheduled_Start,
        a.Scheduled_End,
        a.Status,
        p.First_Name AS Patient_First,
        p.Last_Name  AS Patient_Last
    FROM Appointment a
    INNER JOIN Patient p
        ON a.Patient_ID = p.Patient_ID
    WHERE a.Appointment_ID = ?
      AND a.Provider_User_ID = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

/* NOTE:
   Only allow the logged-in doctor to open appointments assigned to them.
*/
$stmt->execute([
    $appointmentId,
    $user["id"]
]);

$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

/* NOTE:
   Return a clear 404 if no matching appointment was found.
*/
if (!$appointment) {
    http_response_code(404);
    echo json_encode(["error" => "Appointment not found"]);
    exit;
}

/* NOTE:
   doctor.js expects JSON in this exact shape:
   { appointment: {...} }
*/
echo json_encode([
    "appointment" => $appointment
]);