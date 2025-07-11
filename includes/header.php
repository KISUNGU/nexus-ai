<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusAI - Assistant IA d'Entreprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap');
        
        :root {
            --primary: #3b82f6;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
            --accent: #f59e0b;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .tech-font {
            font-family: 'Orbitron', sans-serif;
        }
        
        .glow {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }
        
        .glow-accent {
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.5);
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        
        .chat-container {
            height: calc(100vh - 100px - 100px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .message {
            max-width: 80%;
            border-radius: 1.5rem;
        }
        
        .user-message {
            background-color: var(--primary);
            color: white;
            border-bottom-right-radius: 0.5rem;
        }
        
        .bot-message {
            background-color: var(--light);
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 0.5rem;
        }
        
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--primary);
            margin: 0 2px;
        }
        
        .typing-indicator span:nth-child(1) { animation: bounce 1.3s infinite ease-in-out; }
        .typing-indicator span:nth-child(2) { animation: bounce 1.3s infinite ease-in-out 0.2s; }
        .typing-indicator span:nth-child(3) { animation: bounce 1.3s infinite ease-in-out 0.4s; }
        
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
        
        .robot-head {
            position: relative;
            width: 80px;
            height: 80px;
            background-color: var(--light);
            border-radius: 50% 50% 40% 40%;
            border: 3px solid var(--dark);
        }
        
        .robot-eye {
            position: absolute;
            width: 18px;
            height: 18px;
            background-color: var(--primary);
            border-radius: 50%;
            top: 30px;
        }
        
        .robot-eye.left { left: 20px; }
        .robot-eye.right { right: 20px; }
        
        .robot-mouth {
            position: absolute;
            width: 30px;
            height: 8px;
            background-color: var(--dark);
            border-radius: 5px;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .circuit-bg {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .dashboard-card { transition: all 0.3s ease; }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .webhook-status {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        .webhook-active {
            background-color: var(--secondary);
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.7);
        }
        
        .webhook-inactive { background-color: #ef4444; }

        .system-status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        .status-online { background-color: #10b981; }
        .status-offline { background-color: #ef4444; }
        .status-degraded { background-color: #f59e0b; }

        @media (max-width: 1023px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                width: 256px;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                z-index: 20;
                padding-top: 4rem;
                background-color: white;
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }
            .sidebar.open { transform: translateX(0); }
            #sidebarOverlay {
                position: fixed;
                inset: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 15;
            }
        }
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0) !important;
                position: relative;
                padding-top: 0;
                height: auto;
                box-shadow: none;
            }
            #sidebarOverlay { display: none !important; }
        }
    </style>
</head>
<body class="circuit-bg">
    <header class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg fixed top-0 left-0 right-0 z-10">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <button class="md:hidden text-white focus:outline-none" id="mobileMenuButton">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="robot-head relative">
                    <div class="robot-eye left"></div>
                    <div class="robot-eye right"></div>
                    <div class="robot-mouth"></div>
                    <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 w-6 h-3 bg-gray-300 rounded-full"></div>
                </div>
                <h1 class="tech-font text-2xl md:text-3xl font-bold">Nexus<span class="text-yellow-400">AI</span></h1>
            </div>
            <nav class="hidden md:flex space-x-6">
                <a href="/index.php" class="hover:text-yellow-400 transition">Dashboard</a>
                <a href="/analytics.php" class="hover:text-yellow-400 transition">Analytics</a>
                <a href="/settings.php" class="hover:text-yellow-400 transition">Paramètres</a>
            </nav>
            <div class="flex items-center space-x-4">
                <button class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-2 px-4 rounded-full transition flex items-center">
                    <i class="fas fa-rocket mr-2"></i>
                    <span>Upgrade</span>
                </button>
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-blue-800 font-bold cursor-pointer hover:bg-blue-100 transition">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </header>
    <div class="flex flex-grow w-full pt-20">