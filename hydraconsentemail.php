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
$subject = "HydraFacial Consent Form Submission | ".$_POST['fullName'];

// MIME setup
$boundary = md5(time());
$headers = "From: IVHUB Consent <no-reply@ivhub.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// Build HTML email
$body = "--$boundary\r\n";
$body .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

$body .= "<html><body>";
$body .= "<h2>HydraFacial Consent Form Submission</h2>";

$fields = [
    "Full Name" => "fullName",
    "Emirates ID / Passport" => "emiratesId",
    "Date of Birth" => "dob",
    "Gender" => "gender",
    "Contact Number" => "contact",
    "Email Address" => "email",
    "Allergies / Medical Conditions" => "allergiesDescription",
    "Photography Consent" => "photographyConsent",
    "Patient Name" => "patientName",
    "Consent Date" => "consentDate",
    "Practitioner Name" => "practitionerName"
];

foreach ($fields as $label => $key) {
    $value = isset($_POST[$key]) ? nl2br(htmlspecialchars($_POST[$key])) : 'Not provided';
    $body .= "<p><strong>$label:</strong> $value</p>";
}

// Medical Conditions list
if (!empty($_POST['medicalConditions'])) {
    $body .= "<p><strong>Disclosed Medical Conditions:</strong></p><ul>";
    foreach ($_POST['medicalConditions'] as $item) {
        $body .= "<li>" . htmlspecialchars($item) . "</li>";
    }
    $body .= "</ul>";
}

$body .= "<hr><p><strong>Submission received via IVHUB HydraFacial form.</strong></p>";
$body .= "</body></html>\r\n";

// Patient Signature Attachment
if (isset($_FILES['patientSignature']) && $_FILES['patientSignature']['error'] === UPLOAD_ERR_OK) {
    $sigTmp = $_FILES['patientSignature']['tmp_name'];
    $sigName = $_FILES['patientSignature']['name'];
    $sigData = chunk_split(base64_encode(file_get_contents($sigTmp)));
    $sigMime = mime_content_type($sigTmp);

    $body .= "--$boundary\r\n";
    $body .= "Content-Type: $sigMime; name=\"$sigName\"\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$sigName\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= $sigData . "\r\n";
}

$body .= "--$boundary--";

// Send the email
if (mail($to, $subject, $body, $headers)) {
    http_response_code(200);
    echo '{"message":"HydraFacial form submitted successfully!"}';
} else {
    http_response_code(500);
    echo '{"message":"Submission failed!"}';
}
?>