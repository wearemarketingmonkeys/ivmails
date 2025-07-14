<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// CORS whitelist
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

if(empty(trim($_POST['fullName']))){
    exit(0);
}

$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey'; // literal string
    $mail->Password   = getenv('SENDGRID_API_KEY');
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('no-reply@ivhub.com', 'IVHUB Consent');
    $mail->addAddress(trim($_POST['email']));
    $mail->addReplyTo('hello@ivhub.com', 'IVHUB');
    $mail->addBCC('hello@ivhub.com');
    $mail->addBCC('riti@ivhub.com');
    $mail->addBCC('desk@ivhub.com');

    $mail->isHTML(true);
    $mail->Subject = "Microneedling Consent Form Submission | " . $_POST['fullName'];

    // Build HTML body
    $body = "<html><body>";
    $body .= "<h2>Microneedling Consent Submission</h2>";

    $fields = [
        "Full Name" => "fullName",
        "Emirates ID / Passport" => "emiratesId",
        "Date of Birth" => "dob",
        "Gender" => "gender",
        "Contact Number" => "contact",
        "Email Address" => "email",
        "Type of Microneedling" => "typeof",
        "Treatment Area(s)" => "treatmentArea",
        "Known Allergies / Sensitivities" => "allergyExplanation",
        "Patient Name" => "patientName",
        "Date of Consent" => "consentDate",
        "Practitioner Name" => "practitionerName"
    ];

    foreach ($fields as $label => $field) {
        if (!empty($_POST[$field])) {
            $value = htmlspecialchars($_POST[$field]);
            $body .= "<p><strong>$label:</strong> $value</p>";
        }
    }

    // Medical conditions list
    if (!empty($_POST['medicalConditions'])) {
        $conditions = is_array($_POST['medicalConditions']) ? $_POST['medicalConditions'] : [$_POST['medicalConditions']];
        $body .= "<p><strong>Medical Conditions:</strong><br>" . implode("<br>", array_map('htmlspecialchars', $conditions)) . "</p>";
    }

    $body .= '<p>In cases involving the patients medical history, IV Wellness Lounge Clinic cannot assume responsibility. The administration of the multivitamin drip is exclusively performed in response to the patients specific request.</p>
    <p><b>IV Wellness Lounge Clinic bears no responsibility for any financial consequences that may arise after or during the administration of the IV therapy drip treatment.</b></p>
                        <p><b>IV Hub</b> provides facilities and personnel to assist in the performance of intravenous therapy. You have the right to be informed of the procedure, any feasible alternative options, the risks and benefits. Alternatives to intravenous therapy is oral supplementation and/or dietary and lifestyle changes. Except in emergencies, procedures are not performed until you have had an opportunity to receive such information and to give your informed consent. <b>IV Hub</b> does not claim any clinical therapeutic outcomes, and results may vary from every individual patient.</p>
                        <p>The procedure involves inserting a needle into your vein or muscle and injecting the formula prescribed by your physician. It will be performed by or under the direction of your physician with qualified healthcare providers.</p>
                        <p>Benefits of intravenous therapy include:</p>
                        <ul>
                          <li>Injectable are not affected by stomach or intestinal disease.</li>
                          <li>Total amount of infusion is available to the tissues.</li>
                          <li>Nutrients are focused into cells by means of a high concentration gradient.</li>
                          <li>Higher doses of nutrients can be given than possible by mouth, without intestinal irritation.</li>
                        </ul>
                        <p>Risks of intravenous therapy include:</p>
                        <ul>
                          <li>Potential risks of pain, discomfort, bruising, infection, or inflammation of the vein/phlebitis at or near the injection site.</li>
                          <li>Severe allergic reaction.</li>
                        </ul>
                        <p>Serious potential side effects could occur in the following patients:</p>
                        <ul>
                          <li>A genetic defect called “Glucose-6-Phosphate Dehydrogenase Deficiency”, or G6PD--deficiency, also known as “Favism”</li>
                          <li>Patients with Chronic Renal Insufficiency, or decreased kidney function</li>
                          <li>Patients with Congestive Heart Failure and/or Atrial Fibrillation “A--fib”</li>
                          <li>Patients with very Low Blood Pressure, readings lower than 90 mm Hg systolic or 60 mm Hg diastolic (esp.
Magnesium containing IV Infusions)</li>
                          <li>Patients taking Digoxin or other Potassium--depleting drugs, diuretics, beta-agonists, or glucocorticoids; If patient is hypokalemic (esp. Magnesium containing IV Infusions)</li>
                          <li>Unknown allergies</li>
                          <li>Pregnant women</li>
                        </ul>
                        <p>You have the right to consent to or refuse any proposed treatment at any time prior to its performance.</p>
                        <p><b>Your signature AFFIRMS that:</b></p>
                        <ul>
                          <li>You understand the information provided on this form and agree to the foregoing.</li>
                          <li>The procedure(s) set forth above has been adequately explained to you by your physician.</li>
                          <li>You have received all the information and explanation you desire concerning the procedure.</li>
                          <li>You authorize and consent to the performance of the procedure(s).</li>
                        </ul>';

    $body .= "<hr><p><strong>Submitted via Microneedling Consent Form on IVHUB</strong></p>";
    $body .= "</body></html>";

    $mail->Body = $body;

    // Signature attachment
    if (isset($_FILES['patientSignature']) && $_FILES['patientSignature']['error'] === UPLOAD_ERR_OK) {
        $mail->addAttachment(
            $_FILES['patientSignature']['tmp_name'],
            $_FILES['patientSignature']['name']
        );
    }

    $mail->send();
    http_response_code(200);
    echo json_encode(["message" => "Form submitted successfully."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Form submission failed: {$mail->ErrorInfo}"]);
}
?>