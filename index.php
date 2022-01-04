<?php 

    //Set page headers
    header("Content-type: text/html; charset=UTF-8");


    //Mysql config
    $mysqlIP = "localhost";
    $mysqlUser = "lordbecvold";
    $mysqlPassword = "lordbecvold";
    $mysqlDatabase = "ImageUploader";


    //Encryption config
    $encryptionEnable = "yes"; //If this = yes encryption enabled
    $encryptionKey = "q2Pwkx3o63Tfks06BxZ3P5h62QLUOLBe"; //This si encryption key for encrypt and decrypt image in site


    //Connection to mysql
    $connection = mysqli_connect($mysqlIP, $mysqlUser, $mysqlPassword, $mysqlDatabase);


    //Upload phase
    if (isset($_POST["submit"])) {
        
        //Get image name
        $name = $_FILES["imageFile"]["name"];

        //Extract file extension
        $ext = end((explode(".", $name)));

        //Check if file is image
        if ($ext == "gif" or $ext == "jpg" or $ext == "jpeg" or $ext == "jfif" or $ext == "pjpeg" or $ext == "pjp" or $ext == "png" or $ext == "webp" or $ext == "bmp" or $ext == "ico") {

            //Generate imgSpec value
            $imgSpec = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 20);


            //Check if encryption is enabled
            if ($encryptionEnable == "yes") {

                //Get image from file and encode to aes
                $image = openssl_encrypt(file_get_contents($_FILES["imageFile"]["tmp_name"]), "aes-128-cbc", $encryptionKey);
            } else {

                //Get image from file and encode to base64
                $image = base64_encode(file_get_contents($_FILES["imageFile"]["tmp_name"]));
            }

            //Insert query to mysql table images
            $query = mysqli_query($connection, "INSERT INTO `images`(`imgSpec`, `image`) VALUES ('$imgSpec', '$image')");
            
            //Check if query complete
            if (!$query) {
                http_response_code(503);
                die('The service is currently unavailable due to the inability to send requests');
            }
                
            //Redirect to image shower
            header("location: index.php?process=show&imgSpec=".$imgSpec);

        } else {

            //Print error if file != image
            die("Error file have wrong format");

        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/main.css">
    <title>Image uploader</title>
</head>
<body>
    <script class="jsbin" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <header>
        <ul>
            <li><a href="index.php">Uploader</a></li>
        </ul>
    </header>
    <?php

        //Check if process seted and if = show
        if (!empty($_GET["process"]) && $_GET["process"] == "show") {
            
            //get imgSpec image id and escape
            $imgSpec = htmlspecialchars(mysqli_real_escape_string($connection, $_GET["imgSpec"]), ENT_QUOTES);


            //Check if if specified
            if (empty($imgSpec)) {
                die("Error image is not specified");
            } else {

                //Get image by specID
                $image = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM images WHERE imgSpec='".$imgSpec."'"));

                //Check if encryption is enabled and decrypt image
                if ($encryptionEnable == "yes") {
                    $image = base64_encode(openssl_decrypt($image["image"], "aes-128-cbc", $encryptionKey));
                } else {
                    $image = $image["image"];
                }

                //Print image shower
                echo '
                    <main>
                        <img src="data:image/jpeg;base64,'.$image.'">
                    </main>
                ';
            }
        } else {

            //Print image upload form if process is empty
            echo '
                <main>
                    <form action="index.php" method="post" enctype="multipart/form-data">
                        <div class="file-upload">
                        <p class="formTtitle">Image upload</p>
                            <div class="image-upload-wrap">
                                <input class="file-upload-input" type="file" name="imageFile" onchange="readURL(this);" accept="image/*" />
                                <div class="drag-text">
                                    <h3>Drag and drop a file or select add Image</h3>
                                </div>
                            </div>
                            <div class="file-upload-content">
                                <img class="file-upload-image" src="#" alt="your image" />
                                <div class="image-title-wrap">
                                    <button type="button" onclick="removeUpload()" class="remove-image">Remove <span class="image-title">Uploaded Image</span></button>
                                </div>
                            </div><br>
                            <input class="file-upload-btn" type="submit" value="Upload Image" name="submit">
                        </div>
                    </form>
                </main>
            ';
        }
    ?>
    <footer>
        <p>Made with ❤️ By <a href="https://www.becvar.xyz">Lordbecvold</a></p>
    </footer>
    <script src="assets/main.js"></script>
</body>
</html>