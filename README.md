# ReMatch

ReMatch is a full-stack web application built with PHP, MySQL, and vanilla JavaScript. It was developed as a personal early project showcase to demonstrate secure authentication workflows, robust API design, and third-party service integration. 

*Note: This repository serves as a portfolio piece and is not currently configured for public deployment or third-party local execution.*

## Key Features

* **Advanced Authentication:** Secure user sign-up and login workflows featuring password hashing (BCrypt), password resets, and session management.
* **Two-Factor Authentication (2FA):** Integrated TOTP-based 2FA supporting standard authenticator apps (via QR code generation).
* **Security First:** Includes CSRF token validation, rate limiting, and Cloudflare Turnstile integration to prevent bot abuse.
* **RESTful API & JWT:** Stateless API endpoints secured by JSON Web Tokens (JWT) for handling frontend data fetching and profile updates.
* **Interactive Mapping:** Google Maps API integration with HTML5 Geolocation and IP-based location fallback (GeoIP2).
* **Localization (i18n):** Multi-language support seamlessly toggling between English and Hungarian.
* **Automated Emails:** Transactional emails for account verification, password resets, and security alerts utilizing PHPMailer.

## Tech Stack

* **Backend:** PHP 8.x, MySQL (PDO)
* **Frontend:** HTML5, CSS3, Vanilla JavaScript
* **APIs & Services:** Google Maps API, Cloudflare Turnstile
* **Key Libraries (Composer):** 
  * `firebase/php-jwt` (Token Management)
  * `phpmailer/phpmailer` (Email Services)
  * `spomky-labs/otphp` & `endroid/qr-code` (2FA & QR Generation)
  * `geoip2/geoip2` (IP Geolocation)
