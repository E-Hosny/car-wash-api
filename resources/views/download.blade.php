<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Download Luxuria Car Wash App</title>
    <meta name="description" content="Download Luxuria Car Wash app now and get premium car wash services at your home or office">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideUp 1s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            margin-bottom: 40px;
            position: relative;
        }
        
        .logo {
            width: 120px;
            height: 120px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); }
            50% { box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3); }
        }
        
        .title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            font-size: 1.2rem;
            color: #718096;
            margin-bottom: 50px;
            line-height: 1.6;
        }
        
        .download-buttons {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 30px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
        }
        
        .download-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .download-btn:hover::before {
            left: 100%;
        }
        
        .android-btn {
            background: linear-gradient(135deg, #3ddc84, #2bb673);
            color: white;
            box-shadow: 0 10px 25px rgba(61, 220, 132, 0.3);
        }
        
        .android-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(61, 220, 132, 0.4);
        }
        
        .ios-btn {
            background: linear-gradient(135deg, #007aff, #0056b3);
            color: white;
            box-shadow: 0 10px 25px rgba(0, 122, 255, 0.3);
        }
        
        .ios-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 122, 255, 0.4);
        }
        
        .btn-icon {
            font-size: 1.5rem;
            margin-left: 15px;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .feature {
            padding: 20px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        
        .feature:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .feature-text {
            font-size: 0.9rem;
            color: #4a5568;
            font-weight: 600;
        }
        
        .footer-text {
            margin-top: 30px;
            color: #a0aec0;
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 40px 20px;
                margin: 10px;
            }
            
            .title {
                font-size: 2rem;
            }
            
            .subtitle {
                font-size: 1rem;
            }
            
            .download-btn {
                padding: 15px 25px;
                font-size: 1rem;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="{{ asset('logo.png') }}" alt="Luxuria Car Wash Logo" class="logo">
        </div>
        
        <h1 class="title">Luxuria Car Wash</h1>
        <p class="subtitle">
            Get premium car wash services at your home or office<br>
            Trusted and secure app with professional team
        </p>
        
        <div class="download-buttons">
            <a href="https://play.google.com/store/apps/details?id=com.washluxuria.carwash" 
               target="_blank" 
               class="download-btn android-btn"
               onclick="trackDownload('android')">
                <i class="fab fa-google-play btn-icon"></i>
                <span>Download from Google Play</span>
            </a>
            
            <a href="https://apps.apple.com/us/app/luxuria-car-wash/id6748601716" 
               target="_blank" 
               class="download-btn ios-btn"
               onclick="trackDownload('ios')">
                <i class="fab fa-apple btn-icon"></i>
                <span>Download from App Store</span>
            </a>
        </div>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="feature-text">Interior & Exterior Wash</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="feature-text">Service at Your Location</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="feature-text">Trained & Trusted Team</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="feature-text">Secure & Easy Payment</div>
            </div>
        </div>
        
        <div class="footer-text">
            © 2024 Luxuria Car Wash. All rights reserved
        </div>
    </div>
    
    <script>
        function trackDownload(platform) {
            // Add loading effect
            const btn = event.target.closest('.download-btn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<div class="loading"></div> <span>Redirecting...</span>';
            btn.style.pointerEvents = 'none';
            
            // Restore original text after 2 seconds
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.pointerEvents = 'auto';
            }, 2000);
            
            // Track statistics (can add Google Analytics here)
            console.log(`Download clicked: ${platform}`);
        }
        
        // Additional effects on load
        document.addEventListener('DOMContentLoaded', function() {
            // Add effects to elements
            const features = document.querySelectorAll('.feature');
            features.forEach((feature, index) => {
                feature.style.animationDelay = `${index * 0.1}s`;
                feature.style.animation = 'slideUp 0.6s ease-out forwards';
            });
        });
    </script>
</body>
</html>
