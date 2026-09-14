<?php
session_start();
include("includes/csrf.php");
include("config/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - PaperBank</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/help.css">
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
        .instructions-card {
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
        .guide-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        .guide-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        .step-container {
            margin-top: 25px;
        }
        .step {
            display: flex;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .step:hover {
            background: #eef0f9;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }
        .step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 18px;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .step-content h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 1.1rem;
        }
        .step-content p {
            margin: 0;
            color: #666;
            line-height: 1.6;
        }
        .step-content ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
            color: #666;
            line-height: 1.6;
        }
        .step-content li {
            margin-bottom: 6px;
        }
        .feature-highlight {
            background: #e7f0ff;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
        }
        .feature-highlight strong {
            color: #2563eb;
        }
        .feature-highlight p {
            margin: 5px 0;
            color: #444;
            font-size: 14px;
        }
    </style>
</head>
<body>
<?php include("includes/navbar.php"); ?>

<div class="page-container">
    <h1 class="page-title" style="text-align: center; margin-bottom: 50px;">Help Center</h1>

    <!-- Students Guide Section -->
    <section id="students" class="section">
        <h2>Guide for Students</h2>
        <div class="instructions-card">
            <div class="guide-title">How to Use PaperBank as a Student</div>
            <div class="guide-description">PaperBank allows you to easily browse, search, filter, and download past papers to help you prepare for exams and assessments.</div>
            
            <div class="step-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Access Papers by Subject or Browse</h3>
                        <p>Click on the subject name in the navigation bar, then choose your grade to see relevant papers. Alternatively, go to "Papers" and browse all available papers from there.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Filter by Subject, Grade & Category</h3>
                        <p>Use the filter dropdowns to narrow down papers by:</p>
                        <ul>
                            <li><strong>Grade</strong> - Select your grade level</li>
                            <li><strong>Subject</strong> - Choose the subject you want to study</li>
                            <li><strong>Category</strong> - Filter by type (Past Exams, Class Assessments, etc.)</li>
                            <li><strong>Year</strong> - Select the academic year</li>
                            <li><strong>Track</strong> - Choose your curriculum track if applicable</li>
                        </ul>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Search for Specific Papers</h3>
                        <p>Use the search box to find papers by title or keyword. This helps you quickly locate specific assessments or exam papers.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>View Paper Details</h3>
                        <p>Click on any paper to see detailed information including:</p>
                        <ul>
                            <li>Subject and grade level</li>
                            <li>Teacher who uploaded the paper</li>
                            <li>Year and category</li>
                            <li>Upload date</li>
                        </ul>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h3>Download Papers</h3>
                        <p>Click the "Download" button to save file-based papers to your device. For link-based papers, click directly on the displayed URL to access the resource.</p>
                    </div>
                </div>

            </div>

            <div class="feature-highlight">
                <strong>💡 Tip:</strong>
                <p>Most papers are automatically approved and available immediately after upload. Browse regularly to find newly added materials from your teachers.</p>
            </div>

            <div class="feature-highlight">
                <strong>❓ Can't find a paper?</strong>
                <p>Try using the search function with different keywords, or check with your teacher to see if they've uploaded the specific paper you're looking for.</p>
            </div>
        </div>
    </section>

    <!-- Teachers Guide Section -->
    <section id="teachers" class="section">
        <h2>Guide for Teachers</h2>
        <div class="instructions-card">
            <div class="guide-title">How to Use PaperBank as a Teacher</div>
            <div class="guide-description">PaperBank allows you to upload, organize, and manage past papers that students can access for exam preparation and practice. You can upload both files and direct links to resources.</div>
            
            <div class="step-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Register or Login with the Universal Password</h3>
                        <p>Visit the login page. If you have not registered yet, use the registration form and enter the given universal password to create your teacher account. If you already have an account, log in with your existing credentials.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Change Your Password After First Login</h3>
                        <p>After your first login, you must immediately change the default universal password to a personal, secure password. Navigate to your account settings to complete this mandatory security step.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Access the Upload Page</h3>
                        <p>From your teacher dashboard, click on "Upload Paper" or navigate to the Upload section. This is where you'll manage all your paper uploads.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Choose Upload Type and Fill in Paper Details</h3>
                        <p>Select whether you want to upload a file or provide a link, then complete the following information for each paper:</p>
                        <ul>
                            <li><strong>Title</strong> - Give the paper a descriptive name (e.g., "Physics Midterm Exam 2025")</li>
                            <li><strong>Subject</strong> - Select the subject from the dropdown</li>
                            <li><strong>Grade</strong> - Choose the grade level</li>
                            <li><strong>Category</strong> - Select the type (Past Exam, Class Assessment, Homework, Classwork)</li>
                            <li><strong>Year</strong> - Enter the academic year</li>
                            <li><strong>Track</strong> - Choose the curriculum track if applicable</li>
                        </ul>
                        <p><strong>For file uploads:</strong> Select a file from your computer (max 50MB, PDF, DOC, DOCX, TXT, JPG, PNG)</p>
                        <p><strong>For link uploads:</strong> Provide a direct URL to the resource</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h3>Upload Your Paper File or Provide Link</h3>
                        <p>Depending on your upload type selection:</p>
                        <ul>
                            <li><strong>File Upload:</strong> Click "Choose File" and select the paper document from your computer</li>
                            <li><strong>Link Upload:</strong> Enter the direct URL to the resource in the link field</li>
                        </ul>
                        <p>Supported file formats: PDF, DOC, DOCX, TXT, JPG, PNG (maximum 50MB)</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">6</div>
                    <div class="step-content">
                        <h3>Submit the Paper</h3>
                        <p>Click the "Upload" button to submit your paper. The paper will be automatically approved and will immediately become available for students to download.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">7</div>
                    <div class="step-content">
                        <h3>View Your Uploaded Papers</h3>
                        <p>Access "My Papers" from your dashboard to see all papers you've uploaded. You can view details like upload date, number of downloads, and paper information.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">8</div>
                    <div class="step-content">
                        <h3>Edit Paper Details</h3>
                        <p>Click "Edit" on any paper in your "My Papers" list to modify its details like title, subject, category, or description. You cannot change the file itself through this option.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">9</div>
                    <div class="step-content">
                        <h3>Delete Papers</h3>
                        <p>If you need to remove a paper from the system, click "Delete" on the paper's listing. This will immediately remove it from student access. Use this for outdated or incorrect uploads.</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">10</div>
                    <div class="step-content">
                        <h3>Manage Your Account</h3>
                        <p>Go to "My Account" to update your personal information, change your password, or view your profile. Keep your information current for system security and communication.</p>
                    </div>
                </div>
            </div>

            <div class="feature-highlight">
                <strong>📝 Best Practices:</strong>
                <p><strong>Organization:</strong> Use clear, descriptive titles and select appropriate categories to help students find your papers easily.<br>
                <strong>Consistency:</strong> Upload papers regularly and keep information current (year, subject, category correctly classified).<br>
                <strong>Accuracy:</strong> Double-check file names and paper details before uploading to ensure accuracy.</p>
            </div>

            <div class="feature-highlight">
                <strong>🔒 Security Reminders:</strong>
                <p><strong>Password:</strong> Change your default password immediately on first login. Never share your credentials.<br>
                <strong>Files:</strong> Ensure uploaded files are appropriately formatted and free of sensitive personal information beyond what should be on an exam paper.</p>
            </div>

            <div class="feature-highlight">
                <strong>✅ Automatic Approval:</strong>
                <p>All papers you upload are automatically approved and immediately available to students. There's no waiting period!</p>
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
</script>
</body>
</html>
