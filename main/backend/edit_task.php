<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "notesdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $task_sql = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
    $task_stmt = $conn->prepare($task_sql);
    $task_stmt->bind_param("ii", $task_id, $user_id);
    $task_stmt->execute();
    $task_result = $task_stmt->get_result();

    if ($task_result->num_rows > 0) {
        $task = $task_result->fetch_assoc();
    } else {
        header("Location: ../display.php?error=Task not found");
        exit();
    }
} else {
    header("Location: ../display.php?error=Invalid task ID");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    $update_sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssii", $title, $description, $due_date, $status, $task_id, $user_id);

    if ($update_stmt->execute()) {
        header("Location: ../display.php?message=Task updated successfully");
        exit();
    } else {
        echo "Error updating task: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
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
        /* Line clamp utilities */
        .line-clamp-3, .line-clamp-2 { display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; }
        .line-clamp-3 { -webkit-line-clamp: 3; }
        .line-clamp-2 { -webkit-line-clamp: 2; }
        
        /* Custom dark mode styles */
        .dark body { color: #e2e8f0; background-color: #121212; }
        
        /* Text color overrides */
        .dark .dark\:text-white { color: #ffffff !important; }
        .dark .dark\:text-gray-300 { color: #d1d5db !important; }
        .dark .dark\:text-gray-400 { color: #9ca3af !important; }
        
        /* Background color overrides */
        .dark .dark\:bg-gray-900 { background-color: #121212 !important; }
        .dark .dark\:bg-gray-800 { background-color: #1e1e1e !important; }
        .dark .dark\:bg-gray-700 { background-color: #2a2a2a !important; }
        .dark .dark\:hover\:bg-gray-600:hover { background-color: #333333 !important; }
        .dark .dark\:border-gray-600 { border-color: #4b5563 !important; }
        
        /* Dark mode transitions */
        body, .dark-transition { transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
        
        /* Custom styles for enhanced beauty */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }
        
        .status-in-progress {
            background-color: #E0F2FE;
            color: #075985;
        }
        
        .status-completed {
            background-color: #D1FAE5;
            color: #065F46;
        }
        
        .dark .status-pending {
            background-color: #78350F;
            color: #FEF3C7;
        }
        
        .dark .status-in-progress {
            background-color: #0C4A6E;
            color: #E0F2FE;
        }
        
        .dark .status-completed {
            background-color: #064E3B;
            color: #D1FAE5;
        }
        
        .input-focus-effect:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
        
        .dark .input-focus-effect:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.5);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-white">
    <!-- Navigation Bar -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg mb-6 transition-colors duration-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Left Section: Title -->
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white transition-colors duration-200 flex items-center">
                    <i class="fas fa-tasks mr-2"></i> Edit Task
                </h1>

                <!-- Right Section: User Info and Actions -->
                <div class="flex items-center space-x-6">
                    <!-- Welcome Message -->
                    <span class="text-gray-700 dark:text-gray-300 text-sm transition-colors duration-200">
                        Welcome, <strong class="text-blue-600 dark:text-blue-400"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    </span>

                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-700 rounded-lg text-sm p-2.5 transition-colors duration-200">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- Back to Dashboard Button -->
                    <a href="../display.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center shadow-md">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4">
        <!-- Current Status Badge - Floating at top right -->
        <div class="flex justify-end mb-4">
            <span class="status-badge <?php echo ($task['status'] == 'pending') ? 'status-pending' : (($task['status'] == 'in-progress') ? 'status-in-progress' : 'status-completed'); ?>">
                <?php echo ucfirst($task['status']); ?>
            </span>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-6">
            <form action="edit_task.php?id=<?php echo $task_id; ?>" method="POST" class="space-y-8">
                <!-- Title Input -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Task Title</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo htmlspecialchars($task['title']); ?>" 
                           class="w-full text-2xl font-bold text-gray-800 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect" 
                           placeholder="Enter a title..." 
                           required>
                </div>
                
                <!-- Description Textarea -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Task Description</label>
                    <textarea id="description" 
                              name="description" 
                              class="w-full text-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect leading-relaxed" 
                              style="min-height: 200px;" 
                              placeholder="Enter a description..."><?php echo htmlspecialchars($task['description']); ?></textarea>
                </div>
                
                <!-- Due Date Input -->
                <div class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                        <i class="far fa-calendar-alt mr-2 text-blue-500 dark:text-blue-400"></i>
                        Due Date
                    </label>
                    <input type="datetime-local" 
                           id="due_date" 
                           name="due_date" 
                           value="<?php echo date('Y-m-d\TH:i', strtotime($task['due_date'])); ?>" 
                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect">
                </div>
                
                <!-- Status Dropdown -->
                <div class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                        <i class="fas fa-tasks mr-2 text-blue-500 dark:text-blue-400"></i>
                        Status
                    </label>
                    <div class="relative">
                        <select id="status" 
                                name="status" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect appearance-none">
                            <option value="pending" <?php echo ($task['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="in-progress" <?php echo ($task['status'] == 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo ($task['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" 
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 px-6 rounded-lg transition duration-200 font-medium flex items-center justify-center shadow-md">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                    <a href="../display.php" 
                       class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-lg transition duration-200 font-medium text-center flex items-center justify-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Task Metadata Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                <i class="fas fa-info-circle mr-2 text-blue-500"></i> Task Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
                        <i class="fas fa-calendar-plus text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                        <p class="font-medium"><?php echo date('F j, Y', strtotime($task['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="bg-purple-100 dark:bg-purple-900/30 p-3 rounded-full">
                        <i class="fas fa-clock text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Due Date</p>
                        <p class="font-medium"><?php echo date('F j, Y, g:i a', strtotime($task['due_date'])); ?></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-full">
                        <i class="fas fa-tag text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                        <p class="font-medium capitalize"><?php echo $task['status']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <div class="fixed bottom-6 right-6">
        <button type="button" onclick="window.location.href='../display.php'" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
            <i class="fas fa-home"></i>
        </button>
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
        
        // Auto-resize textarea
        const textarea = document.getElementById('description');
        textarea.style.height = (textarea.scrollHeight) + 'px';
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Add subtle animation to the form elements
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('input, textarea, select, button');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 100 + (index * 50));
            });
        });
    </script>
</body>
</html>