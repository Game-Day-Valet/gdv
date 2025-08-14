<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function registerReferal(Request $request)
    {
        $referralCode = $request->query('referralCode');

        // Custom app scheme with referral code
        $appScheme = "myapp://open?referralCode={$referralCode}";

        // Fallback URL if app not installed
        $storeUrl = route('rentalsystem.signin');

        return <<<HTML
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Open App</title>
                    <script>
                        window.location = '$appScheme';
                        setTimeout(function() {
                            window.location = '$storeUrl';
                        }, 2000);
                    </script>
                </head>
                <body>
                    <p>If your app doesn’t open automatically, <a href="$storeUrl">click here</a>.</p>
                </body>
                </html>
            HTML;
    }
}
