<style>
     .nav a {
         border-bottom: 3px solid transparent;
         color: #333; /* Default text color */
         transition: all 0.3s ease; /* Smooth transition for all properties */
     }

     .nav a:hover {
         border-bottom-color:#2C4B5F; /* Highlight the bottom border on hover */
         background-color: #eee; /* Background color on hover */
         color: #338ecf; /* Text color on hover */
     }

     .nav a.active {
         border-bottom-color: #2C4B5F; /* Highlight the active link with the same color as hover */
         background-color: #eee; /* Background color for active link */
         color: #338ecf; /* Text color for active link */
     }

</style>

<nav class="sb-sidenav accordion sb-sidenav-success" id="sidenavAccordion" style = "background-color: #83b2b7" >
    <div class="sb-sidenav-menu">
        <div class="nav">
        <div class="sb-sidenav-menu-heading">Core</div>
        <a class="nav-link" id = "s_dashboard" href="{{ route('admin.home') }}" >
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            &nbsp;&nbsp;
            Dashboard
        </a>
        <a class="nav-link" id = "s_items" href="{{ route('items.index') }}" >
            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
            &nbsp;&nbsp;
            Manage Items&nbsp;&nbsp;
        </a>
        <!-- <a class="nav-link" id = "s_reqs" href="{{ route('requisitions.index') }}" >
            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
            &nbsp;&nbsp;
            Requisition
        </a> -->
        <a class="nav-link" id = "s_ritems" href="{{ route('requestingitems.index') }}" >
            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
            &nbsp;&nbsp;
            Requisition&nbsp;&nbsp; <span class = 'badge badge-primary' id = "notif" style ="display: none">0</span>
        </a>
        <a class="nav-link" id = "s_mr" href="{{ route('admin.monthlyreport') }}" >
            <div class="sb-nav-link-icon">&nbsp;<i class="fas fa-file"></i></div>
            &nbsp;&nbsp;
            Reports
        </a>
        <div class="sb-sidenav-menu-heading">User</div>
            <a class="nav-link" id = "s_departments" href="{{ route('departments.index') }}" >
                <div class="sb-nav-link-icon"><i class="fas fa-building"></i></div>
                &nbsp;&nbsp;
                Departments
            </a>
            <a class="nav-link" id = "s_profiles" href="{{ route('users.adminprofile') }}" >
                <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                &nbsp;&nbsp;
                My Profile
            </a>
            <a class="nav-link" href="#" id = "logout">
                <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                &nbsp;&nbsp;
                Logout
            </a>
        </div>
        </div>
        <div class="sb-sidenav-footer" style = "font-size: 13px">
        <div class="small">Logged in as: </div>
        <b>{{ Auth::user()->fullname }}</b>
    </div>
</nav>

<script>
$(document).ready(function() {
    // Function to load requisition notifications
    function loadRequisitionNotifications() {
        $.ajax({
            url: '{{ route("requestingitems.notification") }}',
            type: 'GET',
            dataType: 'json',
            cache: false,
            timeout: 3000, // 3 second timeout
            beforeSend: function() {
                console.log('Loading requisition notifications...');
            },
            success: function(data) {
                try {
                    console.log('Requisition notification data received:', data);
                    if (data && typeof data.notif !== 'undefined') {
                        updateRequisitionBadge(data.notif);
                        console.log('Updated requisition badge with count:', data.notif);
                    } else {
                        console.log('No valid notification data received');
                        $("#notif").hide();
                    }
                } catch (e) {
                    console.error('Error processing requisition notification data:', e);
                    $("#notif").hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching requisition notifications:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                // Don't show error to user, just hide the badge
                $("#notif").hide();
            }
        });
    }

    // Function to update the requisition notification badge
    function updateRequisitionBadge(count) {
        const badge = $("#notif");
        console.log('Updating badge with count:', count);
        if (count > 0) {
            badge.text(count);
            badge.show();
            console.log('Badge shown with count:', count);
        } else {
            badge.hide();
            console.log('Badge hidden');
        }
    }

    // Make the function globally available so it can be called from other scripts
    window.loadRequisitionNotifications = loadRequisitionNotifications;

    // Initial load and periodic refresh for requisition notifications
    loadRequisitionNotifications();
    setInterval(loadRequisitionNotifications, 30000); // Refresh every 30 seconds
});
</script>
