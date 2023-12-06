<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require('inc/links.php') ?>
    <title><?php echo $settings_r['site_title'] ?> - Confirm Booking</title>
</head>
<body class="bg-light">
    <?php require('inc/header.php') ?>


     <?php



        if(!isset($_GET['id']) || $settings_r['shutdown'] == true){
         redirect ('rooms.php'); 
        }
        else if(!(isset($_SESSION['login']) && $_SESSION['login'] == true)){
            redirect('rooms.php');
        }


        // loc lay du lieu phong va nguoi dung

        $data = filteration($_GET);

        $room_res = select("SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?", [$data['id'],1,0], 'iii');

        if(mysqli_num_rows($room_res) ==0){
            redirect('rooms.php');
        }

        $room_data = mysqli_fetch_assoc($room_res);

        $_SESSION['room'] = [
            "id" => $room_data['id'],
            "name" => $room_data['name'],
            "price" => $room_data['price'],
            "payment" => null,
            "available" => false,
        ];

        $user_res = select("SELECT * FROM `user_cred` WHERE `id`=? LIMIT 1", [$_SESSION['uId']], "i");
        $user_data = mysqli_fetch_assoc($user_res);

            
    ?>

    

    <div class="container">
        <div class="row">

            <div class="my-5 px-4">
                <h2 class="fw-bold">Confirm Booking</h2>
                <div style="font-size: 14px;">
                    <a href="index.php" class="text-secondary text-decoration-none">Home</a>
                    <span class="text-secondary"> > </span>
                    <a href="Rooms.php" class="text-secondary text-decoration-none">Rooms</a>
                    <span class="text-secondary"> > </span>
                    <a href="#" class="text-secondary text-decoration-none">Confirm Booking</a>
                </div>
            </div>

            <div class="col-lg-7 col-md-12 px-4">
                <?php 



                    $room_thumb = ROOMS_IMG_PATH."1.jpg";
                    $thumb_q = mysqli_query($con, "SELECT * FROM `room_images`
                    WHERE `room_id`='$room_data[id]' AND `thumb`='1'");

                    if (mysqli_num_rows($thumb_q) > 0){
                        $thumb_res = mysqli_fetch_assoc($thumb_q);
                        $room_thumb = ROOMS_IMG_PATH.$thumb_res['image'];
                    }
                    echo<<<data
                        <div class="card p-3 shadow-sm rounded">
                            <img src="$room_thumb" class="img-fluid rounded">
                            <h5>$room_data[name]</h5>
                            <h6>$room_data[price] $/ 1days</h6>
                        </div>
                    data;

                ?>
                    
                    </div>
                    <div class="col-lg-5 col-md12 px-4">
                <div class="card ,mb-4 border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <form action="#" id="booking_form">
                            <h6 class="mb-3">BOOKING DETAILS </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="form-label">Name</label>
                                    <input name="name" type="text" value="<?php echo $user_data['name'] ?>" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="form-label">Phone Number</label>
                                    <input name="phonenum" type="text" value="<?php echo $user_data['name'] ?>" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="form-label">Address</label>
                                    <textarea name="address" class="form-control shadow-none" required><?php echo $user_data['address'] ?></textarea>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="form-label">Check-in</label>
                                    <input name="checkin" onchange="check_availability()" type="date"  class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="form-label">Check-out</label>
                                    <input name="checkout" onchange="check_availability()" type="date"  class="form-control shadow-none" required>
                                </div>
                                <div class="col-12">
                                    <div class="spinner-border text-info mb-3 d-none" id="info_loader" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <h6 class="mb-3 text-danger" id="pay_info">Select check-in and check-out dates</h6>
                                    <button type="button" name="pay_now" class="btn w-100 text-white custom-bg shadow-none mb-41" data-bs-toggle="modal" data-bs-target="#payModal" data-bs-dismiss="modal" disabled>Pay now</button>
                                </div>
                                
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
            

            <div class="col-12 mt-4 px-4">
                <div class="mb-5">
                    <h5>Describe</h5>
                    <p>
                        <?php echo $room_data['description'] ?>
                    </p>
                </div>
            </div>
            <h5 class="mb-3">Reviews and ratings</h5>
            <div>
                <div class="d-flex align-items-center mb-2">
                    <img src="./images//facilities/star.png" width="30px">
                    <h6 class="m-2 ms-2">Duong Duc Toan</h6>
                </div>
                <p>
                    Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                     Fugit consequatur omnis repellat quam. Delectus quos magnam, consequatur at error,
                    ducimus soluta dignissimos itaque a molestias est nemo, iste nisi porro.
                </p>
                <div class="rating">
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    

    <?php require('inc/footer.php') ?>
   <script>
        let booking_form = document.getElementById('booking_form');
        let info_loader = document.getElementById('info_loader');
        let pay_info = document.getElementById('pay_info');


        function check_availability(){
            let checkin_val = booking_form.elements['checkin'].value;
            let checkout_val = booking_form.elements['checkout'].value;

            booking_form.elements['pay_now'].setAttribute('disabled', true);

            if(checkin_val !='' && checkout_val!=''){

                pay_info.classList.add('d-none');
                pay_info.classList.replace('text-dark', 'text-danger');
                info_loader.classList.remove('d-none');

                
                let data = new FormData();

                data.append('check_availability','');
                data.append('check_in', checkin_val);
                data.append('check_out', checkout_val);

                let xhr = new XMLHttpRequest();


                xhr.open("POST", "ajax/confirm_booking.php", true);

                xhr.onload = function() {
                    let data = JSON.parse(this.responseText);


                    if(data.status == 'check_in_out_equal'){
                        pay_info.innerText = "You cannot check in and check out on the same day!";
                    }
                    else if(data.status == 'check_out_earlier'){
                        pay_info.innerText = "Check-out date is earlier than check-in date!";
                    }
                    else if(data.status == 'check_in_earlier'){
                        pay_info.innerText = "Check-in date is earlier than today!";
                    }
                    else if(data.status == 'unavailable'){
                        pay_info.innerText = "Rooms are not available on this date!";
                    }
                    else{
                        pay_info.innerHTML = "Days: "+data.day+"<br>Total payment: "+data.payment+"$";
                        pay_info.classList.replace('text-danger','text-dark');
                        booking_form.elements['pay_now'].removeAttribute('disabled', true);
                    }
                    pay_info.classList.remove('d-none');
                    info_loader.classList.add('d-none');

                };


                // Gửi yêu cầu với dữ liệu
                xhr.send(data);
            }
    }






    
    //thanh toan day
    let pay_form = document.getElementById('pay-form');
    pay_form.addEventListener('submit', (e) => {
    e.preventDefault();

    let data = new FormData();
    data.append('stk', pay_form.elements['stk'].value); // Sửa thành pay_form
    data.append('pay_form', '');

    var myModal = document.getElementById('payModal');
    
    if (myModal) {
        var modal = bootstrap.Modal.getInstance(myModal);
        modal.hide();
    }

    let xhr = new XMLHttpRequest();

    xhr.open("POST", "ajax/login_register.php", true);

    xhr.onload = function () {
        // Thay đổi dòng này để lấy giá trị từ xhr.responseText
        let result = this.responseText;
        console.log(result);
        if (result == 1) {
             
            alert('success',"Payment Successful!");
            redirect('rooms.php');
        } else {
             alert('error',"Fault! The account number is incorrect!");
        }
    };

    // Gửi yêu cầu với dữ liệu
    xhr.send(data);
});
   </script>

</body>
</html>