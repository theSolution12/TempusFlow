<?php
require_once '../config/config.php';
requireLogin();

$conn = getDBConnection();
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $user_id = $_SESSION['user_id'];
    
    // Handle file uploads
    $attachments = [];
    if (isset($_FILES['attachment'])) {
        foreach ($_FILES['attachment']['tmp_name'] as $key => $tmp_name) {
            $file = [
                'name' => $_FILES['attachment']['name'][$key],
                'type' => $_FILES['attachment']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['attachment']['error'][$key],
                'size' => $_FILES['attachment']['size'][$key]
            ];
            
            $validation = validateFileUpload($file);
            if (!$validation['success']) {
                $error = $validation['message'];
                break;
            }
            
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $attachments[] = $new_filename;
            }
        }
    }
    
    if (empty($error)) {
        // Insert note into database
        $sql = "INSERT INTO notes (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $title, $content);
        
        if ($stmt->execute()) {
            $note_id = $conn->insert_id;
            
            // Insert attachments if any
            if (!empty($attachments)) {
                $attachment_sql = "INSERT INTO attachments (note_id, filename) VALUES (?, ?)";
                $attachment_stmt = $conn->prepare($attachment_sql);
                
                foreach ($attachments as $filename) {
                    $attachment_stmt->bind_param("is", $note_id, $filename);
                    $attachment_stmt->execute();
                }
                
                $attachment_stmt->close();
            }
            
            $success = "Note created successfully!";
        } else {
            $error = "Error creating note: " . $conn->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Note</title>
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
        .line-clamp-3 { 
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }
        .line-clamp-2 { 
            -webkit-line-clamp: 2;
            line-clamp: 2;
        }
        
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
        body, .dark-transition { 
            transition: background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        color 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        border-color 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        /* Enhanced input and textarea styling */
        input, textarea {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: top left;
        }
        
        input:hover, textarea:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }
        
        input:focus, textarea:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            transform: scale(1.005);
        }
        
        .dark input:focus, .dark textarea:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.3);
            transform: scale(1.005);
        }
        
        /* Keyframe animations */
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
        button, a.button {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        
        button:hover, a.button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        button:active, a.button:active {
            transform: translateY(1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        button::after, a.button::after {
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
        
        button:active::after, a.button:active::after {
            transform: translate(-50%, -50%) scale(2);
            opacity: 0;
            transition: 0s;
        }
        
        /* Sidebar button animations */
        .sidebar-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
        }
        
        .sidebar-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .sidebar-btn:active {
            transform: scale(0.98);
            animation: pulse 0.2s ease;
        }
    </style></head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-white">
    <!-- Navigation Bar -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg mb-6 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white transition-colors duration-200">
                    <i class="fas fa-plus-circle mr-2"></i> Create Note
                </h1>
                <div class="flex items-center space-x-6">
                    <!-- Welcome Message -->
                    <span class="text-gray-700 dark:text-gray-300 text-sm transition-colors duration-200">
                        Welcome, <strong class="text-blue-600 dark:text-blue-400"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    </span>

                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2 transition-colors duration-200">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <a href="display.php" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition-colors duration-200 shadow-md">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-10">
            <form action="" method="POST" class="space-y-8" enctype="multipart/form-data" id="noteForm">
                <!-- Title Input -->
                <div>
                    <input type="text" style="border-radius: 12px;" 
                           id="title" 
                           name="title" 
                           class="w-full text-5xl pl-4 font-bold text-gray-800 dark:text-white bg-transparent focus:outline-none focus:ring-0 placeholder-gray-400 dark:placeholder-gray-500 hover:scale-[1.02]" 
                           placeholder="Enter a title..." 
                           required>
                </div>
                
                <!-- Content Textarea -->
                <div>
                    <textarea id="content" 
                              name="content" 
                              class="w-full text-xl pl-4 text-gray-700 dark:text-gray-300 bg-transparent focus:outline-none focus:ring-0 placeholder-gray-400 dark:placeholder-gray-500 leading-relaxed hover:scale-[1.02]" 
                              style="min-height: 600px; border-radius: 12px;" 
                              placeholder="Write your content here..." 
                              required></textarea>
                </div>
                
                <!-- File Attachment Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-3">
                        <i class="fas fa-paperclip mr-2"></i> Attachments
                    </h3>
                    <div class="flex items-center space-x-2">
                        <input type="file" 
                               id="attachment" 
                               name="attachment[]" 
                               class="block w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700
                                      dark:file:bg-blue-900 dark:file:text-blue-200
                                      hover:file:bg-blue-100 dark:hover:file:bg-blue-800"
                               multiple>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Max 5MB per file
                        </span>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="flex gap-4">
                    <button type="submit" 
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 px-6 rounded-lg transition duration-200 font-medium shadow-md">
                        <i class="fas fa-save mr-2"></i> Save Note
                    </button>
                    <a href="display.php" 
                       class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-lg transition duration-200 font-medium text-center shadow-md">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar - positioned to be overlapped by navbar -->
    <div class="fixed top-0 right-0 h-full w-20 bg-white dark:bg-gray-800 shadow-lg z-10 flex flex-col items-center" style="padding-top: 6rem;">
        <!-- Text-to-Speech Button -->
        <button id="text-to-speech-btn" type="button" class="sidebar-btn bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg transition-colors duration-200 shadow-md mb-4 w-16 flex flex-col items-center" title="Read your note content aloud">
            <i class="fas fa-volume-up mb-1 text-sm"></i>
            <span class="text-xs">Read</span>
        </button>
        
        <!-- Summarize Button -->
        <button id="summarize-btn" type="button" class="sidebar-btn bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition-colors duration-200 shadow-md mb-4 w-16 flex flex-col items-center" title="Generate a concise summary of your note content">
            <i class="fas fa-robot mb-1 text-sm"></i>
            <span class="text-xs">Summarize</span>
        </button>
        
        <!-- Rewrite Button -->
        <button id="rewrite-btn" type="button" class="sidebar-btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-colors duration-200 shadow-md mb-4 w-16 flex flex-col items-center" title="Rewrite your note content in a different style">
            <i class="fas fa-sync-alt mb-1 text-sm"></i>
            <span class="text-xs">Rewrite</span>
        </button>
        
        <!-- Essay Generator Button -->
        <button id="essay-btn" type="button" class="sidebar-btn bg-purple-500 hover:bg-purple-600 text-white p-2 rounded-lg transition-colors duration-200 shadow-md w-16 flex flex-col items-center" title="Generate a 300-word essay based on your title">
            <i class="fas fa-pen-fancy mb-1 text-sm"></i>
            <span class="text-xs">Essay</span>
        </button>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
            themeToggleDarkIcon.classList.add('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
            themeToggleLightIcon.classList.add('hidden');
        }

        const themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            // Toggle icons
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // If is set in localstorage
            if (localStorage.getItem('color-theme')) {
                if (localStorage.getItem('color-theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            }
        });

        document.getElementById('noteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('backend/process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4';
                    successDiv.textContent = data.message;
                    document.querySelector('.max-w-7xl').insertBefore(successDiv, document.querySelector('.bg-white'));
                    
                    // Clear form
                    this.reset();
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'display.php';
                    }, 2000);
                } else {
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
                    errorDiv.textContent = data.message;
                    document.querySelector('.max-w-7xl').insertBefore(errorDiv, document.querySelector('.bg-white'));
                }
            })
            .catch(error => {
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
                errorDiv.textContent = 'An error occurred. Please try again.';
                document.querySelector('.max-w-7xl').insertBefore(errorDiv, document.querySelector('.bg-white'));
            });
        });
    </script>
</body>
</html>
