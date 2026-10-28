<?php 
include('include/header.php'); 
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">About FurniCraft</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>Our Heritage</span>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Story Row -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="position-relative">
                <img src="img/about.png" alt="FurniCraft Workshop" class="img-fluid rounded-lg shadow-lg" style="border-radius: var(--radius-xl); object-fit: cover; max-height: 480px; width: 100%;">
                <div class="card border-0 shadow-md p-3 rounded-lg position-absolute d-none d-md-block" style="bottom: -20px; right: -20px; background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); max-width: 220px;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-medal text-warning fa-2x mr-3"></i>
                        <div>
                            <h6 class="font-weight-bold mb-0">15+ Years</h6>
                            <small class="text-muted">Master Woodworking</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 pl-lg-5">
            <span class="section-subtitle">Our Heritage</span>
            <h2 class="serif-heading mb-4">Dedicated to Pure Craftsmanship & Everyday Comfort</h2>
            <p class="text-muted lead" style="line-height: 1.8;">
                Welcome to FurniCraft, your premier destination for handcrafted home and executive office furnishings. We believe that furniture is not just functional decor—it shapes the memories and atmosphere of your daily life.
            </p>
            <p class="text-muted" style="line-height: 1.8;">
                Established with a vision to make timeless design accessible, FurniCraft combines seasoned hardwoods (Sheesham, Kikar, and Beechwood) with meticulous upholstery techniques. From solid wood bed sets and ergonomic dining arrangements to statement sofas, every piece is sculpted to last generations.
            </p>
            <div class="row mt-4">
                <div class="col-6 mb-3">
                    <h4 class="font-weight-bold text-accent mb-1">10,000+</h4>
                    <small class="text-muted font-weight-bold text-uppercase">Homes Furnished</small>
                </div>
                <div class="col-6 mb-3">
                    <h4 class="font-weight-bold text-accent mb-1">100%</h4>
                    <small class="text-muted font-weight-bold text-uppercase">Solid Timber Sourcing</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Pillars -->
    <div class="pt-5 mt-5 border-top">
        <div class="section-header">
            <span class="section-subtitle">Values</span>
            <h2 class="section-title">Why Customers Trust FurniCraft</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-tree"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Sustainable Materials</h4>
                    <p class="text-muted">We strictly source responsibly harvested, kiln-dried timber and eco-friendly finishes that safeguard indoor air quality.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-hands"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Artisan Handiwork</h4>
                    <p class="text-muted">Precision joinery, hand-finished surfaces, and high-density foam padding created by seasoned craftspeople.</p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <div class="feature-icon-wrap">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h4 class="serif-heading mb-2">Risk-Free Purchasing</h4>
                    <p class="text-muted">Enjoy complete peace of mind with Cash on Delivery nationwide, allowing you to inspect each piece upon doorstep delivery.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
