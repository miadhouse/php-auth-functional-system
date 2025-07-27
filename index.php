<?php
/**
 * Subscription Landing Page
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/subscription.php';

// Check for auto-login with remember token
if (!is_logged_in()) {
    check_remember_token();
}

// Get subscription plans for display
$subscription_plans = get_subscription_plans();

// Get plan comparison data
$plan_comparison = get_plan_comparison_data();

// Get testimonials from settings or database
$testimonials = [
    [
        'name' => 'Sarah Johnson',
        'company' => 'TechStart Inc.',
        'avatar' => 'assets/images/avatar1.jpg',
        'text' => 'This subscription service has transformed how we manage our projects. The Gold plan gives us everything we need.',
        'plan' => 'Gold Plan'
    ],
    [
        'name' => 'Mike Chen',
        'company' => 'Digital Solutions',
        'avatar' => 'assets/images/avatar2.jpg',
        'text' => 'Excellent value for money. The Silver plan is perfect for our growing team.',
        'plan' => 'Silver Plan'
    ],
    [
        'name' => 'Emily Rodriguez',
        'company' => 'Creative Agency',
        'avatar' => 'assets/images/avatar3.jpg',
        'text' => 'The Enterprise plan gives us the scalability and support we need for our large organization.',
        'plan' => 'Enterprise Plan'
    ]
];

// Page title
$page_title = SITE_NAME . ' - Choose Your Plan';

// Include header
?>
<html lang="fa">

<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Farsi Fahr | آموزش و آمادگی آزمون تئوری گواهینامه آلمانی" name="description">
    <link href="assets/images/favicon.svg" rel="shortcut icon" type="image/x-icon">
    <title>Farsi Fahr | آموزش و آمادگی آزمون تئوری گواهینامه آلمانی</title>
    <link href="assets/css/vendor/fontawesome.css" rel="stylesheet">
    <link href="assets/css/plugins/swiper.rtl.css" rel="stylesheet">
    <link href="assets/css/plugins/odometer.rtl.css" rel="stylesheet">
    <link href="assets/css/vendor/animate.min.css" rel="stylesheet">
    <link href="assets/css/vendor/bootstrap.min.rtl.css" rel="stylesheet">
    <link href="assets/css/style.rtl.css" rel="stylesheet">

</head>
<style>
    .modal-content {
        background-color: #2f506b !important;
        border-radius: 1rem !important;
        padding: 2rem !important;
    }
    .text-bronze{
    color: rgba(186, 107, 34, 1) !important;
}
.text-silver{
    color: rgb(184, 184, 184) !important;
}
.text-gold{
    color: rgb(255, 191, 0) !important;
}
</style>

<body data-csrf-token="<?= get_csrf_token() ?>">
    <header class="tmp-header-area-start header-one header--sticky header--transparent sticky">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header-content">
                        <div class="logo">
                            <a href="<?= BASE_URL ?>/index.php">
                                <img alt="Farsi Fahr" class="logo-dark" src="assets/images/logo/logoAsset%201.svg">
                                <img alt="Farsi Fahr" class="logo-white" src="assets/images/logo/logoAsset%201.svg">
                            </a>
                        </div>
                        <nav class="tmp-mainmenu-nav d-none d-xl-block">
                            <ul class="tmp-mainmenu">
                                <li class="nav-item">
                                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"
                                        href="index.php">
                                        <i class="bi bi-house me-1"></i>خانه
                                    </a>
                                </li>
                                <?php if (is_logged_in()): ?>
                                    <!-- Logged in user navigation -->
                                    <li class="nav-item">
                                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/user/') !== false ? 'active' : '' ?>"
                                            href="user/dashboard.php">
                                            <i class="bi bi-speedometer2 me-1"></i>داشبورد
                                        </a>
                                    </li>

                                    <?php if (has_active_subscription()): ?>
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" href="#" id="subscriptionDropdown" role="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-star me-1"></i>اشتراک من
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="subscriptionDropdown">
                                                <li><a class="dropdown-item" href="user/subscription.php">
                                                        <i class="bi bi-gear me-2"></i>مدیریت اشتراک
                                                    </a></li>
                                                <li><a class="dropdown-item" href="user/billing.php">
                                                        <i class="bi bi-credit-card me-2"></i>سابقه پرداخت
                                                    </a></li>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item" href="subscription/plans.php">
                                                        <i class="bi bi-arrow-up-circle me-2"></i>افزایش اشتراک
                                                    </a></li>
                                            </ul>
                                        </li>
                                    <?php else: ?>
                                        <li class="nav-item">
                                            <a class="nav-link text-warning" href="subscription/plans.php">
                                                <i class="bi bi-star me-1"></i>خرید اشتراک
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <!-- Guest navigation -->
                                    <li class="nav-item">
                                        <a class="nav-link" href="#pricing">
                                            <i class="bi bi-tags me-1"></i>تعرفه ها
                                        </a>
                                    </li>

                                <?php endif; ?>
                            </ul>
                        </nav>
                        <div class="tmp-header-right">
                            <div class="social-share-wrapper d-none d-md-block">
                                <div class="social-link"><a href="#"><i class="fa-brands fa-instagram"></i></a> <a
                                        href="#"><i class="fa-brands fa-linkedin-in"></i></a> <a href="#"><i
                                            class="fa-brands fa-twitter"></i></a> <a href="#"><i
                                            class="fa-brands fa-facebook-f"></i></a></div>
                            </div>
                            <div class="actions-area">
                                <div class="tmp-side-collups-area d-none d-xl-block">
                                    <button class="tmp-menu-bars tmp_button_active"><i
                                            class="fa-regular fa-bars-staggered"></i></button>
                                </div>
                                <div class="tmp-side-collups-area d-block d-xl-none">
                                    <button class="tmp-menu-bars humberger_menu_active"><i
                                            class="fa-regular fa-bars-staggered"></i></button>
                                </div>
                            </div>
                        </div>
                        
                <div class="d-flex align-items-center">
                    <?php if (is_logged_in()): ?>
                        <!-- Notification Bell -->
                        <div class="dropdown me-3">
                            <button type="button" class="btn btn-outline-secondary position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                <!-- Notification badge (you can implement notification logic) -->
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                    3
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><a class="dropdown-item" href="#">
                                    <div class="d-flex">
                                        <i class="bi bi-info-circle text-info me-2 mt-1"></i>
                                        <div>
                                            <div class="fw-bold">Welcome!</div>
                                            <small class="text-muted">Thanks for joining our platform</small>
                                        </div>
                                    </div>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="user/notifications.php">View All</a></li>
                            </ul>
                        </div>

                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <span class="d-none d-md-inline"><?= h($_SESSION['user_name']) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><h6 class="dropdown-header">
                                    <?= h($_SESSION['user_name']) ?>
                                    <?php if (has_active_subscription()): ?>
                                        <?php $subscription = get_user_subscription(); ?>
                                        <div class="small text-muted">
                                            <span class="badge bg-<?= $subscription['plan_type'] === 'gold' ? 'warning' : 'primary' ?> text-dark">
                                                <?= h($subscription['plan_name']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </h6></li>
                                <li><hr class="dropdown-divider"></li>
                                
                                <?php if (is_admin()): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">
                                        <i class="bi bi-shield-check me-2"></i>Admin Dashboard
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                
                                <li><a class="dropdown-item" href="user/dashboard.php">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="user/profile.php">
                                    <i class="bi bi-person me-2"></i>Profile Settings
                                </a></li>
                                
                                <?php if (has_active_subscription()): ?>
                                    <li><a class="dropdown-item" href="user/subscription.php">
                                        <i class="bi bi-star me-2"></i>Subscription
                                    </a></li>
                                    <li><a class="dropdown-item" href="user/billing.php">
                                        <i class="bi bi-credit-card me-2"></i>Billing
                                    </a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item text-warning" href="subscription/plans.php">
                                        <i class="bi bi-star me-2"></i>Upgrade Account
                                    </a></li>
                                <?php endif; ?>
                                
                                <li><a class="dropdown-item" href="user/security.php">
                                    <i class="bi bi-shield-lock me-2"></i>Security
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Guest User Buttons -->
                        <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="bi bi-person-plus me-1"></i>Start Free Trial
                        </button>
                    <?php endif; ?>
                </div>
                    </div>

                </div>
            </div>
        </div>
    </header>
      <!-- Subscription Status Banner (for logged in users) -->
    <?php if (is_logged_in()): ?>
        <?php $subscription = get_user_subscription(); ?>
        
        <?php if (!$subscription): ?>
            <!-- No active subscription -->
            <div class="bg-warning text-dark py-2">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No active subscription</strong> - Upgrade to unlock all features
                        </div>
                        <a href="subscription/plans.php" class="btn btn-dark btn-sm">
                            Choose Plan
                        </a>
                    </div>
                </div>
            </div>
        <?php elseif ($subscription['status'] === 'trial'): ?>
            <!-- Trial period -->
            <?php 
            $days_left = ceil((strtotime($subscription['trial_ends_at']) - time()) / 86400);
            ?>
            <div class="bg-info text-white py-2">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-gift me-2"></i>
                            <strong>Free Trial</strong> - <?= $days_left ?> days remaining
                        </div>
                        <a href="subscription/plans.php" class="btn btn-light btn-sm">
                            Subscribe Now
                        </a>
                    </div>
                </div>
            </div>
        <?php elseif ($subscription['status'] === 'cancelled'): ?>
            <!-- Cancelled subscription -->
            <?php 
            $days_left = ceil((strtotime($subscription['ends_at']) - time()) / 86400);
            ?>
            <div class="bg-danger text-white py-2">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <strong>Subscription Cancelled</strong> - Access ends in <?= $days_left ?> days
                        </div>
                        <a href="subscription/plans.php" class="btn btn-light btn-sm">
                            Reactivate
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php $flash_message = get_flash_message(); ?>
    <?php if ($flash_message): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $flash_message['type'] ?> alert-dismissible fade show" role="alert">
                <?= h($flash_message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="d-none d-xl-block">
        <div class="tmp-sidebar-area tmp_side_bar">
            <div class="inner">
                <div class="top-area"><a class="logo" href="index.html"> <img alt="Farsi - Fahr" class="logo-dark"
                            src="assets/images/logo/logoAsset%201.svg"> <img alt="Farsi - Fahr" class="logo-white"
                            src="assets/images/logo/logoAsset%201.svg"> </a>
                    <div class="close-icon-area">
                        <button class="tmp-round-action-btn close_side_menu_active"><i
                                class="fa-sharp fa-light fa-xmark"></i></button>
                    </div>
                </div>
                <div class="content-wrapper">
                    <div class="image-area-feature"><a href="index.html"> <img alt="personal-logo"
                                src="assets/images/logo/Designer.jpeg"> </a>
                    </div>
                    <h5 class="title mt--30">
                        با افتخار آموزگار ، مترجم ، پشتیبان و پارتنر شما در پروسه اخذ گواهینامه آلمانی هستیم
                    </h5>
                    <p class="disc">

                    </p>
                    <div class="short-contact-area">
                        <div class="single-contact"><i class="fa-solid fa-phone"></i>
                            <div class="information tmp-link-animation"><span>تماس واتس اپپ</span> <a class="number"
                                    href="#">004917661812772</a>
                            </div>
                        </div>
                        <div class="single-contact"><i class="fa-solid fa-envelope"></i>
                            <div class="information tmp-link-animation"><span>با ما توسط ایمیل ارتباط بگیرید</span> <a
                                    class="number" href="#">admin@farsiapp.de</a></div>
                        </div>
                        <!--       <div class="single-contact"><i class="fa-solid fa-location-crosshairs"></i>
                               <div class="information tmp-link-animation"><span>آدرس من</span> <span class="number">66 بروکلین ، نیویورک 3269</span>
                               </div>
                           </div>-->
                    </div>
                    <div class="social-wrapper mt--20"><span class="subtitle">یا در فضای مجازی دنبال کنید</span>
                        <div class="social-link"><a href="#"><i class="fa-brands fa-instagram"></i></a> <a href="#"><i
                                    class="fa-brands fa-linkedin-in"></i></a> <a href="#"><i
                                    class="fa-brands fa-twitter"></i></a> <a href="#"><i
                                    class="fa-brands fa-facebook-f"></i></a></div>
                    </div>
                </div>
            </div>
        </div>
        <a class="overlay_close_side_menu close_side_menu_active" href="javascript:void(0);"></a>
    </div>
    <div class="d-block d-xl-none">
        <div class="tmp-popup-mobile-menu">
            <div class="inner">
                <div class="header-top">
                    <div class="logo"><a class="logo-area" href="index.html"> <img alt="Farsi - Fahr" class="logo-dark"
                                src="assets/images/logo/logoAsset%201.svg"> <img alt="Farsi - Fahr" class="logo-white"
                                src="assets/images/logo/logoAsset%201.svg"> </a></div>
                    <div class="close-menu">
                        <button class="close-button tmp-round-action-btn"><i class="fa-sharp fa-light fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <ul class="tmp-mainmenu">
                    <li><a href="#">خانه</a></li>
                    <li><a href="about.html">در مورد</a></li>
                    <li><a href="contact.html">تماس</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="rpp-banner-two-area">
        <div class="container">
            <div class="banner-two-main-wrapper">
                <div class="row align-items-center">
                    <div class="col-lg-6 order-lg-2">
                        <div class="banner-right-content">
                            <div class="main-img"><img alt="banner-img"
                                    class="tmp-scroll-trigger tmp-zoom-in animation-order-1"
                                    src="assets/images/banner/banner-user-image-two2.png">
                                <h2 class="banner-big-text-1 up-down-2">FARSI-FAHR</h2>
                                <h2 class="banner-big-text-2 up-down">FARSI-FAHR</h2>
                                <div class="benner-two-bg-red-img"><img alt="red-img"
                                        src="assets/images/banner/banner-user-image-two-red-bg.png">
                                </div>
                                <div class="logo-under-img-wrap">
                                    <div class="logo-under-img"><img alt="logo-under-image" style="opacity: .3"
                                            src="assets/images/banner/logo-under-image.png"></div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 order-lg-1 mt--100">
                        <div class="inner"><span class="sub-title tmp-scroll-trigger tmp-fade-in animation-order-1">سلام
                                دوست من !</span>
                            <h1 class="title tmp-scroll-trigger tmp-fade-in animation-order-2">گواهینامه آلمانی سخت
                                نیست،
                                چون اینجا <br>
                                <span class="header-caption">
                                    <span class="cd-headline clip is-full-width">
                                        <span class="cd-words-wrapper" style="width: 107.73px; overflow: hidden;">
                                            <b class="theme-gradient is-visible">ترجمه اختصاصی</b>
                                            <b class="theme-gradient is-hidden">آموزش فارسی</b>
                                            <b class="theme-gradient is-hidden">برنامه ریزی</b>
                                            <b class="theme-gradient is-hidden">تمرین واژه ها</b>
                                            <b class="theme-gradient is-hidden">مدل امتحان</b>
                                            <b class="theme-gradient is-hidden">پشتیبانی 24 ساعته</b>
                                        </span>
                                    </span>
                                </span> داریم
                            </h1>
                            <p class="disc tmp-scroll-trigger tmp-title-split tmp-fade-in animation-order-3">
                                <span>کنار شما هستیم</span>
                                تا حتی با داشتن سطح زبان آلمانی پایین بتونید در مدت کوتاه برای
                                <span>آزمون تئوری گواهینامه رانندگی آلمانی </span>
                                آماده بشید!
                            </p>
                            <div class="button-area-banner-two tmp-scroll-trigger tmp-fade-in animation-order-4"><a
                                    class="tmp-btn hover-icon-reverse radius-round" href="#"> <span
                                        class="icon-reverse-wrapper"> <span class="btn-text">اطلاعات بیشتر در مورد
                                            ما</span> <span class="btn-icon"><i
                                                class="fa-sharp fa-regular fa-arrow-left"></i></span> <span
                                            class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-left"></i></span>
                                    </span> </a></div>
                            <div class="find-me-on tmp-scroll-trigger tmp-fade-in animation-order-5">
                                <h2 class="find-me-on-title">ما رو دنبال کن</h2>
                                <div class="social-link banner"><a href="#"><i class="fa-brands fa-instagram"></i></a>
                                    <a href="#"><i class="fa-brands fa-linkedin-in"></i></a> <a href="#"><i
                                            class="fa-brands fa-twitter"></i></a> <a href="#"><i
                                            class="fa-brands fa-facebook-f"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="banner-shape-two"><img alt="" src="assets/images/banner/banner-shape-two.png"></div>
    </div>
    <div class="about-content-area">
        <div class="container tmp-section-gap">
            <div class="text-para-doc-wrap">
                <h2 class="text-para-documents tmp-scroll-trigger tmp-fade-in tmp-title-split-2 animation-order-1">
                    پروسه
                    <span>
                        یادگیری و آمادگی
                    </span>
                    برای آزمون گواهینامه آلمانی
                    برای فارسی زبانان همیشه سخت، هزینه بر و طاقت فرسا بوده است.
                    ترجمه و مشاهده ویدیو های آموزشی همچنین زمان بر
                    و دسترسی به آن ها آسان نیست، با در نظر گرفتن تمام
                    این مشکلات امروز می توانید با

                    <span>
                        متد جدید این سامانه
                    </span>
                    با حداقل مشکلات سابق این مسیر را پشت سر بگذارید.
                </h2>
                <div class="left-bg-text-para"><img alt="" src="assets/images/banner/right-bg-text-para-doc.png"></div>
                <div class="right-bg-text-para"><img alt="" src="assets/images/banner/left-bg-text-para-doc.png"></div>
            </div>
        </div>
    </div>
    <section class="about-us-area">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-us-left-content-wrap bg-vactor-one">
                        <div class="years-of-experience-card tmp-scroll-trigger tmp-fade-in animation-order-1">
                            <h2 class="counter card-title ">
                                <span class="odometer ltr" data-count="3500">00</span>+
                            </h2>
                            <p class="card-para">توضیح فارسی برای پاسخ های صحیح و غلط</p>
                        </div>
                        <div class="design-card tmp-scroll-trigger tmp-fade-in animation-order-2">
                            <div class="design-card-img">
                                <div class="icon"><i class="fa-sharp fa-thin fa-lock"></i></div>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title">آموزش های اساسی</h3>
                                <p class="card-para">241 آموزش</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-us-right-content-wrap">
                        <div class="section-head text-align-left mb--50">
                            <div class="section-sub-title tmp-scroll-trigger tmp-fade-in animation-order-1"><span
                                    class="subtitle">استراتژی ما</span></div>
                            <h2 class="title split-collab tmp-scroll-trigger tmp-fade-in animation-order-2">زمان کوتاه
                                با
                                حداقل سطح زبان</h2>
                            <p class="description tmp-scroll-trigger tmp-fade-in animation-order-3"></p>
                        </div>
                        <div class="about-us-section-card row g-5">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="about-us-card tmponhover tmp-scroll-trigger tmp-fade-in animation-order-4"
                                    style="--x: 146px; --y: 20px;">
                                    <div class="card-head">
                                        <div class="logo-img"><img alt="logo" src="assets/images/about/logo-1.svg">
                                        </div>
                                        <h3 class="card-title"> کوتاه ترین زمان</h3>
                                    </div>
                                    <p class="card-para">با تحقیق از بین صدها متقاضی گواهینامه در ساله های گذشته، طبق یک
                                        فرومول بسیار جذاب، میتوانیم قبل از شروع زمان مورد نیاز برای آمادگی را با توجه به
                                        سطح
                                        زبان و اطلاعات شما تخمین بزنیم.</p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="about-us-card tmponhover tmp-scroll-trigger tmp-fade-in animation-order-5">
                                    <div class="card-head">
                                        <div class="logo-img"><img alt="logo" src="assets/images/about/logo-2.svg">
                                        </div>
                                        <h3 class="card-title">مشکل سطح زبان </h3>
                                    </div>
                                    <p class="card-para">با پروسه تمرین و تکرار، یادگیری کلمات و یک تمرین ثابت طبق
                                        برنامه،
                                        مشکل پایین بودن سطح زبان آلمانی شما برای یادگیری سوالات را به حداقل می رسانیم.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="about-btn mt--40 tmp-scroll-trigger tmp-fade-in animation-order-6 tmp-scroll-trigger--offscreen">
                            <a class="tmp-btn hover-icon-reverse radius-round" href="about.html"> <span
                                    class="icon-reverse-wrapper"> <span class="btn-text">همین حالا رایگان تست
                                        کنید</span> <span class="btn-icon"><i
                                            class="fa-sharp fa-regular fa-arrow-left"></i></span> <span
                                        class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-left"></i></span>
                                </span> </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tpm My Price plan Start -->
    <section class="our-price-plan-area tmp-section-gapTop">
        <div class="container">
            <div class="section-head mb--50">
                <div class="section-sub-title center-title tmp-scroll-trigger tmp-fade-in animation-order-1">
                    <span class="subtitle">جدول اشتراک ها</span>
                </div>
                <h2 class="title split-collab tmp-scroll-trigger tmp-fade-in animation-order-2">قیمت‌گذاری ساده و شفاف</h2>
                <p>طرحی را انتخاب کنید که به بهترین وجه با نیازهای شما مطابقت داشته باشد. همه طرح‌ها شامل ویژگی‌های اصلی ما هستند.

</p>
 <div class="d-flex justify-content-center align-items-center mb-4">
<!-- From Uiverse.io by bandirevanth --> 
<label class="rocker rocker-small">
    <input id="billingToggle" type="checkbox">
    <span class="switch-left">سالیانه </span>
        <span class="switch-right">ماهیانه</span>
</label>
            <span class="ms-3"> <span class="badge bg-success">20% ارزان تر</span></span>
        </div>
            </div>
            <div class="row align-items-center">
                        <?php foreach ($plan_comparison as $plan): ?>
                <div class="col-lg-<?= count($plan_comparison) > 4 ? '3' : (12 / count($plan_comparison)) ?> col-md-6">
                    <div  style="--x: 387px; --y: 102px;<?= $plan['plan_type'] === 'silver' ? 'border: 2px solid #0071ff !important;' : '' ?>" 
                    class=" price-plan-card tmponhover blur-style-two tmp-scroll-trigger tmp-fade-in animation-order-1 active <?= $plan['plan_type'] === 'silver' ? ' border-primary ' : '' ?>">
                     <?php if ($plan['plan_type'] === 'silver'): ?>
                        <div style="top:10px" class="position-absolute start-50 translate-middle">
                            <span class="badge bg-primary text-dark px-3 py-2">پرطرفدارترین</span>
                        </div>
                    <?php endif; ?>
                        <span class="text-<?= $plan['plan_type'] ?>"><?= h($plan['name']) ?></span>
                        <div class="pricing-display mb-4">
                            <div class="monthly-price">
                                <span class="h2 fw-bold"><?= number_format($plan['price_monthly'], 0) ?><span class=" fs-5"> یورو</span></span>
                                <span class="text-muted">/ماهیانه</span>
                            </div>
                            <div class="yearly-price d-none">
                                <span class="h2 fw-bold">$<?= number_format($plan['price_yearly'], 0) ?></span>
                                <span class="text-muted">/سالیانه</span>
                                <div class="small text-success">
                                <?= number_format(($plan['price_monthly'] * 12) - $plan['price_yearly'], 0) ?><span class=" fs-5"> یورو</span>     در سال ذخیره کنید 
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($plan['trial_days'] > 0): ?>
                            <p class="text-success small mb-3">
                                <i class="bi bi-gift-fill me-1"></i>
                                <?= $plan['trial_days'] ?>-روز بیشتر هدیه بگیرید
                            </p>
                        <?php endif; ?>

                        <div class="check-box">
                            <ul>
                            <?php foreach ($plan['features'] as $feature): ?>
                            
                                <li>
                                    <div class="check-box-item">
                                        <div class="box-icon">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </div>
                                        <p class="box-para"><?= h($feature) ?></p>
                                    </div>
                                </li>
                                            <?php endforeach; ?>

                            </ul>
                        </div>
                        <div class="tmp-button-here">
                            <a class="tmp-btn hover-icon-reverse btn-<?= $plan['plan_type'] === 'silver' ? 'primary' : ''?> btn-border btn-md radius-round" href="contact.html">
                                <span class="icon-reverse-wrapper">
                                    <span class="btn-text ">شروع کن</span>
                                    <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                                    <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Tpm My Price plan End -->

    <section class="blog-and-news-are tmp-section-gap">
        <div class="container">
            <div class="section-head mb--50">
                <div
                    class="section-sub-title center-title tmp-scroll-trigger tmp-fade-in animation-order-1 tmp-scroll-trigger--offscreen">
                    <span class="subtitle">آخرین وبلاگ</span>
                </div>
                <h2
                    class="title split-collab tmp-scroll-trigger tmp-fade-in animation-order-2 tmp-scroll-trigger--offscreen">
                    اطلاعات اولیه و<br>
                    آموزش های رایگان
                </h2>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 col-12">
                    <div
                        class="blog-card-style-two tmponhover image-box-hover tmp-scroll-trigger tmp-fade-in animation-order-3 tmp-scroll-trigger--offscreen">
                        <div class="blog-card-img">
                            <div class="img-box"><a href="blog-details.html"> <img alt="Blog Thumbnail" class="w-100"
                                        src="assets/images/blog/blog-img-1.jpg">
                                </a></div>
                            <span>12 دی</span>
                        </div>
                        <div class="blog-content-wrap">
                            <div class="blog-tags">
                                <ul>
                                    <li><a href="#"><i class="fa-regular fa-user"></i>مسبز</a></li>
                                    <li><a href="#"><i class="fa-regular fa-comments"></i>نظرات (05)</a></li>
                                </ul>
                            </div>
                            <h3 class="blog-title"><a href="blog-details.html">از کجا شروع کنم برای اخذ گواهینامه؟</a>
                            </h3>
                            <div class="read-more-btn"><a
                                    class="tmp-btn hover-icon-reverse radius-round btn-border btn-md"
                                    href="blog-details.html"> <span class="icon-reverse-wrapper"> <span
                                            class="btn-text">بیشتر بخوانید</span> <span class="btn-icon"><i
                                                class="fa-sharp fa-regular fa-arrow-left"></i></span> <span
                                            class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-left"></i></span>
                                    </span> </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div
                        class="blog-card-style-two tmponhover image-box-hover tmp-scroll-trigger tmp-fade-in animation-order-2 tmp-scroll-trigger--offscreen">
                        <div class="blog-card-img">
                            <div class="img-box"><a href="blog-details.html"> <img alt="Blog Thumbnail" class="w-100"
                                        src="assets/images/blog/blog-img-2.jpg">
                                </a></div>
                            <span>12 دی</span>
                        </div>
                        <div class="blog-content-wrap">
                            <div class="blog-tags">
                                <ul>
                                    <li><a href="#"><i class="fa-regular fa-user"></i>مسبز</a></li>
                                    <li><a href="#"><i class="fa-regular fa-comments"></i>نظرات (05)</a></li>
                                </ul>
                            </div>
                            <h3 class="blog-title"><a href="blog-details.html">مراحل و قوانین ترجمه گواهینامه ایران</a>
                            </h3>
                            <div class="read-more-btn"><a
                                    class="tmp-btn hover-icon-reverse radius-round btn-border btn-md"
                                    href="blog-details.html"> <span class="icon-reverse-wrapper"> <span
                                            class="btn-text">بیشتر بخوانید</span> <span class="btn-icon"><i
                                                class="fa-sharp fa-regular fa-arrow-left"></i></span> <span
                                            class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-left"></i></span>
                                    </span> </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div
                        class="blog-card-style-two tmponhover image-box-hover tmp-scroll-trigger tmp-fade-in animation-order-3 tmp-scroll-trigger--offscreen">
                        <div class="blog-card-img">
                            <div class="img-box"><a href="blog-details.html"> <img alt="Blog Thumbnail" class="w-100"
                                        src="assets/images/blog/blog-img-3.jpg">
                                </a></div>
                            <span>12 دی</span>
                        </div>
                        <div class="blog-content-wrap">
                            <div class="blog-tags">
                                <ul>
                                    <li><a href="#"><i class="fa-regular fa-user"></i>مسبز</a></li>
                                    <li><a href="#"><i class="fa-regular fa-comments"></i>نظرات (05)</a></li>
                                </ul>
                            </div>
                            <h3 class="blog-title"><a href="blog-details.html">راهکارهایی برای کاهش استرس در امتحان</a>
                            </h3>
                            <div class="read-more-btn"><a
                                    class="tmp-btn hover-icon-reverse radius-round btn-border btn-md"
                                    href="blog-details.html"> <span class="icon-reverse-wrapper"> <span
                                            class="btn-text">بیشتر بخوانید</span> <span class="btn-icon"><i
                                                class="fa-sharp fa-regular fa-arrow-left"></i></span> <span
                                            class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-left"></i></span>
                                    </span> </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer-area footer-style-two-wrapper bg-color-footer bg_images tmp-section-gap">
        <div class="container">
            <div class="footer-main footer-style-two">
                <div class="row g-5">
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="single-footer-wrapper border-right mr--20">
                            <div class="logo"><a href="index.html"> <img alt="Farsi - Fahr"
                                        src="assets/images/logo/logoAsset%201.svg"> </a></div>
                            <p class="description">در راستای حمایت از فارسی زبانان عزیز در کشور آلمان جهت تسهیل فرایند
                                قبولی
                                در آزمون تئوری گواهینامه، برآن شدیم سامانه ای جامع و کامل آماده کنیم که به آخرین بانک
                                سوالات
                                به روز باشد و پس از ترجمه اختصاصی سوالات بدون سیستم های مترجم آنلاین در بحث آموزش به
                                زبان
                                فارسی در تک تک پاسخ ها نیز مجهز باشد تا این مسیر برای همه هموار شود.</p>
                            <div class="social-link footer"><a href="#"><i class="fa-brands fa-instagram"></i></a> <a
                                    href="#"><i class="fa-brands fa-linkedin-in"></i></a> <a href="#"><i
                                        class="fa-brands fa-twitter"></i></a> <a href="#"><i
                                        class="fa-brands fa-facebook-f"></i></a></div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="quick-link-wrap">
                            <h5 class="ft-title">لینک سریع</h5>
                            <ul
                                class="ft-link tmp-scroll-trigger animation-order-1 tmp-link-animation tmp-scroll-trigger--offscreen">
                                <li><a href="about.html">درباره ما</a></li>
                                <li><a href="team.html">خدمت</a></li>
                                <li><a href="contact.html">قیمت گذاری</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="single-footer-wrapper contact-wrap">
                            <h5 class="ft-title">تماس</h5>
                            <ul
                                class="ft-link tmp-scroll-trigger animation-order-1 tmp-link-animation tmp-scroll-trigger--offscreen">
                                <li><span class="ft-icon"><i class="fa-solid fa-phone"></i></span><a
                                        href="#">004917661812772</a>
                                </li>
                                <li><span class="ft-icon"><i class="fa-solid fa-envelope"></i></span><a
                                        href="#">admin@farsi-app.de</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="newslatter tmp-scroll-trigger animation-order-1 tmp-scroll-trigger--offscreen">
                            <h3 class="title">خبرنامه</h3>
                            <p class="para">از آخرین تغییرات ما در لحظه با خبر باشید</p>
                            <form action="#" class="newsletter-form-1"><input placeholder="ایمیل شما" type="email">
                                <span> <a class="form-icon" href="#"><i class="fa-solid fa-arrow-left"></i></a> </span>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <div class="copyright-area-one">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="main-wrapper tmp-scroll-trigger animation-order-1 tmp-scroll-trigger--offscreen">
                        <p class="copy-right-para">© FARSI-APP
                            <script>
                                document.write(new Date().getFullYear())
                            </script>
                            2025 | کلیه حقوق محفوظ است
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="ready-chatting-option tmp-ready-chat chat-visible"><input id="click" type="checkbox"> <label
            for="click"> <i class="fab fa-facebook-messenger"></i> <i class="fas fa-times"></i> </label>
        <div class="wrapper">
            <div class="head-text">بیایید با من گپ بزنیم؟- آنلاین</div>
            <div class="chat-box">
                <div class="desc-text">لطفاً فرم زیر را پر کنید تا مستقیماً با من گپ بزنید.</div>
                <form action="#" class="tmp-dynamic-form">
                    <div class="field"><input class="input-field" name="name" placeholder="نام شما" required=""
                            type="text">
                    </div>
                    <div class="field"><input class="input-field" name="email" placeholder="ایمیل شما" required=""
                            type="email"></div>
                    <div class="field textarea"><textarea class="input-field" name="message" placeholder="پیام شما"
                            required=""></textarea></div>
                    <div class="field">
                        <button name="submit" type="submit">ارسال پیام</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="scrollToTop active-progress" style="display: block;">
        <div class="arrowUp"><i class="fa-light fa-arrow-up"></i></div>
        <div class="water" style="transform: translate(0px, 91%);">
            <svg class="water_wave water_wave_back" viewBox="0 0 560 20">
                <use xlink:href="#wave"></use>
            </svg>
            <svg class="water_wave water_wave_front" viewBox="0 0 560 20">
                <use xlink:href="#wave"></use>
            </svg>
            <svg style="display: none;" version="1.1" viewBox="0 0 560 20" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
                <symbol id="wave">
                    <path
                        d="M420,20c21.5-0.4,38.8-2.5,51.1-4.5c13.4-2.2,26.5-5.2,27.3-5.4C514,6.5,518,4.7,528.5,2.7c7.1-1.3,17.9-2.8,31.5-2.7c0,0,0,0,0,0v20H420z"
                        fill="#FF014F"
                        style="transition: stroke-dashoffset 10ms linear; stroke-dasharray: 301.839, 301.839; stroke-dashoffset: 301.839px;">
                    </path>
                    <path
                        d="M420,20c-21.5-0.4-38.8-2.5-51.1-4.5c-13.4-2.2-26.5-5.2-27.3-5.4C326,6.5,322,4.7,311.5,2.7C304.3,1.4,293.6-0.1,280,0c0,0,0,0,0,0v20H420z"
                        fill="#FF014F"></path>
                    <path
                        d="M140,20c21.5-0.4,38.8-2.5,51.1-4.5c13.4-2.2,26.5-5.2,27.3-5.4C234,6.5,238,4.7,248.5,2.7c7.1-1.3,17.9-2.8,31.5-2.7c0,0,0,0,0,0v20H140z"
                        fill="#FF014F"></path>
                    <path
                        d="M140,20c-21.5-0.4-38.8-2.5-51.1-4.5c-13.4-2.2-26.5-5.2-27.3-5.4C46,6.5,42,4.7,31.5,2.7C24.3,1.4,13.6-0.1,0,0c0,0,0,0,0,0l0,20H140z"
                        fill="#FF014F"></path>
                </symbol>
            </svg>
        </div>
    </div>
    <?php require_once __DIR__ . '/partials/modals.php'; ?>

    <script src="assets/js/vendor/jquery.js"></script>
    <script src="assets/js/vendor/jquery-ui.min.js"></script>
    <script src="assets/js/vendor/waypoints.min.js"></script>
    <script src="assets/js/plugins/odometer.js"></script>
    <script src="assets/js/vendor/appear.js"></script>
    <script src="assets/js/vendor/jquery-one-page-nav.js"></script>
    <script src="assets/js/plugins/swiper.js"></script>
    <script src="assets/js/plugins/gsap.js"></script>
    <script src="assets/js/plugins/splittext.js"></script>
    <script src="assets/js/plugins/scrolltigger.js"></script>
    <script src="assets/js/plugins/scrolltoplugins.js"></script>
    <script src="assets/js/plugins/smoothscroll.js"></script>
    <script src="assets/js/vendor/bootstrap.min.js"></script>
    <script src="assets/js/vendor/waw.js"></script>
    <script src="assets/js/plugins/isotop.js"></script>
    <script src="assets/js/plugins/animation.js"></script>
    <script src="assets/js/plugins/contact.form.js"></script>
    <script src="assets/js/vendor/backtop.js"></script>
    <script src="assets/js/plugins/text-type.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/main3.js"></script>
|
<!-- Billing Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const billingToggle = document.getElementById('billingToggle');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const yearlyPrices = document.querySelectorAll('.yearly-price');
    
    billingToggle.addEventListener('change', function() {
        if (this.checked) {
            // Show yearly prices
            monthlyPrices.forEach(price => price.classList.add('d-none'));
            yearlyPrices.forEach(price => price.classList.remove('d-none'));
        } else {
            // Show monthly prices
            monthlyPrices.forEach(price => price.classList.remove('d-none'));
            yearlyPrices.forEach(price => price.classList.add('d-none'));
        }
    });
    
    // Handle plan selection in registration modal
    const registerButtons = document.querySelectorAll('[data-plan]');
    registerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const planSlug = this.dataset.plan;
            // Store selected plan in session storage for later use
            sessionStorage.setItem('selectedPlan', planSlug);
        });
    });
});
</script>
</body>

</html>