<?php

// set the following two values and the rest of the contact script should work
const HASHCASH_PRIVATE_KEY = "";
const YOUR_EMAIL_ADDRESS = "";

$nameErr = $emailErr = $subjectErr = $messageErr = "";
$name = $email = $subject = $emailBody = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (empty($_POST["name"])) {
    $nameErr = "Name is required";
  } else {
    $name = test_input($_POST["name"]);
    // check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
      $nameErr = "Only letters and white space allowed";
    }
  }
  
  if (empty($_POST["email"])) {
    $emailErr = "Email is required";
  } else {
    $email = test_input($_POST["email"]);
    // check if e-mail address is well-formed
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format";
    }
  }
    
  if (empty($_POST["subject"])) {
    $subjectErr = "Subject is required";
  } else {
    $subject = test_input($_POST["subject"]);
  }
  
  if (empty($_POST["emailBody"])) {
    $messageErr = "Message is required";
  } else {
    $emailBody = test_input($_POST["emailBody"]);
  }

  if (! $_REQUEST['hashcashid']) {
    $captchaErr = 'Please unlock the submit button!';
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if(isset($_POST['submit']) && isset($_POST['hashcashid']) && (!empty($_POST["name"])) && (preg_match("/^[a-zA-Z ]*$/",$name)) && (!empty($_POST["email"])) &&  (filter_var($email, FILTER_VALIDATE_EMAIL)) && (!empty($_POST["subject"])) && (!empty($_POST["emailBody"]))) {
    // validate that the client performed the proof of work for the captcha
    $url = 'https://hashcash.io/api/checkwork/' . $_POST['hashcashid'] . '?apikey=' . HASHCASH_PRIVATE_KEY;
    $response = json_decode(file_get_contents($url));

    if (!$response) {
      $captchaErr = 'Something went wrong; please try again.';
    } else if ($response->verified) {
      $captchaErr = 'This proof of work was already used. Nice try!';
    } else if ($response->totalDone < 0.1) {
      $captchaErr = 'Failed to complete enough proof of work for form CAPTCHA. Nice try!';
    } else {
      // All good
      $from = $_POST['email'];
      $name = $_POST['name'];
      $subject = $_POST['subject'];
      $emailBody = $name . " from email: " . $email . " wrote the following:" . "\n\n" . $_POST['emailBody'];
      $headers = "From: $from\r\nReply-to: $email";
      mail(YOUR_EMAIL_ADDRESS, $subject, $emailBody, $headers);
      echo '<META HTTP-EQUIV="Refresh" Content="0; URL=thank_you.html">';
      exit;
    }
  }
?>