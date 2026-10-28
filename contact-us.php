<?php 
include('include/header.php'); 

$msg = null;
$error = null;

if (isset($_POST['send_message'])) {
    $fullname = htmlspecialchars(trim($_POST['fullname'] ?? ''));
    $email    = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phone    = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $message  = htmlspecialchars(trim($_POST['message'] ?? ''));

    if (!empty($fullname) && !empty($email) && !empty($message)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = "Thank you, $fullname! Your message has been received. Our showroom team will contact you within 24 hours.";
        } else {
            $error = "Please provide a valid email address.";
        }
    } else {
        $error = "Please fill in all required fields (*).";
    }
}
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Contact & Showroom</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Get in Touch</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Contact Information Column -->
        <div class="col-lg-5 mb-5 mb-lg-0 pr-lg-4">
            <span class="section-subtitle">Connect With Us</span>
            <h2 class="serif-heading mb-4">Visit Our Design Studio or Say Hello</h2>
            <p class="text-muted lead mb-4">
                Have questions about dimensions, wood finishes, or custom dimensions? Reach out to our design consultants or drop by our showroom.
            </p>

            <div class="card border-0 shadow-sm rounded-lg bg-white p-4 mb-4">
                <div class="d-flex align-items-start mb-4">
                    <div class="mr-3 text-warning font-weight-bold" style="font-size: 1.5rem;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Showroom Location</h6>
                        <p class="text-muted small mb-0">Main Boulevard, Lahore Cantt, Punjab 54000, Pakistan</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="mr-3 text-warning font-weight-bold" style="font-size: 1.5rem;">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Direct Phone & WhatsApp</h6>
                        <p class="text-muted small mb-0">+92 (300) 123-4567 / +92 (322) 498-7258</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="mr-3 text-warning font-weight-bold" style="font-size: 1.5rem;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Email Inquiries</h6>
                        <p class="text-muted small mb-0">support@furnicraft.store / sales@furnicraft.store</p>
                    </div>
                </div>

                <div class="d-flex align-items-start">
                    <div class="mr-3 text-warning font-weight-bold" style="font-size: 1.5rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Showroom Hours</h6>
                        <p class="text-muted small mb-0">Monday – Saturday: 09:00 AM – 08:00 PM<br>Sunday: By Appointment</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form Column -->
        <div class="col-lg-7">
            <div class="auth-card p-4 p-md-5">
                <div class="mb-4">
                    <h3 class="serif-heading font-weight-bold mb-1">Send Us a Message</h3>
                    <p class="text-muted small">We typically respond to inquiries within 2-4 business hours.</p>
                </div>

                <?php if ($msg): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo $msg; ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" placeholder="Full name" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase">Phone Number (Optional)</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 0300-1234567" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-muted small text-uppercase">Message / Custom Inquiry <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Tell us how we can help you with your furniture requirements..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" name="send_message" class="btn btn-accent-custom px-4 py-2 font-weight-bold shadow-sm">
                        <i class="fas fa-paper-plane mr-2"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="mt-5 pt-4 border-top">
        <h5 class="font-weight-bold mb-3"><i class="fas fa-map-marked-alt text-accent mr-2"></i> Find Our Showroom</h5>
        <div class="rounded-lg overflow-hidden shadow-sm border" style="border-radius: var(--radius-lg); height: 380px;">
            <iframe src="https://www.google.com/maps/embed?pb=!1m23!1m12!1m3!1d54420.52040063149!2d74.32475536753505!3d31.516399432441787!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m8!3e6!4m0!4m5!1s0x39190483e58107d9%3A0xc23abe6ccc7e2462!2sLahore%2C%20Pakistan!3m2!1d31.5203696!2d74.35874729999999!5e0!3m2!1sen!2s!4v1588778937037!5m2!1sen!2s" width="100%" height="100%" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
