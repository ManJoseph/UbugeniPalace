<?php
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirectTo(SITE_URL . '/pages/login.php');
}

$current_user = getCurrentUser();

// Get dashboard statistics
$total_users = $db->rowCount("SELECT COUNT(*) FROM users WHERE user_type != 'admin'");
$total_artisans = $db->rowCount("SELECT COUNT(*) FROM users WHERE user_type = 'artisan'");
$total_products = $db->rowCount("SELECT COUNT(*) FROM products WHERE status = 'active'");
$total_orders = $db->rowCount("SELECT COUNT(*) FROM orders");
$pending_orders = $db->rowCount("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$pending_password_requests = $db->rowCount("SELECT COUNT(*) FROM password_reset_requests WHERE status = 'pending'");
$unread_notifications = $db->rowCount("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0");

// Get recent password reset requests
$recent_password_requests = $db->fetchAll(
    "SELECT prr.*, u.full_name, u.user_type 
     FROM password_reset_requests prr 
     JOIN users u ON prr.user_id = u.id 
     WHERE prr.status = 'pending' 
     ORDER BY prr.created_at DESC 
     LIMIT 10"
);

// Get recent notifications
$recent_notifications = $db->fetchAll(
    "SELECT * FROM admin_notifications 
     ORDER BY created_at DESC 
     LIMIT 10"
);

$page_title = 'Admin Dashboard - ' . SITE_NAME;
?>

<!DOCTYPE html>
<html lang="rw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="Admin dashboard for UbugeniPalace">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo/favicon.png">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
    
    <style>
        .admin-main {
            min-height: 100vh;
            background: var(--background-light);
        }
        
        .admin-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: var(--spacing-lg) 0;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .admin-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 var(--spacing-xl);
        }
        
        .admin-logo {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }
        
        .admin-logo .logo-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .admin-logo .logo-circle::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .admin-logo .logo {
            width: 25px;
            height: 25px;
            filter: brightness(0) invert(1);
            z-index: 1;
        }
        
        .admin-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }
        
        .admin-user-info {
            text-align: right;
        }
        
        .admin-user-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .admin-user-role {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .admin-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: var(--spacing-3xl) 0;
            position: relative;
            overflow: hidden;
        }
        
        .admin-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .admin-hero-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 var(--spacing-xl);
            position: relative;
            z-index: 1;
        }
        
        .admin-hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: var(--spacing-md);
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-hero-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: var(--spacing-xl);
        }
        
        .admin-hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--spacing-lg);
            margin-top: var(--spacing-xl);
        }
        
        .hero-stat {
            text-align: center;
            padding: var(--spacing-lg);
            background: rgba(255,255,255,0.1);
            border-radius: var(--border-radius-lg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .hero-stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--spacing-xs);
        }
        
        .hero-stat-label {
            font-size: 0.875rem;
            opacity: 0.8;
        }
        
        .admin-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-3xl) var(--spacing-xl);
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-2xl);
            margin-top: var(--spacing-2xl);
        }
        
        .admin-card {
            background: white;
            border-radius: var(--border-radius-xl);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .admin-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }
        
        .admin-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid var(--border-color);
        }
        
        .admin-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .admin-card-action {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-sm);
            transition: background 0.3s ease;
        }
        
        .admin-card-action:hover {
            background: var(--background-light);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-2xl);
        }
        
        .stat-card {
            background: white;
            border-radius: var(--border-radius-xl);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: var(--spacing-md);
            display: block;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: var(--spacing-xs);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .request-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-md);
            background: var(--background-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .request-item:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
        }
        
        .request-info {
            flex: 1;
        }
        
        .request-user {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }
        
        .request-details {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .request-actions {
            display: flex;
            gap: var(--spacing-sm);
        }
        
        .btn-approve {
            background: var(--success);
            color: white;
            border: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-approve:hover {
            background: var(--success-dark);
            transform: translateY(-1px);
        }
        
        .btn-reject {
            background: var(--error);
            color: white;
            border: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-reject:hover {
            background: var(--error-dark);
            transform: translateY(-1px);
        }
        
        .notification-item {
            padding: var(--spacing-lg);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-md);
            background: var(--background-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .notification-item:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
        }
        
        .notification-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }
        
        .notification-message {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-xs);
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-secondary);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: var(--spacing-lg);
            opacity: 0.5;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-2xl);
        }
        
        .action-card {
            background: white;
            border-radius: var(--border-radius-xl);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        
        .action-icon {
            font-size: 2.5rem;
            margin-bottom: var(--spacing-md);
            display: block;
        }
        
        .action-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
        }
        
        .action-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        @media (max-width: 1024px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-hero-title {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .admin-header-content {
                flex-direction: column;
                gap: var(--spacing-md);
                text-align: center;
            }
            
            .admin-user {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .admin-hero-title {
                font-size: 1.75rem;
            }
            
            .admin-hero-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-hero-stats {
                grid-template-columns: 1fr;
            }
            
            .request-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-md);
            }
            
            .request-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="admin-main">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="admin-header-content">
                <div class="admin-logo">
                    <div class="logo-circle">
                        <img src="../assets/images/logo/logo.png" alt="Logo" class="logo">
                    </div>
                    <h1 class="admin-title">Admin Dashboard</h1>
                </div>
                
                <div class="admin-user">
                    <div class="admin-user-info">
                        <div class="admin-user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
                        <div class="admin-user-role">Administrator</div>
                    </div>
                    <a href="../pages/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </header>

        <!-- Admin Hero Section -->
        <section class="admin-hero">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Welcome back, <?php echo htmlspecialchars($current_user['full_name']); ?>!</h1>
                <p class="admin-hero-subtitle">Manage your artisan marketplace and monitor system activity</p>
                
                <div class="admin-hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-number"><?php echo $total_users; ?></div>
                        <div class="hero-stat-label">Total Users</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number"><?php echo $total_artisans; ?></div>
                        <div class="hero-stat-label">Artisans</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number"><?php echo $total_products; ?></div>
                        <div class="hero-stat-label">Products</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-number"><?php echo $total_orders; ?></div>
                        <div class="hero-stat-label">Orders</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Admin Content -->
        <main class="admin-content">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="window.location.href='manage-users.php'">
                    <div class="action-icon">👥</div>
                    <h3 class="action-title">Manage Users</h3>
                    <p class="action-description">View and manage all registered users and artisans</p>
                </div>
                
                <div class="action-card" onclick="window.location.href='manage-products.php'">
                    <div class="action-icon">🛍️</div>
                    <h3 class="action-title">Manage Products</h3>
                    <p class="action-description">Review and approve product listings</p>
                </div>
                
                <div class="action-card" onclick="window.location.href='manage-orders.php'">
                    <div class="action-icon">📦</div>
                    <h3 class="action-title">Manage Orders</h3>
                    <p class="action-description">Track and process customer orders</p>
                </div>
                
                <div class="action-card" onclick="window.location.href='manage-password-requests.php'">
                    <div class="action-icon">🔐</div>
                    <h3 class="action-title">Password Requests</h3>
                    <p class="action-description">Handle password reset requests</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎨</div>
                    <div class="stat-number"><?php echo $total_artisans; ?></div>
                    <div class="stat-label">Artisans</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛍️</div>
                    <div class="stat-number"><?php echo $total_products; ?></div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number"><?php echo $pending_orders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔐</div>
                    <div class="stat-number"><?php echo $pending_password_requests; ?></div>
                    <div class="stat-label">Password Requests</div>
                </div>
            </div>

            <!-- Admin Grid -->
            <div class="admin-grid">
                <!-- Password Reset Requests -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Password Reset Requests</h2>
                        <a href="manage-password-requests.php" class="admin-card-action">View All</a>
                    </div>
                    
                    <?php if (empty($recent_password_requests)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🔐</div>
                            <p>No pending password reset requests</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_password_requests as $request): ?>
                            <div class="request-item">
                                <div class="request-info">
                                    <div class="request-user">
                                        <?php echo htmlspecialchars($request['full_name']); ?> 
                                        <span style="color: var(--text-secondary); font-weight: normal;">
                                            (<?php echo htmlspecialchars($request['user_type']); ?>)
                                        </span>
                                    </div>
                                    <div class="request-details">
                                        <?php echo htmlspecialchars($request['email']); ?> • 
                                        Requested: <?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="request-actions">
                                    <button class="btn-approve" onclick="approvePasswordRequest(<?php echo $request['id']; ?>)">
                                        Approve
                                    </button>
                                    <button class="btn-reject" onclick="rejectPasswordRequest(<?php echo $request['id']; ?>)">
                                        Reject
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Recent Notifications -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Recent Notifications</h2>
                        <span class="admin-card-action"><?php echo $unread_notifications; ?> unread</span>
                    </div>
                    
                    <?php if (empty($recent_notifications)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🔔</div>
                            <p>No notifications</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_notifications as $notification): ?>
                            <div class="notification-item">
                                <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                <div class="notification-time">
                                    <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function approvePasswordRequest(requestId) {
            if (confirm('Are you sure you want to approve this password reset request? A new password will be generated and sent to the user.')) {
                // Send AJAX request to approve
                fetch('../api/admin/approve-password-request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        request_id: requestId,
                        action: 'approve'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Password reset request approved! A new password has been sent to the user.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                });
            }
        }

        function rejectPasswordRequest(requestId) {
            const reason = prompt('Please provide a reason for rejection (optional):');
            if (reason !== null) {
                // Send AJAX request to reject
                fetch('../api/admin/reject-password-request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        request_id: requestId,
                        action: 'reject',
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Password reset request rejected.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                });
            }
        }
    </script>
</body>
</html>
