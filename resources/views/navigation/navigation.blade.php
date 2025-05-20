<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        #sidebarToggle .fa-bars {
            transform: rotate(0deg);
            transition: transform 0.3s ease;
        }
        #sidebarToggle .fa-bars:hover {
            transform: rotate(45deg);
        }
        #sidebarToggle.active .fa-bars,
        #sidebarToggle .fa-times:hover {
            transform: rotate(90deg);
        }
        .digital-clock {
            font-family: 'Digital', sans-serif;
            font-weight: bold;
            font-size: 36px;
            color: white;
            margin-right: 0px;
            padding: 10px;
        }
        /* Notification */
        ul {
            list-style: none;
            margin: 0;
            padding: 0;
          }

          .notification-drop {
            font-family: 'Ubuntu', sans-serif;
            color: #444;
          }
          .notification-drop .item {
            padding: 10px;
            font-size: 18px;
            position: relative;
            border-bottom: 1px solid #ddd;
          }
          .notification-drop .item:hover {
            cursor: pointer;
          }
          .notification-drop .item i {
            margin-left: 10px;
          }
          .notification-drop .item ul {
            display: none;
            position: absolute;
            top: 100%;
            background: #fff;
            left: -200px;
            right: 0;
            z-index: 1;
            border-top: 1px solid #ddd;
          }
          .notification-drop .item ul {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: #FFFFFF; /* Updated background color */
            border-radius: 16px; /* Added border radius */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Updated box shadow */
            backdrop-filter: blur(20px);
            border: 1px solid rgba(41, 125, 176, 0.3); /* Updated border */
            font-family: 'Roboto', sans-serif; /* Added font family */
            z-index: 1;
            min-width: 200px; /* Set a minimum width to accommodate content */
            text-align: center;
          }

          .notification-drop .item ul li {
              font-size: 16px;
              padding: 15px;
              border-bottom: 1px solid #ddd;
          }

          .notification-drop .item ul li:last-child {
              border-bottom: none;
          }

          .notification-drop .item .view-all {
              display: block;
              padding: 10px;
              text-align: center;
          }

          /* Responsive adjustments for small screens */
          @media (max-width: 576px) {
              .notification-drop .item ul {
                  right: 0; /* Adjust position for small screens */
                  left: auto;
                  width: 100%; /* Take up full width */
                  max-width: none; /* Remove max-width for full flexibility */
              }
          }
            .notification-drop .item {
              border: none;
            }
          
          .notification-bell{
            font-size: 25px;
            color: white;
            margin-right: 35px;
          }

          .btn__badge {
            background: #FF5D5D;
            color: white;
            font-size: 11px;
            position: absolute;
            top: 2px;
            right: 2px;
            padding:  3px 10px;
            border-radius: 50%;
            margin-right: 18px;
          }

          .pulse-button {
            box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.5);
            -webkit-animation: pulse 1.5s infinite;
          }

          .pulse-button:hover {
            -webkit-animation: none;
          }

          @-webkit-keyframes pulse {
            0% {
              -moz-transform: scale(0.9);
              -ms-transform: scale(0.9);
              -webkit-transform: scale(0.9);
              transform: scale(0.9);
            }
            70% {
              -moz-transform: scale(1);
              -ms-transform: scale(1);
              -webkit-transform: scale(1);
              transform: scale(1);
              box-shadow: 0 0 0 50px rgba(255, 0, 0, 0);
            }
            100% {
              -moz-transform: scale(0.9);
              -ms-transform: scale(0.9);
              -webkit-transform: scale(0.9);
              transform: scale(0.9);
              box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
            }
          }

          .notification-text{
            font-size: 14px;
            font-weight: bold;
          }

          .notification-text span{
            float: right;
          }
    </style>

</head>
<body>
    <nav class="sb-topnav navbar navbar-expand" style="background-color: #2C4B5F;">
      <a class="navbar-brand ps-4" href="#" style="color: white;">
          <img src="{{ asset('admintemplate/assets/img/round.png') }}" alt="Second Logo" style="width: 50px; height: 50px; margin-right: 10px;">
          <img src="{{ asset('admintemplate/assets/img/seal.png') }}" alt="Logo LSHS" style="width: 50px; height: 50px;">
      </a>

        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" style="color: black; background-color: #192d42;">
        <i class="fas fa-bars" style="color: white;"></i>
        </button>
          
        <div class="digital-clock ml-auto">
        <div id="time" style="text-align: right;"></div>
        </div>
          <ul class="notification-drop">
            <li class="item">
              <i class="fa fa-bell fa-lg notification-bell" aria-hidden="true"></i> <span class="btn__badge pulse-button " id = "lowstock"></span>     
              <ul id = "notificationlist">
              </ul>
            </li>
        </ul>
    </nav>
    <script>
        var navbarDropdown = document.getElementById('navbarDropdown');

    
        var sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            var icon = this.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
</script>

                    <script>
                          requesting_notification();
                                function requesting_notification()
                                {
                                    $.ajax({
                                        type: 'get',
                                        url: "{{ route('requestingitems.notification') }}",
                                        dataType: 'json',
                                        success: function(data) {
                        if (data.lowstock > 0) {
                            $("#lowstock").show();
                            $("#lowstock").text(data.lowstock);
                            var html = "<li><b>LIST OF LOW STOCKS</b></li>";
                            for (var i = 0; i < data.lowstocks.length; i++) {
                                if (data.lowstocks[i] && data.lowstocks[i].item && data.lowstocks[i].stock !== undefined) {
                                    // Determine background color based on stock value
                                    let backgroundColor = data.lowstocks[i].stock === 0 
                                        ? "background-color: #ffcccc;" // Red for stock = 0
                                        : (data.lowstocks[i].stock <= 5 ? "background-color: #fff5cc;" : "background-color: transparent;"); // Yellow for stock 1-5

                                    // Use the item's image if available, otherwise use an inline SVG as a placeholder
                                    let imageUrl = data.lowstocks[i].image 
                                        ? "/storage/upload_images/" + data.lowstocks[i].image 
                                        : "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(`
                                            <svg width="50" height="50" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="50" height="50" fill="#ccc"/>
                                                <text x="25" y="30" font-size="12" text-anchor="middle" fill="#666">No Image</text>
                                            </svg>
                                        `);

                                    html += `
                                    <li style="
                                        display: flex; 
                                        align-items: center; 
                                        gap: 15px; 
                                        padding: 10px; 
                                        border-bottom: 1px solid #ddd; 
                                        ${backgroundColor} 
                                        transition: background-color 0.3s, transform 0.2s; 
                                        cursor: pointer;" 
                                        onmouseover="this.style.transform='scale(1.02)';" 
                                        onmouseout="this.style.transform='scale(1)';">
                                        <img src="${imageUrl}" alt="Item Image" style="
                                            width: 50px; 
                                            height: 50px; 
                                            border-radius: 5px; 
                                            object-fit: cover; 
                                            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                                        <div style="
                                            flex-grow: 1; 
                                            font-family: Arial, sans-serif; 
                                            font-size: 14px; 
                                            color: #333;">
                                            <strong style="display: block; font-size: 16px; color: #000;">${data.lowstocks[i].item}</strong>
                                            <span style="color: #666;">Stock: ${data.lowstocks[i].stock}</span>
                                        </div>
                                    </li>
                                    `;
                                }
                            }

                            html += "<li><a class='btn btn-primary btn-sm btn-flat justify-content-center' href='{{ route('items.index') }}'>View All</a></li>";
                            $("#notificationlist").html(html);
                        } else {
                            $("#lowstock").hide();
                            $("#lowstock").html("");
                        }

                        if (data.notif > 0) {
                            $("#notif").show();
                            $("#notif").text(data.notif);
                        } else {
                            $("#notif").hide();
                            $("#notif").html("");
                        }
                    },
                    error: function()
                    {
                        alert("System cannot process request.")
                    }
                })
            }   
            setInterval(() => {
                requesting_notification();
            }, 5000);
       </script>
    </body>
    </script>
</body>
</html>