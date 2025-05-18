<?php
require_once 'config.php';

$pageTitle = "About EduSphere";
require_once 'header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="display-4 text-center mb-4">About EduSphere</h1>
                    <p class="lead text-center mb-5">
                        Empowering learners and educators through innovative technology
                    </p>
                    
                    <div class="about-section mb-5">
                        <h2 class="mb-4">Our Mission</h2>
                        <p>
                            EduSphere was founded with a simple yet powerful mission: to make quality education 
                            accessible to everyone, everywhere. We believe that learning should have no boundaries, 
                            and technology can bridge the gap between knowledge seekers and providers.
                        </p>
                        <p>
                            Our platform connects students with expert instructors, providing a seamless learning 
                            experience that adapts to individual needs and schedules.
                        </p>
                    </div>
                    
                    <div class="about-section mb-5">
                        <h2 class="mb-4">Our Story</h2>
                        <p>
                            Founded in 2023 by a team of educators and technologists, EduSphere began as a small 
                            project to help local teachers transition to online education. What started as a simple 
                            solution quickly grew into a comprehensive learning management system serving thousands 
                            of users worldwide.
                        </p>
                        <p>
                            Today, EduSphere supports a diverse community of learners across multiple disciplines, 
                            offering courses from basic skills to advanced professional certifications.
                        </p>
                    </div>
                    
                    <div class="about-section mb-5">
                        <h2 class="mb-4">Key Features</h2>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="feature-card text-center p-3 h-100">
                                    <div class="feature-icon mb-3">
                                        <i class="fas fa-laptop-code fa-3x text-primary"></i>
                                    </div>
                                    <h4>Interactive Learning</h4>
                                    <p>Engaging multimedia content and hands-on exercises</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="feature-card text-center p-3 h-100">
                                    <div class="feature-icon mb-3">
                                        <i class="fas fa-chalkboard-teacher fa-3x text-primary"></i>
                                    </div>
                                    <h4>Expert Instructors</h4>
                                    <p>Learn from industry professionals and experienced educators</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="feature-card text-center p-3 h-100">
                                    <div class="feature-icon mb-3">
                                        <i class="fas fa-certificate fa-3x text-primary"></i>
                                    </div>
                                    <h4>Certification</h4>
                                    <p>Earn recognized certificates upon course completion</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="about-section">
                        <h2 class="mb-4">Our Team</h2>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="team-card text-center p-3">
                                    <img src="assets/team1.jpg" alt="Team Member" class="img-fluid rounded-circle mb-3" width="150">
                                    <h4>Dr. Sarah Johnson</h4>
                                    <p class="text-muted">Founder & CEO</p>
                                    <p>Education Technology Specialist with 15+ years experience</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="team-card text-center p-3">
                                    <img src="assets/team2.jpg" alt="Team Member" class="img-fluid rounded-circle mb-3" width="150">
                                    <h4>Michael Chen</h4>
                                    <p class="text-muted">CTO</p>
                                    <p>Software architect and learning platform developer</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="team-card text-center p-3">
                                    <img src="assets/team3.jpg" alt="Team Member" class="img-fluid rounded-circle mb-3" width="150">
                                    <h4>Emma Rodriguez</h4>
                                    <p class="text-muted">Head of Instruction</p>
                                    <p>Curriculum designer and educational consultant</p>
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