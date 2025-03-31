<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

// Database connection
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
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Fetch all tasks for the user with search and status filtering
$task_sql = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$user_id];

// Add status filter condition
if ($status_filter !== 'all') {
    $task_sql .= " AND status = ?";
    $params[] = $status_filter;
}

// Add search condition if search term is provided
if (!empty($search)) {
    $task_sql .= " AND (title LIKE ? OR description LIKE ?)";
    $search_term = '%' . $search . '%';
    $params = array_merge($params, [$search_term, $search_term]);
}

// Add ordering - pending and in-progress tasks first, sorted by due date
$task_sql .= " ORDER BY CASE WHEN status = 'completed' THEN 1 ELSE 0 END, due_date ASC";

$task_stmt = $conn->prepare($task_sql);

// Bind parameters dynamically based on the number of parameters
if (count($params) === 1) {
    $task_stmt->bind_param("i", $params[0]);
} elseif (count($params) === 2) {
    $task_stmt->bind_param("is", $params[0], $params[1]);
} elseif (count($params) === 3) {
    $task_stmt->bind_param("iss", $params[0], $params[1], $params[2]);
} else {
    $task_stmt->bind_param("isss", $params[0], $params[1], $params[2], $params[3]);
}

$task_stmt->execute();
$task_result = $task_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tasks</title>
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
                    <i class="fas fa-tasks mr-2"></i> All Tasks
                </h1>

                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle Button -->
                    <div class="flex items-center">
                        <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-full p-2 transition-colors duration-200" title="Toggle dark mode">
                            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Search and Filter Form -->
                    <form action="tasks.php" method="GET" class="flex items-center space-x-2 w-full md:w-auto">
                        <!-- Status Filter Dropdown -->
                        <select name="status" onchange="this.form.submit()" class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-700" title="Filter by status">
                            <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="in-progress" <?php echo ($status_filter === 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo ($status_filter === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        
                        <!-- Search Input with Button -->
                        <div class="flex items-center w-full rounded-full overflow-hidden border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm">
                            <input type="text" name="search" placeholder="Search tasks..." class="w-full px-2 text-sm bg-transparent border-none focus:ring-0 focus:outline-none text-gray-800 dark:text-white" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
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
        <!-- Tasks Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Your Tasks</h2>
                <a href="add_task.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200 transform hover:scale-105">
                    Add Task
                </a>
            </div>
            
            <!-- Task Status Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <?php
                // Count tasks by status
                $count_sql = "SELECT status, COUNT(*) as count FROM tasks WHERE user_id = ? GROUP BY status";
                $count_stmt = $conn->prepare($count_sql);
                $count_stmt->bind_param("i", $user_id);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                
                $pending_count = 0;
                $in_progress_count = 0;
                $completed_count = 0;
                
                while ($count = $count_result->fetch_assoc()) {
                    if ($count['status'] === 'pending') {
                        $pending_count = $count['count'];
                    } elseif ($count['status'] === 'in-progress') {
                        $in_progress_count = $count['count'];
                    } elseif ($count['status'] === 'completed') {
                        $completed_count = $count['count'];
                    }
                }
                ?>
                
                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-red-700 dark:text-red-400">Pending</h3>
                            <p class="text-2xl font-bold text-red-800 dark:text-red-300"><?php echo $pending_count; ?></p>
                        </div>
                        <div class="text-red-500 dark:text-red-400 text-3xl">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-700 dark:text-yellow-400">In Progress</h3>
                            <p class="text-2xl font-bold text-yellow-800 dark:text-yellow-300"><?php echo $in_progress_count; ?></p>
                        </div>
                        <div class="text-yellow-500 dark:text-yellow-400 text-3xl">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-green-700 dark:text-green-400">Completed</h3>
                            <p class="text-2xl font-bold text-green-800 dark:text-green-300"><?php echo $completed_count; ?></p>
                        </div>
                        <div class="text-green-500 dark:text-green-400 text-3xl">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($task_result && $task_result->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while ($task = $task_result->fetch_assoc()): ?>
                        <div class="block border rounded-lg p-6 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 hover:shadow-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-300 transform hover:scale-101 task-card">
                            <div class="flex justify-between items-start">
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($task['title']); ?></h3>
                                <span class="status-badge <?php echo ($task['status'] == 'completed') ? 'bg-green-500' : (($task['status'] == 'in-progress') ? 'bg-yellow-500' : 'bg-red-500'); ?> text-white px-3 py-1 rounded-md">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </div>
                            <p class="text-[17px] text-gray-700 dark:text-gray-300 mt-4 mb-4 leading-relaxed line-clamp-2">
                                <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                            </p>
                            <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <span class="font-semibold"><i class="far fa-calendar-alt mr-1"></i> Due:</span> 
                                    <?php echo date('F j, Y, g:i a', strtotime($task['due_date'])); ?>
                                </div>
                            </div>
                            <div class="flex space-x-2 mt-3">
                                <a href="edit_task.php?id=<?php echo $task['id']; ?>" 
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
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-300 text-center py-8">No tasks found matching your criteria.</p>
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

        function markTaskComplete(taskId) {
            fetch('mark_complete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'task_id=' + encodeURIComponent(taskId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error marking task as complete: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error marking task as complete');
            });
        }

        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                fetch('delete_task.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'task_id=' + encodeURIComponent(taskId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Error deleting task: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting task');
                });
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>

<style>
    /* Modify the hover scale animation */
    .task-card:hover {
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
</style>

<style>
    /* Add this new style for status badges */
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: bold;
        text-transform: capitalize;
    }
</style>