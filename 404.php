<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8fafc;
        }
        .container {
            text-align: center;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #d1d5db;
        }
        .error-message {
            font-size: 1.5rem;
            color: #6b7280;
        }
        .back-home {
            margin-top: 2rem;
            font-size: 1rem;
            color: #2563eb;
            text-decoration: none;
        }
        .back-home:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <div class="error-message">Sorry, the page you are looking for could not be found.</div>
        <a href="/" class="back-home">Go back to Home</a>
    </div>
</body>
</html>
