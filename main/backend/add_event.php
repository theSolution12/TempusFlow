<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
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
        body, .dark-transition { transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
        
        /* Custom styles for enhanced beauty */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transform: translateY(0);
        }

        .status-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .glass-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.15);
        }

        .dark .glass-effect {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Form element enhancements */
        input, textarea, select {
            transition: all 0.3s ease;
        }

        input:hover, textarea:hover, select:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dark input:hover, .dark textarea:hover, .dark select:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        
        .event-badge {
            background-color: #E0F2FE;
            color: #075985;
        }
        
        .dark .event-badge {
            background-color: #0C4A6E;
            color: #E0F2FE;
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
                    <i class="fas fa-calendar-plus mr-2"></i> Add New Event
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
        <!-- Event Badge - Floating at top right -->
        <div class="flex justify-end mb-4">
            <span class="status-badge event-badge">
                Event
            </span>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-6">
            <form action="process_event.php" method="POST" class="space-y-4">
                <!-- Event Title -->
                <div class="glass-effect p-4 rounded-lg transition-all duration-300">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                        <i class="fas fa-heading mr-2 text-blue-500 dark:text-blue-400"></i>
                        Event Title
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           class="w-full text-2xl font-bold text-gray-800 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect transform transition-all duration-300 hover:shadow-lg" 
                           placeholder="Enter event title..." 
                           required>
                </div>
                
                <!-- Description Textarea -->
                <div class="glass-effect p-4 rounded-lg transition-all duration-300">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                        <i class="fas fa-align-left mr-2 text-blue-500 dark:text-blue-400"></i>
                        Event Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              class="w-full text-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect leading-relaxed transform transition-all duration-300 hover:shadow-lg" 
                              style="min-height: 200px;" 
                              placeholder="Enter event description..."></textarea>
                </div>
                
                <!-- Event Date Input -->
                <div class="glass-effect p-4 rounded-lg transition-all duration-300">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm transition-all duration-300 hover:shadow-md">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                            <i class="far fa-calendar-alt mr-2 text-blue-500 dark:text-blue-400"></i>
                            Event Date
                        </label>
                        <input type="date" 
                               id="event_date" 
                               name="event_date" 
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect"
                               required>
                    </div>
                </div>
                
                <!-- Event Time Inputs -->
                <div class="glass-effect p-4 rounded-lg transition-all duration-300">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm transition-all duration-300 hover:shadow-md">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                            <i class="far fa-clock mr-2 text-blue-500 dark:text-blue-400"></i>
                            Event Time
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Start Time -->
                            <div>
                                <label for="event_time" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Start Time</label>
                                <input type="time" 
                                       id="event_time" 
                                       name="event_time" 
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect"
                                       required>
                            </div>
                            
                            <!-- End Time -->
                            <div>
                                <label for="event_end_time" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">End Time (Optional)</label>
                                <input type="time" 
                                       id="event_end_time" 
                                       name="event_end_time" 
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Location Input -->
                <div class="glass-effect p-4 rounded-lg transition-all duration-300">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm transition-all duration-300 hover:shadow-md">
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-blue-500 dark:text-blue-400"></i>
                            Location (Optional)
                        </label>
                        <input type="text" 
                               id="location" 
                               name="location" 
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 input-focus-effect"
                               placeholder="Enter event location...">
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="glass-effect p-4 rounded-lg transition-all duration-300">
                    <div class="flex gap-4">
                        <button type="submit" 
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 px-6 rounded-lg transition duration-200 font-medium flex items-center justify-center shadow-md">
                            <i class="fas fa-plus mr-2"></i> Add Event
                        </button>
                        <a href="../display.php" 
                           class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-lg transition duration-200 font-medium text-center flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
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
        
        // Auto-resize textarea
        const textarea = document.getElementById('description');
        textarea.style.height = (textarea.scrollHeight) + 'px';
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Time preset functionality
        document.querySelectorAll('.time-preset').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('event_time').value = this.getAttribute('data-time');
                
                // Highlight the selected preset
                document.querySelectorAll('.time-preset').forEach(btn => {
                    btn.classList.remove('bg-blue-200', 'dark:bg-blue-800', 'text-blue-800', 'dark:text-blue-200');
                    btn.classList.add('bg-gray-200', 'dark:bg-gray-600', 'text-gray-700', 'dark:text-gray-300');
                });
                
                this.classList.remove('bg-gray-200', 'dark:bg-gray-600', 'text-gray-700', 'dark:text-gray-300');
                this.classList.add('bg-blue-200', 'dark:bg-blue-800', 'text-blue-800', 'dark:text-blue-200');
            });
        });
        
        // Set default date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            document.getElementById('event_date').value = formattedDate;
            
            // Add subtle animation to the form elements
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