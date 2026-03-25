<?php
require_once "../utils.php";

/* NOTE:
   Keep the same doctor role protection pattern.
*/
$user = require_role("Doctor");

/* NOTE:
   doctor.js sends appointmentId in the POST body when Open is clicked.
*/
$data = json_decode(file_get_contents("php://input"), true);
$appointmentId = (int)($data["appointmentId"] ?? 0);

/* NOTE:
   Stop early if no appointment id was sent.
*/
if ($appointmentId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "appointmentId required"]);
    exit;
}

/* NOTE:
   First make sure this appointment belongs to the logged-in doctor.
   IMPORTANT CHANGE:
   Changed table name to Appointment to match schema.
*/
$stmt = $pdo->prepare("
    SELECT Appointment_ID, Patient_ID, Provider_User_ID
    FROM Appointment
    WHERE Appointment_ID = ?
      AND Provider_User_ID = ?
    LIMIT 1
");
$stmt->execute([$appointmentId, $user["id"]]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

/* NOTE:
   Do not continue if the doctor does not own this appointment.
*/
if (!$appointment) {
    http_response_code(404);
    echo json_encode(["error" => "Appointment not found"]);
    exit;
}

/* NOTE:
   Check whether a visit already exists for this appointment.
   IMPORTANT CHANGE:
   Changed table name to Visit to match schema.
*/
$stmt = $pdo->prepare("
    SELECT Visit_ID
    FROM Visit
    WHERE Appointment_ID = ?
    LIMIT 1
");
$stmt->execute([$appointmentId]);
$existingVisit = $stmt->fetch(PDO::FETCH_ASSOC);

/* NOTE:
   If a visit already exists, return it instead of creating a duplicate.
*/
if ($existingVisit) {
    echo json_encode([
        "visitId" => (int)$existingVisit["Visit_ID"]
    ]);
    exit;
}

/* NOTE:
   Create a new visit row for this appointment.
   IMPORTANT CHANGE:
   Changed table name to Visit to match schema.
*/
$stmt = $pdo->prepare("
    INSERT INTO Visit
    (Appointment_ID, Patient_ID, Provider_User_ID, Created_By_User_ID, Visit_DateTime)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->execute([
    $appointment["Appointment_ID"],
    $appointment["Patient_ID"],
    $appointment["Provider_User_ID"],
    $user["id"]
]);

/* NOTE:
   doctor.js expects JSON in this exact shape:
   { visitId: number }
*/
echo json_encode([
    "visitId" => (int)$pdo->lastInsertId()
]);