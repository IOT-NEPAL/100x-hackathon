<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Gemini API</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1a1a1a;
            border-bottom: 3px solid #ffff00;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            background: #1a1a1a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #2d2d2d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Gemini API Connection Test</h1>
        
        <?php
        require_once 'db_config.php';
        
        echo '<div class="info">';
        echo '<strong>API Key Configured:</strong> ' . substr(GEMINI_API_KEY, 0, 10) . '...' . substr(GEMINI_API_KEY, -4);
        echo '</div>';
        
        // Test API connection
        $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'Say "Hello! API is working!" in exactly those words.'
                        ]
                    ]
                ]
            ]
        ];
        
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n" .
                            "X-goog-api-key: " . GEMINI_API_KEY . "\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ];
        
        echo '<h3>Testing API Connection...</h3>';
        echo '<div class="info">Sending request to Gemini 2.0 Flash...</div>';
        
        try {
            $context  = stream_context_create($options);
            $result = @file_get_contents($api_url, false, $context);
            
            if ($result === FALSE) {
                $error = error_get_last();
                echo '<div class="error">';
                echo '<strong>❌ API Request Failed</strong><br>';
                echo 'Error: ' . ($error['message'] ?? 'Unknown error');
                echo '<br><br><strong>Possible causes:</strong>';
                echo '<ul>';
                echo '<li>Invalid API key</li>';
                echo '<li>Network connection issue</li>';
                echo '<li>API quota exceeded</li>';
                echo '</ul>';
                echo '</div>';
            } else {
                $response = json_decode($result, true);
                
                if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                    $ai_response = $response['candidates'][0]['content']['parts'][0]['text'];
                    
                    echo '<div class="success">';
                    echo '<strong>✅ API Connection Successful!</strong><br><br>';
                    echo '<strong>AI Response:</strong><br>';
                    echo htmlspecialchars($ai_response);
                    echo '</div>';
                    
                    echo '<div class="info">';
                    echo '<strong>✅ Your Gemini API is working perfectly!</strong><br>';
                    echo 'You can now use the AI Career Guidance chatbot in your application.';
                    echo '</div>';
                    
                } elseif (isset($response['error'])) {
                    echo '<div class="error">';
                    echo '<strong>❌ API Error</strong><br>';
                    echo 'Message: ' . htmlspecialchars($response['error']['message'] ?? 'Unknown error');
                    echo '<br>Status: ' . htmlspecialchars($response['error']['status'] ?? 'N/A');
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<strong>❌ Unexpected Response Format</strong><br>';
                    echo '<pre>' . htmlspecialchars(print_r($response, true)) . '</pre>';
                    echo '</div>';
                }
            }
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Exception Occurred</strong><br>';
            echo 'Error: ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
        
        <hr style="margin: 30px 0;">
        
        <h3>📋 Next Steps</h3>
        <ol>
            <li>If the test passed, you're ready to use the AI Career Chatbot!</li>
            <li>Import the database: <code>database.sql</code> in phpMyAdmin</li>
            <li>Create a student account and add skills</li>
            <li>Navigate to "AI Career Guide" and start chatting!</li>
        </ol>
        
        <div style="margin-top: 30px;">
            <a href="index.php" class="btn">Go to Application →</a>
            <a href="QUICK_START_GUIDE.md" class="btn" style="background: #666; margin-left: 10px;">View Setup Guide</a>
        </div>
        
        <hr style="margin: 30px 0;">
        
        <h3>🔧 API Details</h3>
        <div class="info">
            <strong>Endpoint:</strong> <?php echo htmlspecialchars($api_url); ?><br>
            <strong>Model:</strong> gemini-2.0-flash<br>
            <strong>API Key:</strong> Configured ✓<br>
            <strong>Request Method:</strong> POST<br>
            <strong>Content-Type:</strong> application/json
        </div>
    </div>
</body>
</html>

