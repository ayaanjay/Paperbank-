<?php
session_start();
include("includes/csrf.php");
include("config/db.php");

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';

// Handle form submission
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken()) {
        $message = ['text' => 'Security validation failed. Please try again.', 'type' => 'error'];
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $bug_description = trim($_POST['bug_description'] ?? '');

        // Basic validation
        if (empty($name) || empty($email) || empty($subject) || empty($bug_description)) {
            $message = ['text' => 'All fields are required.', 'type' => 'error'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = ['text' => 'Invalid email format.', 'type' => 'error'];
        } else {
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // TODO: Replace if using different SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'ayaan.pandey@acaonefamily.com'; // TODO: Replace with your Gmail address
                $mail->Password = 'ltxw ceec eydr lviu'; // TODO: Replace with your Gmail app password (not regular password)
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom($email, $name);
                $mail->addAddress('ayaan.pandey@acaonefamily.com'); // Your email

                // Content
                $mail->isHTML(false);
                $mail->Subject = "Bug Report: $subject";
                $mail->Body = "Name: $name\nEmail: $email\n\nBug Description:\n$bug_description";

                $mail->send();
                $message = ['text' => 'Bug report submitted successfully. Thank you!', 'type' => 'success'];
            } catch (Exception $e) {
                $message = ['text' => "Failed to send bug report. Mailer Error: {$mail->ErrorInfo}", 'type' => 'error'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>More - PaperBank</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/more.css">
    <style>
        /* Inline styles for animations and layout */
        .section {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .section.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .contact-card, .email-card, .form-card, .statement-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            animation: slideIn 0.8s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        .contact-info, .email-info {
            font-size: 16px;
            line-height: 1.6;
        }
        .statement-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
<?php include("includes/navbar.php"); ?>

<div class="page-container">
    <h1 class="page-title" style="text-align: center; margin-bottom: 50px;">More Information</h1>

    <!-- About Section -->
    <section id="about" class="section">
        <h2>About</h2>
        <div class="contact-card">
            <div class="contact-info">
                <p>This is an INDEPENDENT PROJECT Created By: Ayaan, 11th grade.</p>
            </div>
        </div>
    </section>

    <!-- Email Section -->
    <section id="email" class="section">
        <h2>Email Support</h2>
        <div class="email-card">
            <div class="email-info">
                <p><strong>Email 1:</strong> ayaan.pandey@acaonefamily.com</p> <!-- TODO: Replace with your actual email -->
                <p><strong>Email 2:</strong> ayorinde.omojuwa@acaonefamily.com</p> <!-- TODO: Replace with your actual email -->
                <p><strong>Response Time:</strong> We aim to respond within 24-48 hours.</p>
            </div>
        </div>
    </section>

    <!-- Bug/Issues Form Section -->
    <section id="bugs" class="section">
        <h2>Report Bugs & Issues</h2>
        <div class="form-card">
            <?php include("includes/flash.php"); ?>
            <form method="POST" action="more.php#bugs">
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label for="name">Your Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Your Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="bug_description">Describe the Bug/Issue:</label>
                    <textarea id="bug_description" name="bug_description" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Submit Report</button>
            </form>
        </div>
    </section>

    <!-- Problem Statement Section -->
    <section id="problem-statement" class="section">
        <h2>Problem Statement</h2>
        <div class="statement-card">
            <div class="statement-content">
                <h3>Scope</h3>
                <p>PaperBank is designed to provide students with convenient access to previous class assessments, including tests, classwork, quizzes, homework, and exams uploaded by their teachers. The platform serves as a digital repository where academic materials are organized by subject, grade level, and teacher, enabling students to easily locate and review relevant past assessments.</p>

                <h3>Goals</h3>
                <p>The goal of PaperBank is to enhance student preparedness and academic performance by providing easy, organized, and secure access to teachers' past assessments, enabling students to study effectively and understand the structure of upcoming tests or classwork.</p>

                <h3>Objectives</h3>
                <ul>
                    <li>To create a centralized digital repository where teachers can upload and manage past tests, homework, and classwork for student access.</li>
                    <li>To provide students with access to past assessment materials for improved revision and self study.</li>
                    <li>To help students become familiar with teachers' question formats and improve their confidence before exams or classwork.</li>
                    <li>To reduce the gap between classroom learning and assessment expectations, ensuring better performance and understanding.</li>
                    <li>To promote independent learning by giving students control over their study materials and pace.</li>
                    <li>To maintain security and privacy by ensuring only authorized users (teachers and admin) can upload or view school-related content.</li>
                </ul>

                <h3>Users</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Users</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Role</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Activities</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">Students (Middle and High school)</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">Access and download past assessment for revision and study.</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <ul style="margin: 0; padding-left: 20px;">
                                    <li>Search for papers by subjects, grade, or teacher</li>
                                    <li>View or download classwork, homework, tests, and exams.</li>
                                    <li>Use materials to prepare for upcoming assessments.</li>
                                </ul>
                            </td>
                        </tr>
                        <tr style="background-color: #f8f9fa;">
                            <td style="border: 1px solid #ddd; padding: 8px;">Teachers</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">Upload and manage past assessments for student access</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <ul style="margin: 0; padding-left: 20px;">
                                    <li>Upload classwork, homework, tests, and exams.</li>
                                    <li>Tag materials by subject, grade level, and year</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">Admin</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">Manage user account</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <ul style="margin: 0; padding-left: 20px;">
                                    <li>Add, delete and update material</li>
                                    <li>Approve teacher uploads</li>
                                    <li>Manage Teacher rights</li>
                                    <li>Monitor system performance and content integrity.</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<?php include("includes/footer.php"); ?>

<script>
    // Intersection Observer for animations
    const sections = document.querySelectorAll('.section');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    sections.forEach(section => {
        observer.observe(section);
    });

    // Smooth scroll to anchor on page load if hash exists
    if (window.location.hash) {
        const target = document.querySelector(window.location.hash);
        if (target) {
            setTimeout(() => {
                target.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        }
    }
</script>
</body>
</html>
