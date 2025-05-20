<div class="notification-system">
    <div class="notification-bell-container">
        <i class="fa fa-bell fa-lg notification-bell" aria-hidden="true"></i>
        <span class="notification-badge" id="notification-badge" style="display: none;"></span>
        <div class="notification-dropdown" id="notification-dropdown" style="display: none;">
            <div class="notification-header">
                <h5>Notifications</h5>
            </div>
            <div class="notification-content" id="notification-content">
                <!-- Notification items will be loaded here -->
                <div class="notification-loading">
                    <i class="fa fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
            <div class="notification-footer">
                <a href="{{ route('items.index') }}" class="btn btn-primary btn-sm">View All Items</a>
            </div>
        </div>
    </div>
</div>

<style>
    .notification-system {
        position: relative;
        margin-right: 20px;
    }

    .notification-bell-container {
        position: relative;
        cursor: pointer;
    }

    .notification-bell {
        font-size: 25px;
        color: white;
    }

    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #FF5D5D;
        color: white;
        font-size: 11px;
        padding: 3px 6px;
        border-radius: 50%;
        min-width: 18px;
        text-align: center;
        box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.5);
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.9);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(255, 0, 0, 0);
        }
        100% {
            transform: scale(0.9);
            box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
        }
    }

    .notification-dropdown {
        position: absolute;
        top: 40px;
        right: -10px;
        width: 300px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        max-height: 400px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .notification-header {
        padding: 10px 15px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        text-align: center;
    }

    .notification-header h5 {
        margin: 0;
        color: #333;
        font-size: 16px;
        font-weight: 600;
    }

    .notification-content {
        overflow-y: auto;
        max-height: 300px;
        padding: 0;
    }

    .notification-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item-image {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        margin-right: 10px;
        object-fit: cover;
    }

    .notification-item-content {
        flex: 1;
    }

    .notification-item-title {
        font-weight: 600;
        margin-bottom: 2px;
        color: #333;
        font-size: 14px;
    }

    .notification-item-subtitle {
        color: #666;
        font-size: 12px;
    }

    .notification-footer {
        padding: 10px;
        text-align: center;
        border-top: 1px solid #eee;
    }

    .notification-empty {
        padding: 20px;
        text-align: center;
        color: #666;
    }

    .notification-loading {
        padding: 20px;
        text-align: center;
        color: #666;
    }

    .stock-critical {
        background-color: #ffebee;
    }

    .stock-warning {
        background-color: #fff8e1;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .notification-dropdown {
            width: 280px;
            right: -70px;
        }
    }
</style>

<script>
$(document).ready(function() {
    const bellContainer = $('.notification-bell-container');
    const dropdown = $('#notification-dropdown');
    const badge = $('#notification-badge');
    const content = $('#notification-content');

    // Toggle dropdown when clicking the bell
    bellContainer.on('click', function(e) {
        e.stopPropagation();
        dropdown.toggle();

        if (dropdown.is(':visible')) {
            loadNotifications();
        }
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!bellContainer.is(e.target) && bellContainer.has(e.target).length === 0) {
            dropdown.hide();
        }
    });

    // Function to load notifications
    function loadNotifications() {
        content.html('<div class="notification-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');

        // Use a simple timeout to prevent UI from hanging if request fails
        var notificationTimeout = setTimeout(function() {
            content.html('<div class="notification-empty">Request timed out</div>');
        }, 10000); // 10 second timeout

        $.ajax({
            url: '{{ route("notifications.get") }}',
            type: 'GET',
            dataType: 'json',
            cache: false, // Prevent caching
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                clearTimeout(notificationTimeout);

                try {
                    if (data && data.success) {
                        updateNotificationBadge(data.lowStockCount || 0);
                        updateNotificationContent(data.lowStockItems || []);
                    } else {
                        badge.hide();
                        content.html('<div class="notification-empty">No notifications available</div>');
                    }
                } catch (e) {
                    console.error('Error processing notification data:', e);
                    badge.hide();
                    content.html('<div class="notification-empty">Error processing data</div>');
                }
            },
            error: function(xhr, status, error) {
                clearTimeout(notificationTimeout);
                console.error('Error fetching notifications:', error);
                badge.hide();
                content.html('<div class="notification-empty">Could not load notifications</div>');
            }
        });
    }

    // Function to update the notification badge
    function updateNotificationBadge(count) {
        if (count > 0) {
            badge.text(count);
            badge.show();
        } else {
            badge.hide();
        }
    }

    // Function to update notification content
    function updateNotificationContent(items) {
        if (!items || items.length === 0) {
            content.html('<div class="notification-empty">No notifications</div>');
            return;
        }

        let html = '';

        $.each(items, function(index, item) {
            // Determine background color based on stock level
            let bgColor = '';
            if (item.stock === 0) {
                bgColor = 'background-color: #ffebee;'; // Red for stock = 0
            } else if (item.stock <= 5) {
                bgColor = 'background-color: #fff8e1;'; // Yellow for stock 1-5
            }

            // Create placeholder image URL if no image exists
            let imageUrl = item.image
                ? '/storage/upload_images/' + item.image
                : 'data:image/svg+xml;charset=UTF-8,%3Csvg width="40" height="40" xmlns="http://www.w3.org/2000/svg"%3E%3Crect width="40" height="40" fill="%23eee"/%3E%3Ctext x="20" y="25" font-size="10" text-anchor="middle" fill="%23999"%3ENo Image%3C/text%3E%3C/svg%3E';

            html += '<div class="notification-item" style="' + bgColor + '">';
            html += '<img src="' + imageUrl + '" alt="' + item.item + '" class="notification-item-image">';
            html += '<div class="notification-item-content">';
            html += '<div class="notification-item-title">' + item.item + '</div>';
            html += '<div class="notification-item-subtitle">';
            html += 'Stock: <strong>' + item.stock + '</strong>';
            if (item.supplier_name) {
                html += ' | Supplier: ' + item.supplier_name;
            }
            html += '</div></div></div>';
        });

        content.html(html);
    }

    // Initial load and periodic refresh
    loadNotifications();
    setInterval(loadNotifications, 60000); // Refresh every 60 seconds
});
</script>
