<script>
    $(document).ready(function(){
        $("#logout").on('click', function(){
            confirm("Do you wish to logout?") ? window.location.href = "{{ route('user.logout') }}" : '';
        })
    })
    $(document).ready(function() {
        $(".notification-drop .item").on('click',function() {
            $(this).find('ul').toggle();
        });
    });

    function responseMessage(type, message) { // 'type' can be 'success' or 'error'
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        if (type === 'success') {
            toastr.success(message || "Operation completed successfully!");
        } else if (type === 'error') {
            toastr.error(message || "An error occurred. Please try again.");
        } else {
            toastr.info(message || "Notification triggered.");
        }
    }
</script>
    

