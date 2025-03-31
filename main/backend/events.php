<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

// Database connection
// Replace require_once with direct connection code
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

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming'; // Changed default from 'all' to 'upcoming'

// Fetch events for the user with search and filter functionality
$event_sql = "SELECT * FROM events WHERE user_id = ?";
$params = [$user_id];

// Add filter condition
if ($filter === 'upcoming') {
    $event_sql .= " AND event_date >= CURDATE()";
} elseif ($filter === 'past') {
    $event_sql .= " AND event_date < CURDATE()";
}

// Add search condition if search term is provided
if (!empty($search)) {
    $event_sql .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $search_term = '%' . $search . '%';
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

// Add ordering - for upcoming events, show soonest first; for past events, show most recent first
if ($filter === 'past') {
    $event_sql .= " ORDER BY event_date DESC, event_time DESC";
} else {
    $event_sql .= " ORDER BY event_date ASC, event_time ASC";
}

$event_stmt = $conn->prepare($event_sql);

// Bind parameters dynamically based on the number of parameters
if (count($params) === 1) {
    $event_stmt->bind_param("i", $params[0]);
} else {
    $types = "i" . str_repeat("s", count($params) - 1);
    $event_stmt->bind_param($types, ...$params);
}

$event_stmt->execute();
$event_result = $event_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events</title>
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
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
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
            <div class="flex justify-between items-center py-3">
                <!-- Left Section: Title -->
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white transition-colors duration-200 flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i> All Events
                </h1>

                <!-- Middle Section: Welcome Message -->
                <div class="order-3 md:order-2 mt-2 md:mt-0 flex justify-center">
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
                    <form action="events.php" method="GET" class="flex items-center space-x-2 w-full md:w-auto">
                        <!-- Search Input with Button -->
                        <div class="flex items-center w-full rounded-full overflow-hidden border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm">
                            <input type="text" name="search" placeholder="Search events..." class="search-input w-full px-2 text-sm bg-transparent border-none focus:ring-0 text-gray-800 dark:text-white" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
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
        <!-- Events Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Your Events</h2>
                <a href="add_event.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i> Add Event
                </a>
            </div>
            <?php if ($event_result && $event_result->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while ($event = $event_result->fetch_assoc()): ?>
                        <div class="note-card block border rounded-lg p-6 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 hover:shadow-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-300 transform hover:scale-105">
                            <div class="flex justify-between items-start">
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <?php
                                    $event_date = strtotime($event['event_date']);
                                    $today = strtotime('today');
                                    $tomorrow = strtotime('tomorrow');
                                    $next_week = strtotime('+7 days');
                                    
                                    if ($event_date < $today) {
                                        $badge_color = 'bg-gray-500';
                                        $status = 'Past';
                                    } elseif ($event_date == $today) {
                                        $badge_color = 'bg-red-500';
                                        $status = 'Today';
                                    } elseif ($event_date == $tomorrow) {
                                        $badge_color = 'bg-orange-500';
                                        $status = 'Tomorrow';
                                    } elseif ($event_date < $next_week) {
                                        $badge_color = 'bg-yellow-500';
                                        $status = 'This Week';
                                    } else {
                                        $badge_color = 'bg-green-500';
                                        $status = 'Upcoming';
                                    }
                                ?>
                                <span class="<?php echo $badge_color; ?> text-white text-xs font-bold px-2 py-1 rounded">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                            <p class="text-[17px] text-gray-700 dark:text-gray-300 mt-4 mb-4 leading-relaxed line-clamp-2">
                                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                            </p>
                            <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <span class="font-semibold"><i class="far fa-calendar-alt mr-1"></i> Date:</span> 
                                    <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                                </div>
                                <div>
                                    <span class="font-semibold"><i class="far fa-clock mr-1"></i> Time:</span> 
                                    <?php echo date('g:i a', strtotime($event['event_time'])); ?>
                                    <?php if (!empty($event['event_end_time'])): ?>
                                     - <?php echo date('g:i a', strtotime($event['event_end_time'])); ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($event['location'])): ?>
                                <div>
                                    <span class="font-semibold"><i class="fas fa-map-marker-alt mr-1"></i> Location:</span> 
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex space-x-2 mt-3">
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" 
                                   class="bg-blue-500 text-white px-3 py-1 text-sm rounded-md hover:bg-blue-600 transition duration-200">
                                    Edit
                                </a>
                                <button onclick="deleteEvent(<?php echo $event['id']; ?>)" 
                                        class="bg-red-500 text-white px-3 py-1 text-sm rounded-md hover:bg-red-600 transition duration-200">
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-300">No events found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Dark mode toggle functionality
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        var htmlElement = document.documentElement;

        // Initial state setup
        if (localStorage.getItem('color-theme') === 'dark' || 
            (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            htmlElement.classList.add('dark');
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            htmlElement.classList.remove('dark');
            themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            // Toggle icons
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // Toggle dark mode class
            htmlElement.classList.toggle('dark');
            
            // Update local storage
            localStorage.setItem('color-theme', htmlElement.classList.contains('dark') ? 'dark' : 'light');
        });

        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                fetch('delete_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'event_id=' + encodeURIComponent(eventId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Error deleting event: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting event');
                });
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>

<style>
    /* Add this new animation */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    /* Add this to existing styles */
    .note-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .note-card:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
</style>