<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <!-- <link rel="stylesheet" href="css/contact.css"> -->
</head>

<body>
    <a href="index.php">Home</a>
    <div class="contact-container">
        <h2>Contact Us</h2>
        <p>If you have any questions, feel free to reach out to us.</p>
        <form action="contact.php" method="POST">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <textarea name="message" placeholder="Your Message" required></textarea>
            <button type="submit" class="btn">Send Message</button>
        </form>
    </div>
</body>

</html>