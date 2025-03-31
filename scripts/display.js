document.addEventListener('DOMContentLoaded', function() {
    // The form will now auto-submit on date change, but we'll keep this handler
    // for browsers that might not support the onchange attribute
    const dateInput = document.getElementById('scheduleDate');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const dateForm = document.getElementById('dateSelectForm');
            if (dateForm) {
                dateForm.submit();
            }
        });
    }
});

function deleteNote(noteId) {
    if (confirm('Are you sure you want to delete this note?')) {
        fetch('../main/backend/delete.php', {
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

function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this task?')) {
        fetch('../main/backend/delete_task.php', {
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

function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this event?')) {
        fetch('../main/backend/delete_event.php', {
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

function markTaskComplete(taskId) {
    fetch('../main/backend/mark_complete.php', {
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

// Dark mode toggle functionality
var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
var htmlElement = document.documentElement;

// Initial state setup
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
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