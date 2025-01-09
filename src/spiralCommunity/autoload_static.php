<?php 
require_once 'spiralCommunity/app/Exceptions/ExceptionHandler.php';
require_once 'spiralCommunity/app/Services/OAuthService.php';
require_once 'spiralCommunity/app/Http/Controllers/Api/MeController.php';
require_once 'spiralCommunity/app/Http/Controllers/Api/TagsController.php';
require_once 'spiralCommunity/app/Http/Controllers/Api/UsersController.php';
require_once 'spiralCommunity/app/Http/Controllers/Auth/EntraController.php';
require_once 'spiralCommunity/app/Http/Controllers/Web/WelcomeController.php';
require_once 'spiralCommunity/app/Http/Middleware/RefreshTokenMiddleware.php';
require_once 'spiralCommunity/app/Http/Middleware/TenantValidationMiddleware.php';
require_once 'spiralCommunity/config/app.php';
require_once 'spiralCommunity/spiralCommunityApplication.php';
