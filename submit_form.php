<?php
// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$response = ['success' => false, 'message' => ''];

session_start();
$now = time();
$limit_time = 60;
$limit_requests = 3;

if (!isset($_SESSION['form_submissions'])) {
    $_SESSION['form_submissions'] = [];
}

$_SESSION['form_submissions'] = array_filter($_SESSION['form_submissions'], function($timestamp) use ($now, $limit_time) {
    return ($now - $timestamp) < $limit_time;
});

if (count($_SESSION['form_submissions']) >= $limit_requests) {
    $response['message'] = 'Trop de soumissions. Veuillez attendre. / Too many submissions. Please wait.';
    echo json_encode($response);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $civility = isset($_POST['civility']) ? trim($_POST['civility']) : '';
    $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
    $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
    $birthdate = isset($_POST['birthdate']) ? trim($_POST['birthdate']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $cin = isset($_POST['cin']) ? trim($_POST['cin']) : '';
    $store = isset($_POST['store']) ? htmlspecialchars(trim($_POST['store'])) : '';
    $consent = isset($_POST['consent']) ? 1 : 0;

    if (empty($civility) || empty($lastname) || empty($firstname) || empty($birthdate) ||
        empty($city) || empty($phone) || empty($email) || empty($cin) || empty($store)) {
        throw new Exception('Tous les champs sont obligatoires / All fields are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide / Invalid email format');
    }

    if (!$consent) {
        throw new Exception('Vous devez accepter les conditions / You must accept the terms');
    }

    if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception('Veuillez télécharger un reçu / Please upload a receipt');
    }

    $file = $_FILES['receipt'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors du téléchargement du fichier / File upload error');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('Le fichier est trop volumineux (max 5MB) / File is too large');
    }

    // Sécurité Fileinfo (vérifiez si l'extension est activée dans PHP)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_TYPES)) {
        throw new Exception('Type de fichier non autorisé / Invalid file type');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFilename = uniqid('receipt_' . date('Ymd_')) . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Échec de l\'enregistrement du fichier');
    }

    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    $stmt = $conn->prepare("INSERT INTO registrations (civility, lastname, firstname, birthdate, city, phone, email, cin, store, receipt_filename, receipt_path, consent, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $receipt_path = 'uploads/' . $newFilename;

    $stmt->bind_param("sssssssssssss", $civility, $lastname, $firstname, $birthdate, $city, $phone, $email, $cin, $store, $newFilename, $receipt_path, $consent, $ip_address);

    if ($stmt->execute()) {
        $_SESSION['form_submissions'][] = $now;

        if (defined('ENABLE_EMAIL_NOTIFICATIONS') && ENABLE_EMAIL_NOTIFICATIONS) {
            sendAdminEmailNotification([
                'civility' => $civility, 'lastname' => $lastname, 'firstname' => $firstname,
                'birthdate' => $birthdate, 'city' => $city, 'phone' => $phone,
                'email' => $email, 'cin' => $cin, 'store' => $store,
                'receipt_filename' => $newFilename, 'ip_address' => $ip_address
            ], $receipt_path);
        }

        $response['success'] = true;
        $response['message'] = 'Enregistrement réussi ! / Registration successful!';
    } else {
        throw new Exception('Erreur lors de l\'enregistrement');
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    if (isset($uploadPath) && file_exists($uploadPath)) { unlink($uploadPath); }
    error_log("Form submission error: " . $e->getMessage());
}

echo json_encode($response);

function sendAdminEmailNotification($data, $receiptPath) {
    if (!defined('ADMIN_EMAIL') || ADMIN_EMAIL === 'your-email@example.com') return false;

    $websiteUrl = defined('WEBSITE_URL') ? WEBSITE_URL : 'https://' . $_SERVER['HTTP_HOST'];
    $receiptUrl = $websiteUrl . '/' . $receiptPath;
    $subject = "Nouvelle inscription - Groupe SEB / New Registration";
    $submissionDate = date('d/m/Y H:i:s');

    $body = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #444; max-width: 600px; margin: 0 auto; padding: 0; background-color: #f4f4f4; }
        .wrapper { padding: 20px; }
        .header { background: #e30613; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; text-transform: uppercase; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #eeeeee; border-radius: 0 0 8px 8px; }
        .section-title { font-size: 15px; font-weight: bold; color: #e30613; border-bottom: 1px solid #f0f0f0; padding-bottom: 8px; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; }
        .info-row { margin-bottom: 8px; border-bottom: 1px solid #fafafa; }
        .label { font-weight: 600; color: #777; font-size: 11px; text-transform: uppercase; display: block; }
        .value { color: #222; font-size: 14px; display: block; }
        .action-area { text-align: center; margin-top: 25px; padding: 20px; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ddd; }
        .receipt-link { display: inline-block; background: #e30613; color: #ffffff !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header"><h1>Groupe SEB</h1></div>
        <div class="content">
            <p>Soumission reçue le <strong>{$submissionDate}</strong></p>
            <div class="section-title">👤 Participant</div>
            <div class="info-row"><span class="label">Identité :</span><span class="value">{$data['civility']} {$data['firstname']} {$data['lastname']}</span></div>
            <div class="info-row"><span class="label">CIN :</span><span class="value">{$data['cin']}</span></div>
            <div class="section-title">📍 Coordonnées</div>
            <div class="info-row"><span class="label">Email :</span><span class="value">{$data['email']}</span></div>
            <div class="info-row"><span class="label">Téléphone :</span><span class="value">{$data['phone']}</span></div>
            <div class="section-title">🛒 Achat</div>
            <div class="info-row"><span class="label">Magasin :</span><span class="value">{$data['store']}</span></div>
            <div class="action-area">
                <a href="{$receiptUrl}" class="receipt-link">VOIR LE REÇU</a>
            </div>
        </div>
        <div class="footer">2026 Groupe SEB Maroc ©</div>
    </div>
</body>
</html>
HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Groupe SEB <' . (defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@' . $_SERVER['HTTP_HOST']) . '>',
        'Reply-To: ' . $data['email'],
        'X-Mailer: PHP/' . phpversion()
    ];

    return @mail(ADMIN_EMAIL, $subject, $body, implode("\r\n", $headers));
}