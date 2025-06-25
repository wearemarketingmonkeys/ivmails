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
    $mail->addAddress('hello@ivhub.com');
    $mail->addAddress('riti@ivhub.com');
    $mail->addAddress('desk@ivhub.com');
    $mail->addAddress('fiona@ivhub.com');

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
    $body .= <<<HTML
    <div>
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
    </div>
    HTML;

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
