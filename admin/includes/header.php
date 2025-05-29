<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        :root {
            --bg-color: #f4f4f4;
            --text-color: #000;
            --card-bg: #fff;
            --bar-color: #4CAF50;
            --header-bg: #333;
        }
        [data-theme="dark"] {
            --bg-color: #1e1e1e;
            --text-color: #fff;
            --card-bg: #2c2c2c;
            --bar-color: #00c3ff;
            --header-bg: #222;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        .header {
            background: var(--header-bg);
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .profile-section {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            cursor: pointer;
        }
        .profile-section img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        .dropdown {
            position: absolute;
            right: 0;
            top: 50px;
            background: var(--card-bg);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            display: none;
            flex-direction: column;
            min-width: 160px;
        }
        .dropdown a {
            padding: 10px 15px;
            text-decoration: none;
            color: var(--text-color);
        }
        .dropdown a:hover {
            background: rgba(0, 0, 0, 0.1);
        }
        .dark-mode-toggle {
            background: none;
            border: 1px solid #fff;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .main-content {
            padding: 20px;
        }
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .bar {
            height: 10px;
            background: var(--bar-color);
            border-radius: 4px;
            margin-top: 10px;
        }
        .nav-links {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        .nav-links a {
            text-decoration: none;
            padding: 12px;
            background: var(--card-bg);
            color: var(--text-color);
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: background 0.3s ease;
        }
        .nav-links a:hover {
            background: rgba(0,0,0,0.1);
        }
        @media (max-width: 600px) {
            .header h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
