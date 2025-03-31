<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$servername = "localhost";
$username = "root";
$password = "";
$database = "notesdb"; // Change to your database name

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Query to fetch notes
$note_sql = "SELECT * FROM notes WHERE user_id = ? AND (title LIKE ? OR content LIKE ?) ORDER BY created_at DESC";
$search_term = '%' . $search . '%';
$note_stmt = $conn->prepare($note_sql);
$note_stmt->bind_param("iss", $user_id, $search_term, $search_term);
$note_stmt->execute();
$note_result = $note_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        /* Add these new animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes glow {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            100% { box-shadow: 0 0 20px 10px rgba(59, 130, 246, 0); }
        }

        @keyframes slideUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Apply animations to elements */
        .nav-item { 
            animation: slideIn 0.5s ease-out;
        }

        .note-card {
            animation: fadeIn 0.6s ease-out, slideUp 0.8s ease;
        }

        .button-hover {
            animation: float 3s ease-in-out infinite;
        }

        .search-container:hover {
            animation: glow 1.5s infinite alternate;
        }

        #theme-toggle:hover svg {
            animation: rotate 1s linear infinite;
        }

        /* Add hover effects to note cards */
        .note-card:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        /* Add animation to welcome message */
        .welcome-message {
            animation: slideIn 0.8s ease-out 0.5s both;
        }

        /* Add animation to search input */
        .search-input {
            transition: all 0.3s ease;
        }

        .search-input:focus {
            animation: glow 1.5s infinite alternate;
        }

        /* Add animation to add note button */
        .add-note-btn {
            animation: float 3s ease-in-out infinite;
        }

        .add-note-btn:hover {
            animation: none;
            transform: scale(1.05);
        }
        /* Enhanced button animations */
        button, .btn {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        button:hover, .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        button:active, .btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        button::after, .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.5s, opacity 0.5s;
        }

        button:active::after, .btn:active::after {
            transform: translate(-50%, -50%) scale(2);
            opacity: 0;
            transition: 0s;
        }

        /* Apply animations to elements */
        .nav-item { animation: slideIn 0.5s ease-out; }
        .note-card { animation: fadeIn 0.6s ease-out; }
        
        /* Smooth transitions */
        .hover-scale { transition: transform 0.2s ease; }
        .hover-scale:hover { transform: scale(1.02); }
        
        /* Search and filter animations */
        .search-container {
            animation: slideIn 0.5s ease-out;
            transition: all 0.3s ease;
        }
        .search-container:focus-within {
            transform: scale(1.02);
        }

        /* Theme toggle animation */
        #theme-toggle svg {
            transition: transform 0.3s ease;
        }

        #theme-toggle:hover svg {
            transform: rotate(180deg);
        }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3; /* Standard property for future compatibility */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Custom dark mode styles */
        .dark body {
            color: #e2e8f0;
            background-color: #121212; /* Darker gray background */
        }
        
        /* Fix for text colors in dark mode */
        .dark .dark\:text-white {
            color: #ffffff !important;
        }
        
        .dark .dark\:text-gray-300 {
            color: #d1d5db !important;
        }
        
        .dark .dark\:text-gray-400 {
            color: #9ca3af !important;
        }
        
        /* Background color fixes */
        .dark .dark\:bg-gray-900 {
            background-color: #121212 !important; /* Override Tailwind's dark bg */
        }
        
        .dark .dark\:bg-gray-800 {
            background-color: #1e1e1e !important; /* Override for content areas */
        }
        
        .dark .dark\:bg-gray-700 {
            background-color: #2a2a2a !important; /* Override for cards/items */
        }
        
        .dark .dark\:hover\:bg-gray-600:hover {
            background-color: #333333 !important; /* Hover state for items */
        }
        
        .dark .dark\:border-gray-600 {
            border-color: #4b5563 !important;
        }
        
        /* Dark mode transitions */
        body, .dark-transition {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg mb-6 transition-colors duration-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-wrap justify-between items-center py-3">
                <!-- Left Section: Title -->
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white transition-colors duration-200 flex items-center">
                    <i class="fas fa-sticky-note mr-2"></i> My Notes
                </h1>

                <!-- Middle Section: Welcome Message -->
                <div class="order-3 md:order-2 mt-2 md:mt-0 flex justify-center">
                    <!-- Update the Welcome Message -->
                    <span class="welcome-message text-gray-700 dark:text-gray-300 text-lg transition-colors duration-200 hidden md:inline-block">
                        Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    </span>
                </div>

                <!-- Right Section: Theme Toggle, Search and Logout -->
                <div class="flex flex-col md:flex-row items-center space-y-3 md:space-y-0 md:space-x-4 mt-2 sm:mt-0 order-2 md:order-3 w-full md:w-auto">
                    <!-- Theme Toggle Button -->
                    <div class="flex items-center space-x-4 w-full md:w-auto justify-center md:justify-start">
                        <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-full p-2 transition-colors duration-200" title="Toggle dark mode">
                            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Search Form -->
                    <form action="notes.php" method="GET" class="flex items-center space-x-2 w-full md:w-auto">
                        <!-- Search Input with Button -->
                        <div class="flex items-center w-full rounded-full overflow-hidden border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm">
                            <input type="text" name="search" placeholder="Search notes..." class="search-input w-full px-2 text-sm bg-transparent border-none focus:ring-0 text-gray-800 dark:text-white" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white p-2 focus:outline-none transition-colors duration-200" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Back to Dashboard Button -->
                    <a href="../display.php" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-3 py-1.5 rounded-full text-sm transition-colors duration-200 flex items-center" title="Back to Dashboard">
                        <i class="fas fa-home mr-1.5"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Logout Button -->
                    <a href="../../login/logout.php" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-3 py-1.5 rounded-full text-sm transition-colors duration-200 flex items-center" title="Log out of your account">
                        <i class="fas fa-sign-out-alt mr-1.5"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4">
        <!-- Notes Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Your Notes</h2>
                <!-- Update the Add Note button -->
                <a href="../index.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i> Add Note
                </a>
            </div>
            <?php if ($note_result && $note_result->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php $note_index = 0; while ($note = $note_result->fetch_assoc()): 
                        // Fetch attachments for the note (limit to 2)
                        $attach_sql = "SELECT file_name FROM attachments WHERE note_id = ? LIMIT 2";
                        $attach_stmt = $conn->prepare($attach_sql);
                        $attach_stmt->bind_param("i", $note['id']);
                        $attach_stmt->execute();
                        $attachments_result = $attach_stmt->get_result();
                        
                        // Count total attachments
                        $count_sql = "SELECT COUNT(*) as total FROM attachments WHERE note_id = ?";
                        $count_stmt = $conn->prepare($count_sql);
                        $count_stmt->bind_param("i", $note['id']);
                        $count_stmt->execute();
                        $total_attachments = $count_stmt->get_result()->fetch_assoc()['total'];
                    ?>
                        <!-- Note content remains unchanged -->
                        <a href="edit.php?id=<?php echo $note['id']; ?>" class="note-card block border rounded-lg p-6 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 hover:shadow-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-300" style="--note-index: <?php echo $note_index++; ?>">
                            <div class="flex justify-between items-start">
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($note['title']); ?></h3>
                            </div>
                            <p class="text-[17px] text-gray-700 dark:text-gray-300 mt-4 mb-4 leading-relaxed line-clamp-3">
                                <?php echo nl2br(htmlspecialchars(substr($note['content'], 0, 300))); ?>...
                            </p>
                            
                            <?php if ($total_attachments > 0): ?>
                            <div class="flex items-center mt-3 mb-2">
                                <i class="fas fa-paperclip text-blue-500 dark:text-blue-400 mr-2"></i>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <?php 
                                    $attachment_names = [];
                                    while ($attachment = $attachments_result->fetch_assoc()) {
                                        $attachment_names[] = htmlspecialchars($attachment['file_name']);
                                    }
                                    echo implode(', ', $attachment_names);
                                    
                                    // Show how many more if there are more than 2
                                    if ($total_attachments > 2) {
                                        echo ' <span class="text-blue-500 dark:text-blue-400">+' . ($total_attachments - 2) . ' more</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <?php if ($note['updated_at'] !== null): ?>
                                    <span class="font-semibold">Last Updated:</span> <?php echo date('F j, Y, g:i a', strtotime($note['updated_at'])); ?>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-300">No notes found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            var themeToggleBtn = document.getElementById('theme-toggle');
            var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
            var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

            // Change the icons inside the button based on previous settings
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                themeToggleLightIcon.classList.remove('hidden');
            } else {
                themeToggleDarkIcon.classList.remove('hidden');
            }

            themeToggleBtn.addEventListener('click', function() {
                // Toggle icons
                themeToggleDarkIcon.classList.toggle('hidden');
                themeToggleLightIcon.classList.toggle('hidden');

                // If set via local storage previously
                if (localStorage.getItem('color-theme')) {
                    if (localStorage.getItem('color-theme') === 'light') {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('color-theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('color-theme', 'light');
                    }
                } else {
                    // If NOT set via local storage previously
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('color-theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('color-theme', 'dark');
                    }
                }
            });
        });

        function deleteNote(noteId) {
            if (confirm('Are you sure you want to delete this note?')) {
                fetch('delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'note_id=' + noteId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting note: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting note');
                });
            }
        }
    </script>

    
</div>
</html>