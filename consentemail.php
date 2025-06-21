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
$subject = "IV Consent Form Submission | ".$_POST['patientName'];

// Body content
$body = "<p><strong>Patient Name:</strong> " . $_POST['patientName'] . "</p>" .
        "<p><strong>Email:</strong> " . $_POST['email'] . "</p>" .
        "<p><strong>Date of Birth:</strong> " . $_POST['dob'] . "</p>" .
        "<p><strong>Mobile:</strong> " . $_POST['mobile'] . "</p>" .
        "<p><strong>Blood Pressure:</strong> " . $_POST['bloodPressure'] . "</p>" .
        "<p><strong>Pulse:</strong> " . $_POST['pulse'] . "</p>" .
        "<p><strong>Treatment:</strong> " . $_POST['treatment'] . "</p>" .
        "<p><strong>Amount (AED):</strong> " . $_POST['amount'] . "</p>" .
        "<p><strong>Payment Mode:</strong> " . $_POST['paymentMode'] . "</p>" .
        "<p><strong>Registered Nurse:</strong> " . $_POST['nurse'] . "</p>" .
        "<p><strong>Referral:</strong> " . $_POST['referral'] . "</p>" .
        "<p><strong>Appointment Time:</strong> " . $_POST['appointmentTime'] . "</p>" .
        "<p><strong>Participant:</strong> " . $_POST['participant'] . "</p>";


$body .= "<div>
            <br/>
            <p>
              In cases involving the patient’s medical history, <strong>IV Wellness Lounge Clinic</strong> cannot assume responsibility. The administration of the multivitamin drip is
              exclusively performed in response to the patient’s specific request.
            </p>
            <br/>
            <p>
              IV Wellness Lounge Clinic bears no responsibility for any financial consequences that may arise after or during the administration of the IV therapy drip treatment.
            </p>
            <br/>
            <p>
              IV Hub provides facilities and personnel to assist in the performance of intravenous therapy. You have the right to be informed of the procedure, any feasible alternative
              options, the risks and benefits. Alternatives to intravenous therapy is oral supplementation and/or dietary and lifestyle changes. Except in emergencies, procedures are not
              performed until you have had an opportunity to receive such information and to give your informed consent.
            </p>
            <br/>
            <p>
              IV Hub does not claim any clinical therapeutic outcomes, and results may vary from every individual patient. The procedure involves inserting a needle into your vein or muscle
              and injecting the formula prescribed by your physician. It will be performed by or under the direction of your physician with qualified healthcare providers.
            </p>
            <br/>
            <h4>Benefits of intravenous therapy include:</h4>
            <ul>
              <li>Injectables are not affected by stomach or intestinal disease.</li>
              <li>Total amount of infusion is available to the tissues.</li>
              <li>Nutrients are focused into cells by means of a high concentration gradient.</li>
              <li>Higher doses of nutrients can be given than possible by mouth, without intestinal irritation.</li>
            </ul>
            <br/>
            <h4>Risks of intravenous therapy include:</h4>
            <ul>
              <li>Potential risks of pain, discomfort, bruising, infection, or inflammation of the vein/phlebitis at or near the injection site.</li>
              <li>Severe allergic reaction.</li>
            </ul>
            <br/>
            <p><strong>Serious potential side effects could occur in the following patients:</strong></p>
            <ul>
              <li>G6PD deficiency (“Glucose-6-Phosphate Dehydrogenase Deficiency” also known as “Favism”)</li>
              <li>Chronic Renal Insufficiency / decreased kidney function</li>
              <li>Congestive Heart Failure and/or Atrial Fibrillation (“A-fib”)</li>
              <li>Very Low Blood Pressure (e.g., below 90/60 mm Hg, especially with Magnesium IVs)</li>
              <li>Taking Digoxin or other potassium-depleting drugs, diuretics, beta-agonists, or glucocorticoids</li>
              <li>Hypokalemic patients (especially with Magnesium IV infusions)</li>
              <li>Unknown allergies</li>
              <li>Pregnant women</li>
            </ul>
            <br/>
            <p>
              You have the right to consent to or refuse any proposed treatment at any time prior to its performance.
            </p>
            <br/>
            <p><strong>Your signature AFFIRMS that:</strong></p>
            <ul>
              <li>You understand the information provided on this form and agree to the foregoing.</li>
              <li>The procedure(s) set forth above has been adequately explained to you by your physician.</li>
              <li>You have received all the information and explanation you desire concerning the procedure.</li>
              <li>You authorize and consent to the performance of the procedure(s).</li>
            </ul>
            <br/>
            <p><strong>Person obtaining the consent:</strong></p>
            <p>(I have read this patient information sheet/consent form to the subject and/or the subject has read this form. I have provided the subject with a copy of the form. An
            explanation of the research was given and questions from the subject were solicited and answered to the subject's information. A copy of the signed consent form has been
            provided to the participant).</p>
          </div>";

// Boundary for attachments
$boundary = md5(time());
$headers = "From: IVHUB Consent <no-reply@ivhub.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

$message = "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $body . "\r\n";

// Attach signature image
if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
    $sigTmp = $_FILES['signature']['tmp_name'];
    $sigName = $_FILES['signature']['name'];
    $sigData = chunk_split(base64_encode(file_get_contents($sigTmp)));
    $sigMime = mime_content_type($sigTmp);

    $message .= "--$boundary\r\n";
    $message .= "Content-Type: $sigMime; name=\"$sigName\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"$sigName\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= $sigData . "\r\n";
}

// Attach document upload
if (isset($_FILES['documentUpload']) && $_FILES['documentUpload']['error'] === UPLOAD_ERR_OK) {
    $docTmp = $_FILES['documentUpload']['tmp_name'];
    $docName = $_FILES['documentUpload']['name'];
    $docData = chunk_split(base64_encode(file_get_contents($docTmp)));
    $docMime = mime_content_type($docTmp);

    $message .= "--$boundary\r\n";
    $message .= "Content-Type: $docMime; name=\"$docName\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"$docName\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= $docData . "\r\n";
}

$message .= "--$boundary--";

// Send mail
if (mail($to, $subject, $message, $headers)) {
    http_response_code(200);
    echo '{"message":"email sent!"}';
} else {
    http_response_code(500);
    echo '{"message":"email not sent!"}';
}
?>