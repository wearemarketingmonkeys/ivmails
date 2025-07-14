<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// CORS Origin Whitelist
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

if(empty(trim($_POST['patientName']))){
    exit(0);
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey';
    $mail->Password   = getenv('SENDGRID_API_KEY');
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('no-reply@ivhub.com', 'IVHUB Consent');
    $mail->addAddress(trim($_POST['email']));
    $mail->addReplyTo('hello@ivhub.com', 'IVHUB');
    $mail->addBCC('hello@ivhub.com');
    $mail->addBCC('riti@ivhub.com');
    $mail->addBCC('desk@ivhub.com');

    $mail->Subject = "IV Consent Form Submission | " . $_POST['patientName'];
    $mail->isHTML(true);

    // Dynamic fields
    $body = "<p><strong>Patient Name:</strong> {$_POST['patientName']}</p>" .
            "<p><strong>Email:</strong> {$_POST['email']}</p>" .
            "<p><strong>Date of Birth:</strong> {$_POST['dob']}</p>" .
            "<p><strong>Mobile:</strong> {$_POST['mobile']}</p>" .
            "<p><strong>Blood Pressure:</strong> {$_POST['bloodPressure']}</p>" .
            "<p><strong>Pulse:</strong> {$_POST['pulse']}</p>" .
            "<p><strong>Treatment:</strong> {$_POST['treatment']}</p>" .
            "<p><strong>Amount (AED):</strong> {$_POST['amount']}</p>" .
            "<p><strong>Payment Mode:</strong> {$_POST['paymentMode']}</p>" .
            "<p><strong>Registered Nurse:</strong> {$_POST['nurse']}</p>" .
            "<p><strong>Referral:</strong> {$_POST['referral']}</p>" .
            "<p><strong>Appointment Time:</strong> {$_POST['appointmentTime']}</p>";

    // Full consent HTML (your original content)
    $body .= '<p>In cases involving the patient\'s medical history, IV Wellness Lounge Clinic cannot assume responsibility. The administration of the multivitamin drip is exclusively performed in response to the patient\'s specific request.</p>
<p><strong>IV Wellness Lounge Clinic bears no responsibility for any financial consequences that may arise after or during the administration of the IV therapy drip treatment.</strong></p>
<p><strong>IV Hub</strong> provides facilities and personnel to assist in the performance of intravenous therapy. You have the right to be informed of the procedure, any feasible alternative options, the risks and benefits. Alternatives to intravenous therapy include oral supplementation and/or dietary and lifestyle changes. Except in emergencies, procedures are not performed until you have had an opportunity to receive such information and to give your informed consent. <strong>IV Hub</strong> does not claim any clinical therapeutic outcomes, and results may vary from patient to patient.</p>
<p>The procedure involves inserting a needle into your vein or muscle and injecting the formula prescribed by your physician. It will be performed by or under the direction of your physician with qualified healthcare providers.</p>

<p><strong>Benefits of intravenous therapy include:</strong></p>
<ul>
  <li>Injectables are not affected by stomach or intestinal disease.</li>
  <li>Total amount of infusion is available to the tissues.</li>
  <li>Nutrients are focused into cells by means of a high concentration gradient.</li>
  <li>Higher doses of nutrients can be given than possible by mouth, without intestinal irritation.</li>
</ul>

<p><strong>Risks of intravenous therapy include:</strong></p>
<ul>
  <li>Potential risks of pain, discomfort, bruising, infection, or inflammation of the vein/phlebitis at or near the injection site.</li>
  <li>Severe allergic reaction.</li>
</ul>

<p><strong>Serious potential side effects could occur in the following patients:</strong></p>
<ul>
  <li>A genetic defect called "Glucose-6-Phosphate Dehydrogenase Deficiency", or G6PD-deficiency, also known as "Favism"</li>
  <li>Patients with Chronic Renal Insufficiency, or decreased kidney function</li>
  <li>Patients with Congestive Heart Failure and/or Atrial Fibrillation (A-fib)</li>
  <li>Patients with very low blood pressure (e.g., lower than 90/60 mm Hg), especially with Magnesium-containing IV infusions</li>
  <li>Patients taking Digoxin or other potassium-depleting drugs, diuretics, beta-agonists, or glucocorticoids - particularly if hypokalemic (especially with Magnesium IV infusions)</li>
  <li>Patients with unknown allergies</li>
  <li>Pregnant women</li>
</ul>

<p>You have the right to consent to or refuse any proposed treatment at any time prior to its performance.</p>

<p><strong>Your signature AFFIRMS that:</strong></p>
<ul>
  <li>You understand the information provided on this form and agree to the foregoing.</li>
  <li>The procedure(s) described above have been adequately explained to you by your physician.</li>
  <li>You have received all the information and explanation you desire concerning the procedure.</li>
  <li>You authorize and consent to the performance of the procedure(s).</li>
</ul>';


    $mail->Body = $body;

    // Attachments
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $mail->addAttachment($_FILES['signature']['tmp_name'], $_FILES['signature']['name']);
    }

    if (isset($_FILES['documentUpload']) && $_FILES['documentUpload']['error'] === UPLOAD_ERR_OK) {
        $mail->addAttachment($_FILES['documentUpload']['tmp_name'], $_FILES['documentUpload']['name']);
    }

    $mail->send();
    http_response_code(200);
    echo json_encode(["message" => "Email sent successfully!"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Email failed: {$mail->ErrorInfo}"]);
}
?>
