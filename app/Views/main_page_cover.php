<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Times New Roman', serif; 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e8ba3 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="none"/><path d="M0 0 L50 50 L0 100 L50 50 L100 100 L100 0 L50 50 L100 0" stroke="rgba(255,255,255,0.03)" stroke-width="2" fill="none"/></svg>');
            opacity: 0.5;
        }
        
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
            padding: 40px 20px;
        }
        
        .content-wrapper {
            max-width: 1400px;
            width: 100%;
        }
        
        .brand-header {
            text-align: center;
            color: white;
            margin-bottom: 60px;
            animation: fadeInDown 1s ease;
        }
        
        .brand-logo {
            font-family: 'Georgia', serif;
            font-size: 72px;
            font-weight: bold;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .brand-logo i {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .brand-tagline {
            font-size: 24px;
            opacity: 0.95;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: 300;
            margin-bottom: 10px;
        }
        
        .brand-description {
            font-size: 18px;
            opacity: 0.85;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 30px;
            justify-content: center;
            animation: fadeInUp 1s ease;
        }
        
        li {
            flex: 0 1 auto;
        }
        
        li a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 20px 50px;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            min-width: 250px;
        }
        
        li a i {
            font-size: 24px;
        }
        
        li a:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }
        
        li a:active {
            transform: translateY(-2px);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 80px;
            animation: fadeIn 1.2s ease;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 35px 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            color: white;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }
        
        .feature-card i {
            font-size: 50px;
            margin-bottom: 20px;
            display: block;
            opacity: 0.9;
        }
        
        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            font-weight: bold;
            font-family: 'Georgia', serif;
        }
        
        .feature-card p {
            font-size: 16px;
            opacity: 0.85;
            line-height: 1.6;
            margin: 0;
        }
        
        .stats-section {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 60px;
            flex-wrap: wrap;
            animation: fadeIn 1.4s ease;
        }
        
        .stat-item {
            text-align: center;
            color: white;
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            font-family: 'Georgia', serif;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .stat-label {
            font-size: 16px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        @media (max-width: 768px) {
            .brand-logo {
                font-size: 48px;
                flex-direction: column;
                gap: 10px;
            }
            .brand-tagline {
                font-size: 18px;
                letter-spacing: 2px;
            }
            .brand-description {
                font-size: 16px;
            }
            ul {
                flex-direction: column;
                gap: 20px;
                padding: 0 20px;
            }
            li a {
                min-width: auto;
                width: 100%;
                padding: 18px 40px;
                font-size: 18px;
            }
            .features-grid {
                grid-template-columns: 1fr;
                margin-top: 50px;
            }
            .stats-section {
                gap: 40px;
                margin-top: 40px;
            }
            .stat-number {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="content-wrapper">
            <!-- Brand Header -->
            <div class="brand-header">
                <div class="brand-logo">
                    <i class="fas fa-warehouse"></i>
                    <span>WeBuild</span>
                </div>
                <div class="brand-tagline">Warehouse Management System</div>
                <p class="brand-description">
                    Streamline your warehouse operations with our comprehensive management solution. 
                    Track inventory, manage orders, and optimize your supply chain efficiently.
                </p>
            </div>
            
            <!-- Navigation Links -->
            <ul>
                <li>
                    <a href="<?= site_url('register') ?>">
                        <i class="fas fa-user-plus"></i>
                        <span>Register</span>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('login') ?>">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</body>
</html>