<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "notesdb";

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

$note_id = isset($_GET['id']) ? $_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Fetch the note
$sql = "SELECT * FROM notes WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $note_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$note = $result->fetch_assoc();

// If note doesn't exist or doesn't belong to user
if (!$note) {
    header("Location: ../display.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note</title>
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
            line-clamp: 3; /* Standard property */
        }
        .line-clamp-2 { 
            -webkit-line-clamp: 2;
            line-clamp: 2; /* Standard property */
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
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-white">
    <!-- Navigation Bar -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg mb-6 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i> Edit Note
                </h1>
                <div class="flex items-center space-x-6">
                    <!-- Welcome Message -->
                    <span class="text-gray-700 dark:text-gray-300 text-sm transition-colors duration-200">
                        Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    </span>
                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2 transition-colors duration-200">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <a href="../display.php" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Notes
                    </a>
                    <!-- Delete Button -->
                    <form action="delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this note?');">
                        <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition" title="Permanently delete this note">
                            Delete Note
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-10">
            <form action="update.php" method="POST" class="space-y-8" enctype="multipart/form-data">
                <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                
                <!-- Title Input -->
                <div>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo htmlspecialchars($note['title']); ?>" 
                           class="w-full text-5xl font-bold text-gray-800 dark:text-white bg-transparent focus:outline-none focus:ring-0 placeholder-gray-400 dark:placeholder-gray-500 hover:scale-[1.02]" 
                           placeholder="Enter a title..." 
                           required>
                </div>
                
                <!-- Content Textarea -->
                <div>
                    <textarea id="content" 
                              name="content" 
                              class="w-full text-xl text-gray-700 dark:text-gray-300 bg-transparent focus:outline-none focus:ring-0 placeholder-gray-400 dark:placeholder-gray-500 leading-relaxed hover:scale-[1.02]" 
                              style="min-height: 600px;" 
                              placeholder="Write your content here..." 
                              required><?php echo htmlspecialchars($note['content']); ?></textarea>
                </div>
                
                <!-- File Attachment Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-3">
                        <i class="fas fa-paperclip mr-2"></i> Attachments
                    </h3>
                    
                    <!-- Display existing attachments -->
                    <?php
                    $attach_sql = "SELECT * FROM attachments WHERE note_id = ?";
                    $attach_stmt = $conn->prepare($attach_sql);
                    $attach_stmt->bind_param("i", $note_id);
                    $attach_stmt->execute();
                    $attachments = $attach_stmt->get_result();
                    
                    if ($attachments->num_rows > 0):
                    ?>
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Current Attachments:</h4>
                        <div class="space-y-2">
                            <?php while ($attachment = $attachments->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>" 
                                   target="_blank"
                                   class="flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                                    <i class="fas fa-file mr-2"></i>
                                    <?php echo htmlspecialchars($attachment['file_name']); ?>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                        (<?php echo round($attachment['file_size'] / 1024, 2); ?> KB)
                                    </span>
                                </a>
                                <a href="delete_attachment.php?id=<?php echo $attachment['id']; ?>&note_id=<?php echo $note_id; ?>" 
                                   class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                   onclick="return confirm('Are you sure you want to delete this attachment?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Upload new attachments -->
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
                            class="flex-1 bg-blue-500 text-white py-3 px-6 rounded-lg hover:bg-blue-600 transition duration-200 font-medium shadow-md"
                            title="Save your changes to this note">
                        Save Changes
                    </button>
                    <a href="../display.php" 
                       class="flex-1 bg-gray-500 text-white py-3 px-6 rounded-lg hover:bg-gray-600 transition duration-200 font-medium text-center shadow-md"
                       title="Return to dashboard without saving changes">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar - now wider and positioned to start after navbar -->
    <div class="fixed top-0 right-0 h-full w-20 bg-white dark:bg-gray-800 z-10 flex flex-col items-center" style="padding-top: 6rem;">
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
        <!-- <button id="rewrite-btn" type="button" class="sidebar-btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-colors duration-200 shadow-md mb-4 w-16 flex flex-col items-center" title="Rewrite your note content in a different style">
            <i class="fas fa-sync-alt mb-1 text-sm"></i>
            <span class="text-xs">Rewrite</span>
        </button> -->
        
        <!-- Essay Generator Button -->
        <!-- <button id="essay-btn" type="button" class="sidebar-btn bg-purple-500 hover:bg-purple-600 text-white p-2 rounded-lg transition-colors duration-200 shadow-md w-16 flex flex-col items-center" title="Generate a 300-word essay based on your title">
            <i class="fas fa-pen-fancy mb-1 text-sm"></i>
            <span class="text-xs">Essay</span>
        </button> -->
    </div>

    <script>
        // Auto-resize textarea as user types
        const textarea = document.getElementById('content');
        textarea.style.height = 'auto'; // Initially set height to auto
        textarea.addEventListener('input', function() {
            this.style.height = 'auto'; // Reset height to auto to shrink if necessary
            this.style.height = (this.scrollHeight) + 'px'; // Adjust the height according to content
        });
        
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

        // Summarize functionality
        document.getElementById('summarize-btn').addEventListener('click', function() {
            const content = document.getElementById('content').value;
            
            if (content.trim().length < 50) {
                alert('Please enter more content to summarize (at least 50 characters).');
                return;
            }
            
            // Change button state to loading
            const summarizeBtn = this;
            const originalBtnText = summarizeBtn.innerHTML;
            summarizeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            summarizeBtn.disabled = true;
            
            // Send request to summarize.php
            const formData = new FormData();
            formData.append('content', content);
            
            fetch('summarize.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Create modal to display summary
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Summary</h3>
                            <button id="close-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="prose dark:prose-invert max-w-none">
                            ${data.summary.replace(/\n/g, '<br>')}
                        </div>
                        <div class="mt-6 flex justify-end space-x-2">
                            <button id="replace-with-summary" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
                                Replace
                            </button>
                            <button id="append-summary" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                Append to Note
                            </button>
                            <button id="close-modal-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                                Close
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Handle close modal
                const closeModal = () => {
                    document.body.removeChild(modal);
                };
                
                document.getElementById('close-modal').addEventListener('click', closeModal);
                document.getElementById('close-modal-btn').addEventListener('click', closeModal);
                
                // Handle append summary
                document.getElementById('append-summary').addEventListener('click', () => {
                    const textarea = document.getElementById('content');
                    textarea.value += '\n\n## Summary\n' + data.summary;
                    textarea.style.height = (textarea.scrollHeight) + 'px';
                    closeModal();
                });
                
                // Handle replace with summary
                document.getElementById('replace-with-summary').addEventListener('click', () => {
                    if (confirm('Are you sure you want to replace the entire content with the summary?')) {
                        const textarea = document.getElementById('content');
                        textarea.value = '## Summary\n' + data.summary;
                        textarea.style.height = (textarea.scrollHeight) + 'px';
                        closeModal();
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to generate summary: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                summarizeBtn.innerHTML = originalBtnText;
                summarizeBtn.disabled = false;
            });
        });

        // Text-to-Speech functionality
        document.getElementById('text-to-speech-btn').addEventListener('click', function() {
            const content = document.getElementById('content').value;
            const title = document.getElementById('title').value;
            const ttsBtn = this;
            
            // Check if speech synthesis is already speaking
            if (window.speechSynthesis.speaking) {
                // If speaking, toggle between pause and resume
                if (window.speechSynthesis.paused) {
                    // If paused, resume speaking
                    window.speechSynthesis.resume();
                    ttsBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
                } else {
                    // If speaking, pause it
                    window.speechSynthesis.pause();
                    ttsBtn.innerHTML = '<i class="fas fa-play"></i> Resume';
                }
                return;
            }
            
            // If not already speaking, start new speech
            if (content.trim().length < 1) {
                alert('Please enter some content to read aloud.');
                return;
            }
            
            // Change button state to indicate playing
            const originalBtnText = ttsBtn.innerHTML;
            ttsBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
            
            // Create a temporary container for highlighted text
            let highlightContainer = document.createElement('div');
            highlightContainer.id = 'tts-highlight-container';
            highlightContainer.className = 'fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 p-5 shadow-xl rounded-lg z-40 text-xl max-h-40 overflow-y-auto w-3/4 md:w-1/2 lg:w-2/5 border border-gray-200 dark:border-gray-700';
            document.body.appendChild(highlightContainer);
            
            // Show the highlight container
            highlightContainer.style.display = 'block';
            
            // Split content into sentences for better highlighting
            const sentences = (title + ". " + content).split(/(?<=[.!?])\s+/);
            let currentSentenceIndex = 0;
            
            // Function to speak the next sentence
            function speakNextSentence() {
                if (currentSentenceIndex < sentences.length) {
                    const sentence = sentences[currentSentenceIndex];
                    
                    // Update highlight container with current sentence
                    highlightContainer.innerHTML = `<div class="bg-yellow-100 dark:bg-yellow-800 p-3 rounded text-gray-800 dark:text-gray-100">${sentence}</div>`;
                    
                    // Create utterance for this sentence
                    const utterance = new SpeechSynthesisUtterance(sentence);
                    utterance.volume = 1;
                    utterance.rate = 1;
                    utterance.pitch = 1;
                    
                    // When this sentence ends, speak the next one
                    utterance.onend = function() {
                        currentSentenceIndex++;
                        speakNextSentence();
                    };
                    
                    // Handle errors
                    utterance.onerror = function() {
                        console.error('Error speaking sentence:', sentence);
                        currentSentenceIndex++;
                        speakNextSentence();
                    };
                    
                    // Speak the sentence
                    window.speechSynthesis.speak(utterance);
                } else {
                    // All sentences have been spoken
                    ttsBtn.innerHTML = originalBtnText;
                    highlightContainer.style.display = 'none';
                    document.body.removeChild(highlightContainer);
                }
            }
            
            // Start speaking
            speakNextSentence();
            
            // Add a close button to the highlight container
            const closeButton = document.createElement('button');
            closeButton.innerHTML = '<i class="fas fa-times"></i>';
            closeButton.className = 'absolute top-2 right-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white';
            closeButton.addEventListener('click', function() {
                window.speechSynthesis.cancel();
                ttsBtn.innerHTML = originalBtnText;
                highlightContainer.style.display = 'none';
                document.body.removeChild(highlightContainer);
            });
            highlightContainer.appendChild(closeButton);
        });
        
        // Essay generation functionality using Gemini
        // document.getElementById('essay-btn').addEventListener('click', function() {
        //     const title = document.getElementById('title').value;
            
        //     if (title.trim().length < 3) {
        //         alert('Please enter a more descriptive title to generate an essay.');
        //         return;
        //     }
            
        //     // Change button state to loading
        //     const essayBtn = this;
        //     const originalBtnText = essayBtn.innerHTML;
        //     essayBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        //     essayBtn.disabled = true;
            
        //     // Send request to essay.php
        //     const formData = new FormData();
        //     formData.append('title', title);
            
        //     fetch('essay.php', {
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(response => {
        //         if (!response.ok) {
        //             throw new Error('Network response was not ok');
        //         }
        //         return response.json();
        //     })
        //     .then(data => {
        //         if (data.error) {
        //             throw new Error(data.error);
        //         }
                
        //         // Create modal to display essay
        //         const modal = document.createElement('div');
        //         modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        //         modal.innerHTML = `
        //             <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        //                 <div class="flex justify-between items-center mb-4">
        //                     <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Generated Essay</h3>
        //                     <button id="close-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
        //                         <i class="fas fa-times"></i>
        //                     </button>
        //                 </div>
        //                 <div class="prose dark:prose-invert max-w-none">
        //                     ${data.essay.replace(/\n/g, '<br>')}
        //                 </div>
        //                 <div class="mt-6 flex justify-end space-x-2">
        //                     <button id="replace-with-essay" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
        //                         Replace
        //                     </button>
        //                     <button id="append-essay" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
        //                         Append to Note
        //                     </button>
        //                     <button id="close-modal-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
        //                         Close
        //                     </button>
        //                 </div>
        //             </div>
        //         `;
                
        //         document.body.appendChild(modal);
                
        //         // Handle close modal
        //         const closeModal = () => {
        //             document.body.removeChild(modal);
        //         };
                
        //         document.getElementById('close-modal').addEventListener('click', closeModal);
        //         document.getElementById('close-modal-btn').addEventListener('click', closeModal);
                
        //         // Handle append essay
        //         document.getElementById('append-essay').addEventListener('click', () => {
        //             const textarea = document.getElementById('content');
        //             textarea.value += '\n\n## Generated Essay\n' + data.essay;
        //             textarea.style.height = (textarea.scrollHeight) + 'px';
        //             closeModal();
        //         });
                
        //         // Handle replace with essay
        //         document.getElementById('replace-with-essay').addEventListener('click', () => {
        //             if (confirm('Are you sure you want to replace the entire content with the generated essay?')) {
        //                 const textarea = document.getElementById('content');
        //                 textarea.value = '## Generated Essay\n' + data.essay;
        //                 textarea.style.height = (textarea.scrollHeight) + 'px';
        //                 closeModal();
        //             }
        //         });
        //     })
        //     .catch(error => {
        //         console.error('Error:', error);
        //         alert('Failed to generate essay: ' + error.message);
        //     })
        //     .finally(() => {
        //         // Reset button state
        //         essayBtn.innerHTML = originalBtnText;
        //         essayBtn.disabled = false;
        //     });
        // });

        // // Rewrite functionality using Gemini
        // document.getElementById('rewrite-btn').addEventListener('click', function() {
        //     const content = document.getElementById('content').value;
            
        //     if (content.trim().length < 50) {
        //         alert('Please enter more content to rewrite (at least 50 characters).');
        //         return;
        //     }
            
        //     // Change button state to loading
        //     const rewriteBtn = this;
        //     const originalBtnText = rewriteBtn.innerHTML;
        //     rewriteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        //     rewriteBtn.disabled = true;
            
        //     // Create style selection modal
        //     const styleModal = document.createElement('div');
        //     styleModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        //     styleModal.innerHTML = `
        //         <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-md w-full">
        //             <div class="flex justify-between items-center mb-4">
        //                 <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Choose Rewrite Style</h3>
        //                 <button id="close-style-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
        //                     <i class="fas fa-times"></i>
        //                 </button>
        //             </div>
        //             <div class="space-y-4">
        //                 <button data-style="professional" class="style-btn w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white p-3 rounded-lg text-left">
        //                     <span class="font-bold">Professional</span>
        //                     <p class="text-sm text-gray-600 dark:text-gray-400">Formal language suitable for business or academic contexts</p>
        //                 </button>
        //                 <button data-style="creative" class="style-btn w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white p-3 rounded-lg text-left">
        //                     <span class="font-bold">Creative</span>
        //                     <p class="text-sm text-gray-600 dark:text-gray-400">Engaging and imaginative with colorful language</p>
        //                 </button>
        //                 <button data-style="simple" class="style-btn w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white p-3 rounded-lg text-left">
        //                     <span class="font-bold">Simple</span>
        //                     <p class="text-sm text-gray-600 dark:text-gray-400">Clear and easy to understand with simpler vocabulary</p>
        //                 </button>
        //                 <button data-style="persuasive" class="style-btn w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white p-3 rounded-lg text-left">
        //                     <span class="font-bold">Persuasive</span>
        //                     <p class="text-sm text-gray-600 dark:text-gray-400">Convincing language designed to influence the reader</p>
        //                 </button>
        //             </div>
        //         </div>
        //     `;
            
        //     document.body.appendChild(styleModal);
            
        //     // Handle close modal
        //     document.getElementById('close-style-modal').addEventListener('click', function() {
        //         document.body.removeChild(styleModal);
        //         rewriteBtn.innerHTML = originalBtnText;
        //         rewriteBtn.disabled = false;
        //     });
            
        //     // Handle style selection
        //     document.querySelectorAll('.style-btn').forEach(button => {
        //         button.addEventListener('click', function() {
        //             const selectedStyle = this.getAttribute('data-style');
        //             document.body.removeChild(styleModal);
                    
        //             // Send request to rewrite.php
        //             const formData = new FormData();
        //             formData.append('content', content);
        //             formData.append('style', selectedStyle);
                    
        //             fetch('rewrite.php', {
        //                 method: 'POST',
        //                 body: formData
        //             })
        //             .then(response => {
        //                 if (!response.ok) {
        //                     throw new Error('Network response was not ok');
        //                 }
        //                 return response.json();
        //             })
        //             .then(data => {
        //                 if (data.error) {
        //                     throw new Error(data.error);
        //                 }
                        
        //                 // Create modal to display rewritten content
        //                 const resultModal = document.createElement('div');
        //                 resultModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        //                 resultModal.innerHTML = `
        //                     <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        //                         <div class="flex justify-between items-center mb-4">
        //                             <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Rewritten Content (${selectedStyle.charAt(0).toUpperCase() + selectedStyle.slice(1)} Style)</h3>
        //                             <button id="close-result-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
        //                                 <i class="fas fa-times"></i>
        //                             </button>
        //                         </div>
        //                         <div class="prose dark:prose-invert max-w-none">
        //                             ${data.rewritten.replace(/\n/g, '<br>')}
        //                         </div>
        //                         <div class="mt-6 flex justify-end space-x-2">
        //                             <button id="replace-with-rewrite" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
        //                                 Replace
        //                             </button>
        //                             <button id="append-rewrite" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
        //                                 Append to Note
        //                             </button>
        //                             <button id="close-result-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
        //                                 Close
        //                             </button>
        //                         </div>
        //                     </div>
        //                 `;
                        
        //                 document.body.appendChild(resultModal);
                        
        //                 // Handle close modal
        //                 const closeResultModal = () => {
        //                     document.body.removeChild(resultModal);
        //                 };
                        
        //                 document.getElementById('close-result-modal').addEventListener('click', closeResultModal);
        //                 document.getElementById('close-result-btn').addEventListener('click', closeResultModal);
                        
        //                 // Handle append rewrite
        //                 document.getElementById('append-rewrite').addEventListener('click', () => {
        //                     const textarea = document.getElementById('content');
        //                     textarea.value += '\n\n## Rewritten Content (' + selectedStyle.charAt(0).toUpperCase() + selectedStyle.slice(1) + ' Style)\n' + data.rewritten;
        //                     textarea.style.height = (textarea.scrollHeight) + 'px';
        //                     closeResultModal();
        //                 });
                        
        //                 // Handle replace with rewrite
        //                 document.getElementById('replace-with-rewrite').addEventListener('click', () => {
        //                     if (confirm('Are you sure you want to replace the entire content with the rewritten version?')) {
        //                         const textarea = document.getElementById('content');
        //                         textarea.value = data.rewritten;
        //                         textarea.style.height = (textarea.scrollHeight) + 'px';
        //                         closeResultModal();
        //                     }
        //                 });
        //             })
        //             .catch(error => {
        //                 console.error('Error:', error);
        //                 alert('Failed to rewrite content: ' + error.message);
        //             })
        //             .finally(() => {
        //                 // Reset button state
        //                 rewriteBtn.innerHTML = originalBtnText;
        //                 rewriteBtn.disabled = false;
        //             });
        //         });
        //     });
        // });
    </script>
</body>
</html>

<?php $conn->close(); ?>
