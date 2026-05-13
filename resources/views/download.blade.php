<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('download.page_title') }}</title>
    <meta name="description" content="{{ __('download.meta_description') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            padding: 56px 20px 24px;
            position: relative;
            overflow-x: hidden;
        }

        html[dir="rtl"] body {
            font-family: 'Cairo', 'Inter', sans-serif;
        }

        /* Language toggle */
        .lang-switch {
            position: fixed;
            top: 52px;
            inset-inline-end: 16px;
            z-index: 1001;
        }

        .lang-switch-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 700;
            text-decoration: none;
            color: #fff;
            background: rgba(255, 255, 255, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .lang-switch-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-1px);
        }

        /* Top urgency strip — impossible to miss */
        .promo-top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 11px 16px;
            font-size: 0.92rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            background: linear-gradient(90deg, #ea580c 0%, #f59e0b 35%, #fbbf24 50%, #f59e0b 65%, #ea580c 100%);
            background-size: 220% 100%;
            animation: promoBarGlow 4s ease-in-out infinite;
            box-shadow: 0 6px 24px rgba(234, 88, 12, 0.45);
        }

        html[dir="rtl"] .promo-top-bar {
            text-transform: none;
            letter-spacing: 0.02em;
        }

        .promo-top-bar i {
            font-size: 1rem;
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.15));
        }

        .promo-top-bar .promo-highlight {
            background: rgba(0, 0, 0, 0.18);
            padding: 3px 10px;
            border-radius: 999px;
            font-weight: 900;
        }

        @keyframes promoBarGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
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
            line-height: 1.2;
        }
        
        .subtitle {
            font-size: 1.2rem;
            color: #718096;
            margin-bottom: 22px;
            line-height: 1.6;
        }

        /* In-card promo — conversion-focused */
        .promo-hero {
            margin: 0 auto 30px;
            max-width: 460px;
            padding: 20px 24px;
            border-radius: 22px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(245, 158, 11, 0.65);
            background:
                linear-gradient(145deg, rgba(255, 255, 255, 0.97) 0%, #fff7ed 42%, #ffedd5 100%);
            box-shadow:
                0 16px 48px rgba(234, 88, 12, 0.22),
                inset 0 1px 0 rgba(255, 255, 255, 0.95);
            animation: promoHeroLift 5s ease-in-out infinite;
        }

        @keyframes promoHeroLift {
            0%, 100% { transform: translateY(0); box-shadow: 0 16px 48px rgba(234, 88, 12, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.95); }
            50% { transform: translateY(-3px); box-shadow: 0 22px 56px rgba(234, 88, 12, 0.32), inset 0 1px 0 rgba(255, 255, 255, 0.95); }
        }

        .promo-hero::after {
            content: '';
            position: absolute;
            inset: -40%;
            background: linear-gradient(105deg, transparent 42%, rgba(255, 255, 255, 0.55) 50%, transparent 58%);
            animation: promoHeroSheen 5s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes promoHeroSheen {
            0%, 100% { transform: translateX(-18%) rotate(12deg); opacity: 0.35; }
            50% { transform: translateX(18%) rotate(12deg); opacity: 0.85; }
        }

        .promo-hero-inner {
            position: relative;
            z-index: 1;
        }

        .promo-headline {
            font-size: 1.65rem;
            font-weight: 900;
            line-height: 1.2;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .promo-headline em {
            font-style: normal;
            background: linear-gradient(135deg, #ea580c, #dc2626);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .promo-price-row {
            font-size: 1.15rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 0;
        }

        .promo-price-row .aed {
            display: inline-block;
            margin-inline-start: 4px;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 1.35rem;
            font-weight: 900;
            color: #fff;
            background: linear-gradient(135deg, #16a34a, #15803d);
            box-shadow: 0 8px 22px rgba(22, 163, 74, 0.45);
            animation: aedPulse 2.2s ease-in-out infinite;
        }

        @keyframes aedPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .social-proof {
            font-size: 0.9rem;
            color: #667eea;
            margin-bottom: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .social-proof i {
            color: #ffd700;
            font-size: 1rem;
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
            margin-inline-end: 15px;
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
            body {
                padding: 52px 16px 20px;
            }

            .lang-switch {
                top: 48px;
                inset-inline-end: 10px;
            }

            .lang-switch-link {
                padding: 7px 12px;
                font-size: 0.8rem;
            }

            .promo-top-bar {
                font-size: 0.78rem;
                padding: 10px 12px;
                gap: 8px;
            }

            .promo-headline {
                font-size: 1.38rem;
            }

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
            
            .social-proof {
                font-size: 0.85rem;
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

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-J663ES0FEL"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-J663ES0FEL');
</script>

<body data-redirecting="{{ __('download.redirecting') }}">
    <nav class="lang-switch" aria-label="{{ __('download.lang_switch_aria') }}">
        @if (app()->isLocale('ar'))
            <a href="{{ route('lang.switch', ['locale' => 'en']) }}" class="lang-switch-link">{{ __('download.switch_to_en') }}</a>
        @else
            <a href="{{ route('lang.switch', ['locale' => 'ar']) }}" class="lang-switch-link">{{ __('download.switch_to_ar') }}</a>
        @endif
    </nav>

    <div class="promo-top-bar" role="banner">
        <i class="fas fa-fire" aria-hidden="true"></i>
        <span><span class="promo-highlight">{{ __('download.promo_bar_off') }}</span> {{ __('download.promo_bar_services') }}</span>
        <span aria-hidden="true">·</span>
        <span>{{ __('download.promo_bar_from') }} <span class="promo-highlight">{{ __('download.promo_bar_price') }}</span></span>
    </div>

    <div class="container">
        <div class="logo-container">
            <img src="{{ asset('logo.png') }}" alt="{{ __('download.logo_alt') }}" class="logo">
        </div>
        
        <h1 class="title">{{ __('download.brand_title') }}</h1>
        <p class="subtitle">
            {{ __('download.subtitle') }}
        </p>

        <aside class="promo-hero" aria-labelledby="promo-heading">
            <div class="promo-hero-inner">
                <p id="promo-heading" class="promo-headline">
                    {{ __('download.promo_headline_prefix') }}<em>{{ __('download.promo_headline_em') }}</em>{{ __('download.promo_headline_suffix') }}
                </p>
                <p class="promo-price-row">
                    {{ __('download.promo_starting') }} <span class="aed">{{ __('download.promo_currency') }}</span>
                </p>
            </div>
        </aside>

        <div class="download-buttons">
            <a href="https://play.google.com/store/apps/details?id=com.washluxuria.carwash" 
               target="_blank" 
               class="download-btn android-btn"
               onclick="trackDownload(event, 'android')">
                <i class="fab fa-google-play btn-icon"></i>
                <span>{{ __('download.download_app') }}</span>
            </a>
            
            <a href="https://apps.apple.com/us/app/luxuria-car-wash/id6748601716" 
               target="_blank" 
               class="download-btn ios-btn"
               onclick="trackDownload(event, 'ios')">
                <i class="fab fa-apple btn-icon"></i>
                <span>{{ __('download.download_app') }}</span>
            </a>
        </div>
        
        <div class="social-proof">
            <i class="fas fa-star"></i>
            <span>{{ __('download.trusted') }}</span>
        </div>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-tag"></i>
                </div>
                <div class="feature-text">{{ __('download.feature_pricing') }}</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="feature-text">{{ __('download.feature_location') }}</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="feature-text">{{ __('download.feature_team') }}</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="feature-text">{{ __('download.feature_payment') }}</div>
            </div>
        </div>
        
        <div class="footer-text">
            {{ __('download.footer_rights') }}
        </div>
    </div>
    
    <script>
        function trackDownload(event, platform) {
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            const redirecting = document.body.dataset.redirecting || 'Redirecting…';
            
            btn.innerHTML = '<div class="loading"></div> <span>' + redirecting + '</span>';
            btn.style.pointerEvents = 'none';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.pointerEvents = 'auto';
            }, 2000);
            
            console.log('Download clicked: ' + platform);
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
