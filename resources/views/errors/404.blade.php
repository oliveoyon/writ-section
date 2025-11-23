<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }
        h1 {
            font-size: 6rem;
            color: #c30f08;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.25rem;
            margin-bottom: 30px;
            color: #555;
        }
        .cta-btn {
            background-color: #c30f08;
            color: white;
            padding: 15px 30px;
            font-size: 1rem;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .cta-btn:hover {
            background-color: #a40c06;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>404</h1>
        <p>Oops! The page you're looking for could not be found.</p>
        <a href="{{ url('/') }}" class="cta-btn">Go Back to Home</a>
    </div>

</body>
</html>
