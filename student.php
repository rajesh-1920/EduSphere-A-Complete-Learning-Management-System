<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students</title>
    <link rel="stylesheet" href="css/student.css">
    <link rel="stylesheet" href="template/css/navbar.css">
</head>

<body>
    <!-- Navbar & Sidebar -->
    <?php
    include_once("template/navbar.php");
    ?>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Hero Section -->
        <div class="all-students-container">
            <h2>All Students</h2>
            <ul class="students-list">
                <li>John Doe - johndoe@example.com</li>
                <li>Jane Smith - janesmith@example.com</li>
                <li>Michael Brown - michaelbrown@example.com</li>
                <li>Emily Johnson - emilyjohnson@example.com</li>
                <li>David Wilson - davidwilson@example.com</li>
            </ul>
        </div>
        <!-- Footer -->
        <?php
        include_once("template/footer.php");
        ?>
    </div>
</body>

</html>