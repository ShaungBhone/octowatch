// Real-time notifications with Laravel Echo and Filament
import './echo';

document.addEventListener('DOMContentLoaded', function () {
    // Listen for comment events on public channel
    window.Echo?.channel('repository')
        .listen('.comment.created', (e) => {
            console.log('New comment received:', e);
            showFilamentNotification(e);
        });

    // Listen for user-specific notifications on private channel
    if (window.userAuth?.id) {
        window.Echo?.private(`App.Models.User.${window.userAuth.id}`)
            .notification((notification) => {
                console.log('User notification received:', notification);
                showFilamentNotification(notification);
            });
    }

    // Listen for repository-specific comment notifications
    if (window.repositoryId) {
        window.Echo?.channel(`repository.${window.repositoryId}`)
            .listen('.comment.created', (e) => {
                console.log('Repository comment received:', e);
                showFilamentNotification(e);
            });
    }
});

function showFilamentNotification(data) {
    // Use Filament's notification system if available
    if (window.FilamentNotification) {
        new window.FilamentNotification()
            .title(data.title || 'New Comment')
            .body(data.body || `Comment by ${data.author_login}`)
            .success()
            .duration(8000)
            .send();
    } else if (window.$wire) {
        // Fallback to Livewire dispatch
        window.$wire.dispatch('notify', {
            title: data.title || 'New Comment',
            body: data.body || `Comment by ${data.author_login}`,
            type: 'success'
        });
    } else {
        // Fallback to browser notification
        if (Notification.permission === 'granted') {
            new Notification(data.title || 'New Comment', {
                body: data.body || `Comment by ${data.author_login}`,
                icon: data.author_avatar_url || '/favicon.ico'
            });
        }
    }
}

// Request browser notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
