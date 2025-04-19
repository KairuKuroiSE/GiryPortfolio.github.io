<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Anti-spam: Check if the form was submitted too quickly
    if (isset($_SESSION['last_submission_time']) && (time() - $_SESSION['last_submission_time'] < 30)) {
        die("You are submitting too quickly. Please wait before trying again.");
    }

    // Validate CAPTCHA
    if (empty($_POST['captcha']) || $_POST['captcha'] != '7') {
        die("Incorrect CAPTCHA answer.");
    }

    // Sanitize and validate inputs
    $fullname = filter_var(trim($_POST['fullname']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

    if (!$fullname || !$email || !$subject || !$message) {
        die("Invalid input. Please fill out the form correctly.");
    }

    // Handle file uploads (optional)
    if (!empty($_FILES['files']['name'][0])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['files']['name'] as $key => $filename) {
            $tmpName = $_FILES['files']['tmp_name'][$key];
            $destination = $uploadDir . basename($filename);

            if (!move_uploaded_file($tmpName, $destination)) {
                die("Failed to upload file: " . htmlspecialchars($filename));
            }
        }
    }

    // Prepare email
    $to = "KairuKuroiSE@gmail.com"; // Replace with your email address
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $emailBody = "Name: $fullname\n";
    $emailBody .= "Email: $email\n";
    $emailBody .= "Subject: $subject\n";
    $emailBody .= "Message:\n$message\n";

    // Send email
    if (mail($to, $subject, $emailBody, $headers)) {
        echo "Thank you for your message. We will get back to you shortly.";
    } else {
        die("Failed to send email. Please try again later.");
    }

    // Update last submission time
    $_SESSION['last_submission_time'] = time();
} else {
    die("Invalid request method.");
}
?>