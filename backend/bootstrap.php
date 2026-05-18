<?php
require_once __DIR__ . '/Support/Env.php';
require_once __DIR__ . '/Core/App.php';
require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Services/Mailer.php';
require_once __DIR__ . '/Services/OrderNotifier.php';
require_once __DIR__ . '/Services/OtpService.php';
require_once __DIR__ . '/Services/ChatbotService.php';

Env::load(__DIR__ . '/.env');
App::config();
