<?php
require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For now, we'll just store it in the database
        
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages 
                                 (name, email, subject, message, created_at) 
                                 VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $subject, $message]);
            
            $_SESSION['success_message'] = 'Thank you for your message! We will get back to you soon.';
            header("Location: contact.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Failed to send your message. Please try again.';
        }
    }
}

$pageTitle = "Contact Us";
require_once 'header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="display-4 text-center mb-4">Contact Us</h1>
                    <p class="lead text-center mb-5">
                        Have questions? We're here to help!
                    </p>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <form method="POST" action="contact.php">
                                <div class="form-group">
                                    <label for="name">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="subject">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="message">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required><?= 
                                        isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' 
                                    ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="contact-info">
                                <h4 class="mb-4">Contact Information</h4>
                                <div class="d-flex align-items-start mb-4">
                                    <div class="icon mr-3">
                                        <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h5>Address</h5>
                                        <p>123 Education Street<br>Learning City, LC 12345</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start mb-4">
                                    <div class="icon mr-3">
                                        <i class="fas fa-phone fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h5>Phone</h5>
                                        <p>+1 (123) 456-7890<br>Mon-Fri, 9am-5pm</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start mb-4">
                                    <div class="icon mr-3">
                                        <i class="fas fa-envelope fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h5>Email</h5>
                                        <p>info@edusphere.com<br>support@edusphere.com</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="social-media mt-5">
                                <h4 class="mb-4">Follow Us</h4>
                                <div class="d-flex">
                                    <a href="#" class="social-icon mr-3">
                                        <i class="fab fa-facebook-f fa-2x"></i>
                                    </a>
                                    <a href="#" class="social-icon mr-3">
                                        <i class="fab fa-twitter fa-2x"></i>
                                    </a>
                                    <a href="#" class="social-icon mr-3">
                                        <i class="fab fa-linkedin-in fa-2x"></i>
                                    </a>
                                    <a href="#" class="social-icon">
                                        <i class="fab fa-instagram fa-2x"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>