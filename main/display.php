<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$servername = "localhost";
$username = "root";
$password = "";
$database = "notesdb";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Retrieve the filter value from the GET request
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Modify the SQL query for tasks based on the filter
if ($filter === 'tasks' || $filter === 'all') {
    $task_sql = "SELECT * FROM tasks WHERE user_id = ? AND (title LIKE ? OR description LIKE ?)";
    if ($filter === 'tasks') {
        $task_sql .= " ORDER BY FIELD(status, 'pending', 'in-progress', 'completed'), due_date ASC";
    }
    $task_stmt = $conn->prepare($task_sql);
    $search_term = '%' . $search . '%';
    $task_stmt->bind_param("iss", $user_id, $search_term, $search_term);
    $task_stmt->execute();
    $task_result = $task_stmt->get_result();
} else {
    $task_result = null; // No tasks to display if the filter is "notes"
}

// Modify the SQL query for notes based on the filter
if ($filter === 'notes' || $filter === 'all') {
    $note_sql = "SELECT * FROM notes WHERE user_id = ? AND (title LIKE ? OR content LIKE ?) ORDER BY created_at DESC";
    $search_term = '%' . $search . '%';
    $note_stmt = $conn->prepare($note_sql);
    $note_stmt->bind_param("iss", $user_id, $search_term, $search_term);
    $note_stmt->execute();
    $note_result = $note_stmt->get_result();
} else {
    $note_result = null; // No notes to display if the filter is "tasks"
}

// Modify the SQL query for events based on the filter
if ($filter === 'events' || $filter === 'all') {
    $event_sql = "SELECT * FROM events WHERE user_id = ? AND (title LIKE ? OR description LIKE ?) ORDER BY event_date ASC";
    $search_term = '%' . $search . '%';
    $event_stmt = $conn->prepare($event_sql);
    $event_stmt->bind_param("iss", $user_id, $search_term, $search_term);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
} else {
    $event_result = null; // No events to display if the filter is not "events" or "all"
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes ripple {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(2); opacity: 0; }
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
        .card { animation: fadeIn 0.6s ease-out; }
        
        /* Smooth transitions */
        .hover-scale { transition: transform 0.2s ease; }
        .hover-scale:hover { transform: scale(1.02); }
        
        /* Status indicators */
        .status-badge {
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }

        /* Search and filter animations */
        .search-container {
            animation: slideIn 0.5s ease-out;
            transition: all 0.3s ease;
        }
        .search-container:focus-within {
            transform: scale(1.02);
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3; /* Standard property for future compatibility */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2; /* Standard property for future compatibility */
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
    <!-- Add animation classes to main elements -->
    <div class="animate-fade-in">
    <!-- Navigation Bar -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg mb-6 transition-colors duration-200 nav-item">
        <!-- Add animation classes to navigation elements -->
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-wrap justify-between items-center py-3">
                <!-- Left Section: Title -->
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white transition-colors duration-200 flex items-center">
                    <i class="fas fa-tasks mr-2"></i> My Productivity Dashboard
                </h1>

                <!-- Middle Section: Welcome Message -->
                <div class="order-3 md:order-2 mt-2 md:mt-0 flex justify-center">
                    <span class="text-gray-700 dark:text-gray-300 text-lg transition-colors duration-200 hidden md:inline-block">
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
                    <form action="display.php" method="GET" class="flex items-center space-x-2 w-full md:w-auto search-container">
                        <!-- Separate Filter Dropdown -->
                        <select name="filter" onchange="this.form.submit()" class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-700" title="Filter your results">
                            <option value="all" <?php echo ($filter === 'all') ? 'selected' : ''; ?>>All</option>
                            <option value="tasks" <?php echo ($filter === 'tasks') ? 'selected' : ''; ?>>Tasks</option>
                            <option value="notes" <?php echo ($filter === 'notes') ? 'selected' : ''; ?>>Notes</option>
                            <option value="events" <?php echo ($filter === 'events') ? 'selected' : ''; ?>>Events</option>
                        </select>
                        
                        <!-- Search Input with Button -->
                        <div class="flex items-center w-full rounded-full overflow-hidden border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm">
                            <input type="text" name="search" placeholder="Search..." class="w-full px-2 text-sm bg-transparent border-none focus:ring-0 text-gray-800 dark:text-white" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white p-2 focus:outline-none transition-colors duration-200" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Logout Button -->
                    <a href="../login/logout.php" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-3 py-1.5 rounded-full text-sm transition-all duration-200 flex items-center btn" title="Log out of your account">
                        <i class="fas fa-sign-out-alt mr-1.5"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4">
        <!-- Today's Schedule Gantt Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6 card hover-scale">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center">
                    <i class="far fa-calendar-check mr-2"></i> Schedule for the day
                </h2>
                <div class="flex items-center">
                    <!-- Date selector -->
                    <form id="dateSelectForm" class="flex items-center">
                        <input type="date" id="scheduleDate" name="scheduleDate" 
                               class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-800 dark:text-white"
                               value="<?php echo isset($_GET['scheduleDate']) ? htmlspecialchars($_GET['scheduleDate']) : date('Y-m-d'); ?>">
                    </form>
                </div>
            </div>
            
            <?php
            // Get selected date (default to today if not specified)
            $selected_date = isset($_GET['scheduleDate']) ? $_GET['scheduleDate'] : date('Y-m-d');
            
            // Fetch events for the selected date
            $today_events_sql = "SELECT id, title, event_time, event_end_time FROM events 
                                WHERE user_id = ? AND event_date = ? 
                                ORDER BY event_time ASC";
            $today_events_stmt = $conn->prepare($today_events_sql);
            $today_events_stmt->bind_param("is", $user_id, $selected_date);
            $today_events_stmt->execute();
            $today_events_result = $today_events_stmt->get_result();
            
            // Fetch tasks for the selected date
            $today_tasks_sql = "SELECT id, title, due_date, status FROM tasks 
                               WHERE user_id = ? AND DATE(due_date) = ? 
                               ORDER BY due_date ASC";
            $today_tasks_stmt = $conn->prepare($today_tasks_sql);
            $today_tasks_stmt->bind_param("is", $user_id, $selected_date);
            $today_tasks_stmt->execute();
            $today_tasks_result = $today_tasks_stmt->get_result();
            
            $has_items = ($today_events_result->num_rows > 0 || $today_tasks_result->num_rows > 0);
            
            // Get current hour to highlight - use server timezone
            date_default_timezone_set('Asia/Kolkata'); // Set to Indian timezone
            $current_hour = (int)date('G');
            
            // Process events and tasks for the Gantt chart
            $events = [];
            $tasks = [];
            
            // Process events
            while ($event = $today_events_result->fetch_assoc()) {
                $start_time = strtotime($event['event_time']);
                $end_time = !empty($event['event_end_time']) ? strtotime($event['event_end_time']) : $start_time + 3600; // Default 1 hour
                
                $start_hour = date('G', $start_time) + (date('i', $start_time) / 60);
                $end_hour = date('G', $end_time) + (date('i', $end_time) / 60);
                
                // Handle events that cross midnight
                if ($end_hour < $start_hour) {
                    $end_hour = 24;
                }
                
                $events[] = [
                    'id' => 'event_' . $event['id'],
                    'title' => $event['title'],
                    'start' => $start_hour,
                    'end' => $end_hour,
                    'color' => 'bg-blue-500',
                    'start_time' => date('g:i A', $start_time),
                    'end_time' => date('g:i A', $end_time),
                    'duration' => round(($end_hour - $start_hour) * 60) . ' min',
                    'type' => 'event'
                ];
            }
            
            // Process tasks
            while ($task = $today_tasks_result->fetch_assoc()) {
                $due_time = strtotime($task['due_date']);
                $due_hour = date('G', $due_time) + (date('i', $due_time) / 60);
                
                $color = 'bg-red-500';
                $icon = 'fa-hourglass-half';
                if ($task['status'] == 'completed') {
                    $color = 'bg-green-500';
                    $icon = 'fa-check-circle';
                } elseif ($task['status'] == 'in-progress') {
                    $color = 'bg-yellow-500';
                    $icon = 'fa-spinner';
                }
                
                $tasks[] = [
                    'id' => 'task_' . $task['id'],
                    'title' => $task['title'],
                    'start' => $due_hour,
                    'end' => $due_hour + 0.5, // Show as a 30-minute block
                    'color' => $color,
                    'icon' => $icon,
                    'status' => $task['status'],
                    'due_time' => date('g:i A', $due_time),
                    'type' => 'task'
                ];
            }
            
            // Function to check if two items overlap
            function doesOverlap($lane, $item) {
                foreach ($lane as $existing) {
                    // Check if there's any overlap
                    if ($item['start'] < $existing['end'] && $item['end'] > $existing['start']) {
                        return true;
                    }
                }
                return false;
            }
            
            // Function to arrange items in lanes to prevent overlapping
            function arrangeItems($items) {
                $lanes = [];
                foreach ($items as $item) {
                    $placed = false;
                    for ($i = 0; $i < count($lanes); $i++) {
                        if (!doesOverlap($lanes[$i], $item)) {
                            $lanes[$i][] = $item;
                            $placed = true;
                            break;
                        }
                    }
                    if (!$placed) {
                        $lanes[] = [$item];
                    }
                }
                return $lanes;
            }
            
            // Arrange events and tasks in separate lanes
            $event_lanes = arrangeItems($events);
            $task_lanes = arrangeItems($tasks);
            
            // Calculate total number of lanes for spacing
            $total_lanes = count($event_lanes) + count($task_lanes);
            ?>
            
            <?php if ($has_items): ?>
                <div class="relative mt-6">
                    <!-- Current time indicator -->
                    <?php 
                    $now = new DateTime('now', new DateTimeZone('Asia/Kolkata')); // Use Indian timezone
                    $current_hour_decimal = $now->format('G') + ($now->format('i') / 60);
                    $current_position = ($current_hour_decimal / 24) * 100;
                    ?>
                    <div class="absolute h-full" style="left: <?php echo $current_position; ?>%; top: 0; z-index: 10;">
                        <div class="w-0.5 bg-red-500 animate-pulse" style="height: 100%;"></div>
                        <div class="w-3 h-3 rounded-full bg-red-500 -ml-1.5 -mt-1"></div>
                        <div class="absolute top-0 -ml-10 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-2 py-0.5 rounded text-xs font-bold">
                            <?php echo $now->format('g:i A'); ?>
                        </div>
                    </div>
                    
                    <!-- Time indicators with improved styling -->
                    <div class="flex border-b-2 border-gray-300 dark:border-gray-600 pb-2 mb-2">
                        <?php for ($hour = 0; $hour < 24; $hour++): ?>
                            <div class="flex-1 text-xs text-center <?php echo ($hour == $current_hour) ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-500 dark:text-gray-400'; ?>">
                                <?php if ($hour % 3 == 0): ?>
                                    <span class="inline-block"><?php echo date('g A', strtotime("$hour:00")); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Gantt chart grid with improved styling -->
                    <div class="h-6 w-full flex mb-1 bg-gray-50 dark:bg-gray-700 rounded">
                        <?php for ($hour = 0; $hour < 24; $hour++): ?>
                            <div class="flex-1 border-r border-gray-200 dark:border-gray-600 
                                <?php echo ($hour >= 9 && $hour < 17) ? 'bg-blue-50/50 dark:bg-blue-900/10' : 
                                    (($hour >= 22 || $hour < 6) ? 'bg-gray-100 dark:bg-gray-800' : ''); ?>">
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Render events -->
                    <?php 
                    $lane_index = 0;
                    foreach ($event_lanes as $lane): 
                        foreach ($lane as $event):
                            $left = ($event['start'] / 24) * 100;
                            $width = (($event['end'] - $event['start']) / 24) * 100;
                            $top = 30 + ($lane_index * 45); // 45px per lane
                            
                            // Determine if this event is current
                            $is_current = ($current_hour_decimal >= $event['start'] && $current_hour_decimal < $event['end']);
                            $border_class = $is_current ? 'border-2 border-white dark:border-gray-900' : '';
                            $shadow_class = $is_current ? 'shadow-lg' : 'shadow';
                    ?>
                            <div class="absolute rounded-md px-3 py-1.5 text-xs text-white overflow-hidden whitespace-nowrap transition-all duration-200 <?php echo $event['color']; ?> <?php echo $shadow_class; ?> <?php echo $border_class; ?>"
                                 style="left: <?php echo $left; ?>%; width: <?php echo max(5, $width); ?>%; top: <?php echo $top; ?>px; z-index: <?php echo $is_current ? 5 : 1; ?>;"
                                 onclick="window.location.href='./backend/edit_event.php?id=<?php echo substr($event['id'], 6); ?>'">
                                <div class="flex items-center">
                                    <i class="far fa-calendar-alt mr-1.5"></i>
                                    <span class="font-medium"><?php echo htmlspecialchars($event['title']); ?></span>
                                    <span class="ml-auto text-white/80"><?php echo $event['start_time']; ?> - <?php echo $event['end_time']; ?></span>
                                </div>
                            </div>
                    <?php 
                        endforeach;
                        $lane_index++;
                    endforeach; 
                    ?>
                    
                    <!-- Render tasks -->
                    <?php 
                    foreach ($task_lanes as $lane): 
                        foreach ($lane as $task):
                            $left = ($task['start'] / 24) * 100;
                            $width = (($task['end'] - $task['start']) / 24) * 100;
                            $top = 30 + ($lane_index * 45); // Continue from where events left off
                            
                            // Determine if this task is current
                            $is_current = ($current_hour_decimal >= $task['start'] && $current_hour_decimal < $task['end']);
                            $border_class = $is_current ? 'border-2 border-white dark:border-gray-900' : '';
                            $shadow_class = $is_current ? 'shadow-lg' : 'shadow';
                    ?>
                            <div class="absolute rounded-md px-3 py-1.5 text-xs text-white overflow-hidden whitespace-nowrap transition-all duration-200 <?php echo $task['color']; ?> <?php echo $shadow_class; ?> <?php echo $border_class; ?>"
                                 style="left: <?php echo $left; ?>%; width: <?php echo max(5, $width); ?>%; top: <?php echo $top; ?>px; z-index: <?php echo $is_current ? 5 : 1; ?>;"
                                 onclick="window.location.href='./backend/edit_task.php?id=<?php echo substr($task['id'], 5); ?>'">
                                <div class="flex items-center">
                                    <i class="fas <?php echo $task['icon']; ?> mr-1.5"></i>
                                    <span class="font-medium"><?php echo htmlspecialchars($task['title']); ?></span>
                                    <span class="ml-auto text-white/80"><?php echo $task['due_time']; ?></span>
                                </div>
                            </div>
                    <?php 
                        endforeach;
                        $lane_index++;
                    endforeach; 
                    ?>
                    
                    <!-- Add some space based on number of lanes -->
                    <div style="height: <?php echo max(100, ($total_lanes * 45) + 50); ?>px;"></div>
                    
                    <!-- Legend with improved styling -->
                    <div class="flex flex-wrap items-center gap-4 mt-4 text-sm bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-1.5"></div>
                            <span class="text-gray-700 dark:text-gray-300">Event</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-1.5"></div>
                            <span class="text-gray-700 dark:text-gray-300">Pending Task</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-1.5"></div>
                            <span class="text-gray-700 dark:text-gray-300">In-Progress Task</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-1.5"></div>
                            <span class="text-gray-700 dark:text-gray-300">Completed Task</span>
                        </div>
                        <div class="flex items-center ml-auto">
                            <div class="w-0.5 h-4 bg-red-500 mr-1.5"></div>
                            <span class="text-gray-700 dark:text-gray-300">Current Time</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-300">Nothing found.</p>
                
                <?php /* Remove the View All Events button when no events are found to be consistent
                <div class="mt-6 text-center">
                    <a href="./backend/events.php" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition duration-200">
                        <i class="fas fa-calendar-alt mr-2"></i> View All Events
                    </a>
                </div>
                */ ?>
            <?php endif; ?>
        </div>
        
        <!-- Tasks Section -->
        <?php if ($filter === 'all' || $filter === 'tasks'): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6 card hover-scale">
            <div class="flex justify-between items-center mb-6">
                <a href="./backend/tasks.php"> <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Your Tasks</h2></a>
                <a href="./backend/add_task.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                    Add Task
                </a>
            </div>
            <?php 
            // Modify the SQL query to limit to 3 tasks if not searching
            if ($filter === 'all' && empty($search)) {
                $task_sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC LIMIT 3";
                $task_stmt = $conn->prepare($task_sql);
                $task_stmt->bind_param("i", $user_id);
                $task_stmt->execute();
                $task_result = $task_stmt->get_result();
            }
            
            if ($task_result && $task_result->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while ($task = $task_result->fetch_assoc()): ?>
                        <!-- Wrap the task in a styled block that's clickable -->
                        <div onclick="window.location.href='./backend/tasks.php'" class="block border rounded-lg p-6 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 hover:shadow-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition duration-200 cursor-pointer">
                            <div class="flex justify-between items-start">
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($task['title']); ?></h3>
                                <span class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full inline-block 
                                        <?php echo ($task['status'] == 'completed') ? 'bg-green-500' : (($task['status'] == 'in-progress') ? 'bg-yellow-500' : 'bg-red-500'); ?>">
                                    </span>
                                    <span class="text-sm text-gray-800 dark:text-gray-300">
                                        <?php echo ucfirst($task['status']); ?>
                                    </span>
                                </span>
                            </div>
                            <!-- Task Description -->
                            <p class="text-[17px] text-gray-700 dark:text-gray-300 mt-4 mb-4 leading-relaxed line-clamp-2">
                                <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                            </p>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-semibold">Due:</span> <?php echo date('F j, Y, g:i a', strtotime($task['due_date'])); ?>
                            </div>
                            <div class="flex space-x-2 mt-3" onclick="event.stopPropagation()">
                                <a href="./backend/edit_task.php?id=<?php echo $task['id']; ?>" 
                                   class="bg-blue-500 text-white px-3 py-1 text-sm rounded-md hover:bg-blue-600 transition duration-200">
                                    Edit
                                </a>
                                <?php if ($task['status'] != 'completed'): ?>
                                    <button onclick="markTaskComplete(<?php echo $task['id']; ?>)" 
                                            class="bg-green-500 text-white px-3 py-1 text-sm rounded-md hover:bg-green-600 transition duration-200">
                                        Mark as Complete
                                    </button>
                                <?php endif; ?>
                                <?php if ($task['status'] == 'completed'): ?>
                                    <button onclick="deleteTask(<?php echo $task['id']; ?>)" 
                                            class="bg-red-500 text-white px-3 py-1 text-sm rounded-md hover:bg-red-600 transition duration-200">
                                        Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- View All Tasks button -->
                <div class="mt-6 text-center">
                    <a href="./backend/tasks.php" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition duration-200">
                        <i class="fas fa-tasks mr-2"></i> View All Tasks
                    </a>
                </div>
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-300">No tasks found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Events Section -->
        <?php if ($filter === 'all' || $filter === 'events'): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6 card hover-scale">
            <div class="flex justify-between items-center mb-6">
                <a href="./backend/events.php"><h2 class="text-2xl font-bold text-gray-800 dark:text-white">Your Events</h2></a>
                <a href="./backend/add_event.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                    Add Event
                </a>
            </div>
            <?php 
            // Modify the SQL query to limit to 3 upcoming events if not searching
            if ($filter === 'all' && empty($search)) {
                $event_sql = "SELECT * FROM events WHERE user_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC, event_time ASC LIMIT 3";
                $event_stmt = $conn->prepare($event_sql);
                $event_stmt->bind_param("i", $user_id);
                $event_stmt->execute();
                $event_result = $event_stmt->get_result();
            }
            
            if ($event_result && $event_result->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while ($event = $event_result->fetch_assoc()): ?>
                         <div onclick="window.location.href='./backend/events.php'" class="block border rounded-lg p-6 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 hover:shadow-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition duration-200 cursor-pointer">
                            <!-- Event content remains unchanged -->
                            <div class="flex justify-between items-start">
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <?php
                                    $event_date = strtotime($event['event_date']);
                                    $today = strtotime('today');
                                    $tomorrow = strtotime('tomorrow');
                                    $next_week = strtotime('+7 days');
                                    
                                    // Status code remains the same
                                ?>
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
                            <!-- Rest of event content -->
                            <div class="flex space-x-2 mt-3" onclick="event.stopPropagation()">
                                <a href="./backend/edit_event.php?id=<?php echo $event['id']; ?>" 
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
                
                <!-- View All Events button -->
                <div class="mt-6 text-center">
                    <a href="./backend/events.php" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition duration-200">
                        <i class="fas fa-calendar-alt mr-2"></i> View All Events
                    </a>
                </div>
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-300">No events found.</p>
                
                <!-- Remove the View All Events button when no events are found to be consistent -->
                <!-- <div class="mt-6 text-center">
                    <a href="./backend/events.php" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition duration-200">
                        <i class="fas fa-calendar-alt mr-2"></i> View All Events
                    </a>
                </div> -->
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <!-- Notes Section -->
        <?php if ($filter === 'all' || $filter === 'notes'): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6 card hover-scale">
            <div class="flex justify-between items-center mb-6">
                <a href="./backend/notes.php">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Your Notes</h2>
        </a>
        <a href="index.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                    Add Note
                </a>
            </div>
            <?php 
            // Modify the SQL query to limit to 3 notes if not searching
            if ($filter === 'all' && empty($search)) {
                $note_sql = "SELECT * FROM notes WHERE user_id = ? ORDER BY updated_at DESC, created_at DESC LIMIT 3";
                $note_stmt = $conn->prepare($note_sql);
                $note_stmt->bind_param("i", $user_id);
                $note_stmt->execute();
                $note_result = $note_stmt->get_result();
            }
            
            if ($note_result && $note_result->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while ($note = $note_result->fetch_assoc()): 
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
                        <a href="./backend/notes.php" class="block border rounded-lg p-6 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 hover:shadow-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition duration-200">
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
                                    <span class="font-semibold">Updated:</span> <?php echo date('F j, Y, g:i a', strtotime($note['updated_at'])); ?>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
                
                <!-- View All Notes button -->
                <div class="mt-6 text-center">
                    <a href="./backend/notes.php" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition duration-200">
                        <i class="fas fa-sticky-note mr-2"></i> View All Notes
                    </a>
                </div>
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-300">No notes found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="../scripts/display.js"></script>
</body>
</html>

<?php $conn->close(); ?>