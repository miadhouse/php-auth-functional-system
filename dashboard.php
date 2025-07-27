<?php
// dashboard.php - User Dashboard
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'کاربر';
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Farsi Fahr | آموزش و آمادگی آزمون تئوری گواهینامه آلمانی" name="description">
    <link href="assets/images/favicon.svg" rel="shortcut icon" type="image/x-icon">
    <title>پنل کاربری | Farsi Fahr</title>
    <link href="assets/css/vendor/fontawesome.css" rel="stylesheet">
    <link href="assets/css/vendor/bootstrap.min.rtl.css" rel="stylesheet">
    <link href="assets/css/style.rtl.css" rel="stylesheet">
    <script src="assets/js/vendor/jquery.js"></script>
</head>
<body>
    <header class="tmp-header-area-start header-one header--sticky header--transparent sticky">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header-content">
                        <div class="logo">
                            <a href="index.html">
                                <img alt="Farsi Fahr" class="logo-dark" src="assets/images/logo/logoAsset%201.svg">
                                <img alt="Farsi Fahr" class="logo-white" src="assets/images/logo/logoAsset%201.svg">
                            </a>
                        </div>
                        <nav class="tmp-mainmenu-nav d-none d-xl-block">
                            <ul class="tmp-mainmenu">
                                <li><a href="index.php">خانه</a></li>
                                <li><a href="about.html">در مورد ما</a></li>
                                <li><a href="contact.html">تماس</a></li>
                                <li><a href="#" id="logoutBtn">خروج</a></li>
                            </ul>
                        </nav>
                        <div class="tmp-header-right">
                            <div class="user-profile">
                                <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-5 pt-5">
        <div class="row">
            <div class="col-12">
                <div class="dashboard-welcome">
                    <h2>خوش آمدید <?php echo htmlspecialchars($user_name); ?>!</h2>
                    <p>این صفحه پنل کاربری شما است. اینجا می‌توانید به امکانات و ویژگی‌های سایت دسترسی داشته باشید.</p>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-book fa-3x mb-3"></i>
                        <h4>آموزش‌ها</h4>
                        <p>دسترسی به تمام آموزش‌های تئوری گواهینامه</p>
                        <a href="#" class="btn btn-primary">مشاهده</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-clipboard-question fa-3x mb-3"></i>
                        <h4>آزمون‌های آزمایشی</h4>
                        <p>تمرین با سوالات مشابه آزمون اصلی</p>
                        <a href="#" class="btn btn-primary">شروع آزمون</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-chart-line fa-3x mb-3"></i>
                        <h4>گزارش پیشرفت</h4>
                        <p>مشاهده وضعیت یادگیری و پیشرفت</p>
                        <a href="#" class="btn btn-primary">مشاهده گزارش</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>آخرین فعالیت‌ها</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                مطالعه فصل ۳ آموزش علائم راهنمایی
                                <span class="badge bg-primary rounded-pill">دیروز</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                آزمون آزمایشی شماره ۲
                                <span class="badge bg-primary rounded-pill">۳ روز پیش</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                مرور قوانین اولویت عبور
                                <span class="badge bg-primary rounded-pill">هفته گذشته</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>آمار کلی</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6>درصد پیشرفت کلی</h6>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">65%</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6>آزمون‌های انجام شده</h6>
                                    <h3 class="mb-0">7 / 15</h3>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6>میانگین نمرات</h6>
                                    <h3 class="mb-0">78%</h3>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6>روزهای متوالی تمرین</h6>
                                    <h3 class="mb-0">5 روز</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-5 py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p>تمامی حقوق برای Farsi Fahr محفوظ است &copy; <?php echo date('Y'); ?></p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="assets/js/vendor/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // Logout functionality
        $('#logoutBtn').on('click', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'auth_controller.php',
                type: 'POST',
                data: {
                    action: 'logout',
                    csrf_token: '<?php echo generate_csrf_token(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = response.redirect;
                    }
                }
            });
        });
    });
    </script>
</body>
</html>