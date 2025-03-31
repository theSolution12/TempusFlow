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

// Add after the existing dark mode toggle code

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
    
    fetch('../main/backend/summarize.php', {
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
document.getElementById('essay-btn').addEventListener('click', function() {
    const title = document.getElementById('title').value;
    
    if (title.trim().length < 3) {
        alert('Please enter a more descriptive title to generate an essay.');
        return;
    }
    
    // Change button state to loading
    const essayBtn = this;
    const originalBtnText = essayBtn.innerHTML;
    essayBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    essayBtn.disabled = true;
    
    // Send request to essay.php
    const formData = new FormData();
    formData.append('title', title);
    
    fetch('../main/backend/essay.php', {
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
        
        // Create modal to display essay
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Generated Essay</h3>
                    <button id="close-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="prose dark:prose-invert max-w-none">
                    ${data.essay.replace(/\n/g, '<br>')}
                </div>
                <div class="mt-6 flex justify-end space-x-2">
                    <button id="replace-with-essay" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
                        Replace
                    </button>
                    <button id="append-essay" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
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
        
        // Handle append essay
        document.getElementById('append-essay').addEventListener('click', () => {
            const textarea = document.getElementById('content');
            textarea.value += '\n\n## Generated Essay\n' + data.essay;
            textarea.style.height = (textarea.scrollHeight) + 'px';
            closeModal();
        });
        
        // Handle replace with essay
        document.getElementById('replace-with-essay').addEventListener('click', () => {
            if (confirm('Are you sure you want to replace the entire content with the generated essay?')) {
                const textarea = document.getElementById('content');
                textarea.value = '## Generated Essay\n' + data.essay;
                textarea.style.height = (textarea.scrollHeight) + 'px';
                closeModal();
            }
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate essay: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        essayBtn.innerHTML = originalBtnText;
        essayBtn.disabled = false;
    });
});

// Rewrite functionality
document.getElementById('rewrite-btn').addEventListener('click', function() {
    const content = document.getElementById('content').value;
    
    if (content.trim().length < 50) {
        alert('Please enter more content to rewrite (at least 50 characters).');
        return;
    }
    
    // Change button state to loading
    const rewriteBtn = this;
    const originalBtnText = rewriteBtn.innerHTML;
    rewriteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    rewriteBtn.disabled = true;
    
    // Create style selection modal
    const styleModal = document.createElement('div');
    styleModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    styleModal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Choose Rewrite Style</h3>
                <button id="close-style-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <button data-style="professional" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-lg transition duration-200">Professional</button>
                <button data-style="casual" class="w-full bg-green-500 hover:bg-green-600 text-white py-3 px-4 rounded-lg transition duration-200">Casual</button>
                <button data-style="academic" class="w-full bg-purple-500 hover:bg-purple-600 text-white py-3 px-4 rounded-lg transition duration-200">Academic</button>
                <button data-style="creative" class="w-full bg-pink-500 hover:bg-pink-600 text-white py-3 px-4 rounded-lg transition duration-200">Creative</button>
                <button data-style="simplified" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-3 px-4 rounded-lg transition duration-200">Simplified</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(styleModal);
    
    // Handle close style modal
    document.getElementById('close-style-modal').addEventListener('click', () => {
        document.body.removeChild(styleModal);
        rewriteBtn.innerHTML = originalBtnText;
        rewriteBtn.disabled = false;
    });
    
    // Handle style selection
    const styleButtons = styleModal.querySelectorAll('button[data-style]');
    styleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const style = button.getAttribute('data-style');
            document.body.removeChild(styleModal);
            
            // Send request to rewrite.php
            const formData = new FormData();
            formData.append('content', content);
            formData.append('style', style);
            
            fetch('../main/backend/rewrite.php', {
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
                
                // Create modal to display rewritten content
                const resultModal = document.createElement('div');
                resultModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                resultModal.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Rewritten Content (${style})</h3>
                            <button id="close-result-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="prose dark:prose-invert max-w-none">
                            ${data.rewritten.replace(/\n/g, '<br>')}
                        </div>
                        <div class="mt-6 flex justify-end space-x-2">
                            <button id="replace-with-rewritten" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
                                Replace
                            </button>
                            <button id="append-rewritten" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                Append to Note
                            </button>
                            <button id="close-result-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                                Close
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(resultModal);
                
                // Handle close result modal
                const closeResultModal = () => {
                    document.body.removeChild(resultModal);
                };
                
                document.getElementById('close-result-modal').addEventListener('click', closeResultModal);
                document.getElementById('close-result-btn').addEventListener('click', closeResultModal);
                
                // Handle append rewritten
                document.getElementById('append-rewritten').addEventListener('click', () => {
                    const textarea = document.getElementById('content');
                    textarea.value += '\n\n## Rewritten Content\n' + data.rewritten;
                    textarea.style.height = (textarea.scrollHeight) + 'px';
                    closeResultModal();
                });
                
                // Handle replace with rewritten
                document.getElementById('replace-with-rewritten').addEventListener('click', () => {
                    if (confirm('Are you sure you want to replace the entire content with the rewritten version?')) {
                        const textarea = document.getElementById('content');
                        textarea.value = data.rewritten;
                        textarea.style.height = (textarea.scrollHeight) + 'px';
                        closeResultModal();
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to rewrite content: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                rewriteBtn.innerHTML = originalBtnText;
                rewriteBtn.disabled = false;
            });
        });
    });
});