<?php



error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "iapes");

if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}

// 1. Get Form Data
$name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$college = $_POST['university'];
$degree = $_POST['degree'];
$year = $_POST['year'];
$cgpa = $_POST['cgpa'];
$domain = $_POST['domain'];
$duration = $_POST['duration'];
$skills = $_POST['skills'];

$duration_unit = "months";

// 2. Handle File Upload and Renaming
$original_filename = $_FILES['resume']['name'];
$tmp = $_FILES['resume']['tmp_name'];

// Get the file extension (e.g., pdf, docx)
$ext = pathinfo($original_filename, PATHINFO_EXTENSION);

// Clean up the name to make it a safe filename (replace spaces with underscores)
$safe_name = str_replace(' ', '_', strtolower($name));

// Create the new filename: e.g., "john_smith_1710000000.pdf"
$new_filename = $safe_name .'.' . $ext;

// Set the final upload path
$path = "uploads/" . $new_filename;

// Move the file
move_uploaded_file($tmp, $path);

$current_year = date("Y");

// 3. Generate Application Code
/* Note: I fixed the prefix mismatch here so your counter actually works */
$result = $conn->query(
    "SELECT COUNT(*) as total FROM applications WHERE application_code LIKE 'APP/$current_year/%'"
);

$row = $result->fetch_assoc();
$number = $row['total'] + 1;

/* format number like 001 */
$formatted = str_pad($number, 3, "0", STR_PAD_LEFT);

$application_code = "APP/" . $current_year . "/" . $formatted;
$token = bin2hex(random_bytes(10));

// 4. Insert into Database
$sql = "INSERT INTO applications
(application_code, verification_token, email, name, phone, college, degree, year, cgpa, domain, duration, duration_unit, skills, resume_path, status)
VALUES
('$application_code', '$token', '$email', '$name', '$phone', '$college', '$degree', '$current_year', '$cgpa', '$domain', '$duration', '$duration_unit', '$skills', '$path', 'applied')";

if($conn->query($sql)){
    echo "Application Submitted Successfully";
} else {
    echo "Error: " . $conn->error;
}

?>