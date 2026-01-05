<?php
/**
 * Test if mail() function is working
 * This checks if XAMPP is configured to send emails
 */

// Test 1: Simple mail test
$test_email = '9918043621@sms.dito.ph';
$subject = 'Test SMS';
$message = 'Test message 12345';
$headers = "From: noreply@solesource.local\r\nContent-Type: text/plain; charset=UTF-8";

$result = @mail($test_email, $subject, $message, $headers);

echo "<!DOCTYPE html>";
echo "<html><head><title>Mail Test</title><style>body{font-family:Arial;padding:20px;}</style></head><body>";
echo "<h2>XAMPP Mail Configuration Test</h2>";

if ($result) {
    echo "<p style='color:green;'><strong>✓ mail() function returned TRUE</strong></p>";
    echo "<p>Email was sent (or queued) to: <code>$test_email</code></p>";
    echo "<p>This means XAMPP mail is configured. Check your DITO phone for SMS in 1-2 minutes.</p>";
} else {
    echo "<p style='color:red;'><strong>✗ mail() function returned FALSE</strong></p>";
    echo "<p>XAMPP is NOT configured to send emails.</p>";
    echo "<p><strong>Solution:</strong> Configure XAMPP's sendmail in <code>php.ini</code></p>";
}

echo "<hr>";
echo "<h3>Detailed Info:</h3>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>sendmail_path:</strong> " . ini_get('sendmail_path') . "</li>";
echo "<li><strong>SMTP:</strong> " . ini_get('SMTP') . "</li>";
echo "<li><strong>smtp_port:</strong> " . ini_get('smtp_port') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='profile.php'>Back to Profile</a></p>";
echo "</body></html>";
?>
