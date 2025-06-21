<?php
$allowed_origins = [
    "https://ivhubnew.onrender.com",
    "https://ivhub.com",
    "https://www.ivhub.com"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$to = "hello@ivhub.com,riti@ivhub.com,desk@ivhub.com";
$subject = "Laser Hair Removal Consent Form Submission | ".$_POST['fullName'];

// Build the HTML body
$body = "<h2>Patient Information</h2>";
$body .= "<p><strong>Full Name:</strong> " . $_POST['fullName'] . "</p>";
$body .= "<p><strong>Emirates ID / Passport:</strong> " . $_POST['emiratesId'] . "</p>";
$body .= "<p><strong>Date of Birth:</strong> " . $_POST['dob'] . "</p>";
$body .= "<p><strong>Gender:</strong> " . $_POST['gender'] . "</p>";
$body .= "<p><strong>Contact Number:</strong> " . $_POST['contact'] . "</p>";
$body .= "<p><strong>Email Address:</strong> " . $_POST['email'] . "</p>";
$body .= "<p><strong>Area:</strong> " . $_POST['area'] . "</p>";
$body .= "<p><strong>Skin Type (Fitzpatrick):</strong> " . $_POST['skinType'] . "</p>";

$body .= "<h2>Medical History & Safety Screening</h2>";
if (!empty($_POST['medicalConditions'])) {
    foreach ($_POST['medicalConditions'] as $condition) {
        $body .= "<p>☑ " . htmlspecialchars($condition) . "</p>";
    }
}
$body .= "<p><strong>Allergies or Sensitivities:</strong> " . nl2br($_POST['allergyDetails']) . "</p>";

$body .= "<h2>Consent Acknowledgment</h2>";
$body .= "<p><strong>Patient Name:</strong> " . $_POST['patientName'] . "</p>";
$body .= "<p><strong>Date of Consent:</strong> " . $_POST['consentDate'] . "</p>";
$body .= "<p><strong>Practitioner Name:</strong> " . $_POST['practitionerName'] . "</p>";

// Email headers
$boundary = md5(time());
$headers = "From: IVHUB Consent <no-reply@ivhub.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// Email body start
$message = "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $body . "\r\n";

// Attach signature
if (isset($_FILES['patientSignature']) && $_FILES['patientSignature']['error'] === UPLOAD_ERR_OK) {
    $sigTmp = $_FILES['patientSignature']['tmp_name'];
    $sigName = $_FILES['patientSignature']['name'];
    $sigData = chunk_split(base64_encode(file_get_contents($sigTmp)));
    $sigMime = mime_content_type($sigTmp);

    $message .= "--$boundary\r\n";
    $message .= "Content-Type: $sigMime; name=\"$sigName\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"$sigName\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= $sigData . "\r\n";
}

$message .= "--$boundary--";

// Send the email
if (mail($to, $subject, $message, $headers)) {
    http_response_code(200);
    echo '{"message":"email sent!"}';
} else {
    http_response_code(500);
    echo '{"message":"email not sent!"}';
}
?>