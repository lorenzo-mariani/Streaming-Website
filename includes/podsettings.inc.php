
<script src="../load_audio.js">
</script>

<?php

require 'dbh.inc.php';

session_start();

$genre = strtolower($_POST["genre-value-mod"]);
$new_title = str_replace(' ', '_', strtolower($_POST["podcast-title-mod"]));
$playlist = str_replace(' ', '_', strtolower($_POST["podcast-playlist-mod"]));
$old_title = $_POST["podcast-old-title"];

$old_dir = "./content/users/".$_SESSION['userUid']."/"."podcasts/".$old_title."/";
$target_dir = "./content/users/".$_SESSION['userUid']."/"."podcasts/".$new_title."/";
$target_file_img = $target_dir . str_replace(' ','_',basename($_FILES["img-file-mod"]["name"]));
$checkImg = 1;
if(empty($new_title) && empty($playlist) && $genre == "select genre" && $_FILES["img-file-mod"]["error"] == 4){
    header("Location: ../home.php?warning=emptyfields");
} else {
    $query_check = "SELECT podcastTitle, podcastImg, podcastFile FROM podcasts WHERE podcastTitle = ? AND userUID = ?";
    $stmt_check = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt_check, $query_check)){
        header("Location: ../upload.php?error=sqlerror");
        exit();
    } else {
        mysqli_stmt_bind_param($stmt_check, "ss", $old_title, $_SESSION['userUid']);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        $stmt_check->bind_result($ttl, $old_img, $old_audiopath);
        if($stmt_check->num_rows > 0){
            while($stmt_check->fetch()){
                if ($_FILES["img-file-mod"]["size"] > 5000000000) {
                    echo "Sorry, your image file is too large.";
                    $checkImg = 0;
                }
            
                if ($checkImg == 0) {
                    echo "Sorry, your img file was not uploaded.";
                    exit();
                } else {

                    $update_title = "UPDATE podcasts SET podcastTitle = ? WHERE podcastTitle = ? AND userUID = ?";
                    $update_playlist = "UPDATE podcasts SET playlist = ? WHERE podcastTitle = ? AND userUID = ?";
                    $update_img = "UPDATE podcasts SET podcastImg = ? WHERE podcastTitle = ? AND userUID = ?";
                    $update_genre = "UPDATE podcasts SET genre = ? WHERE podcastTitle = ? AND userUID = ?";
                    $update_audio = "UPDATE podcasts SET podcastFile = ? WHERE podcastTitle = ? AND userUID = ?";
        
                    if(!empty($playlist) && $playlist != str_repeat("_", strlen($playlist))) {
                        $stmt_playlist = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt_playlist, $update_playlist)) {
                            header("Location: ./podcast_settings?error=sqlerror");
                                exit();
                        } else {
                            mysqli_stmt_bind_param($stmt_playlist, "sss", $playlist, $old_title, $_SESSION['userUid']);
                            mysqli_stmt_execute($stmt_playlist);
                            mysqli_stmt_close($stmt_playlist);
                        }
                    }
                    if(!($genre == "select genre")){
                        $stmt_genre = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt_genre, $update_genre)) {
                            header("Location: ./podcast_settings?error=sqlerror");
                                exit();
                        } else {
                            mysqli_stmt_bind_param($stmt_genre, "sss", $genre, $old_title, $_SESSION['userUid']);
                            mysqli_stmt_execute($stmt_genre);
                            mysqli_stmt_close($stmt_genre);
                        }
                    }
        
                    if (!empty($new_title) && $new_title != str_repeat("_", strlen($new_title))) {
                        if($_FILES["img-file-mod"]["error"] != 4) {
                            if(file_exists(str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $old_img))){
                                unlink(str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $old_img));
                                rename(str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $old_dir), str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $target_dir));
                                if (move_uploaded_file($_FILES["img-file-mod"]["tmp_name"], str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $target_file_img))) {
                                    echo "The file ". htmlspecialchars( basename( $_FILES["img-file-mod"]["name"])). " has been uploaded.";
                                } else {
                                    echo "Sorry, there was an error uploading your image file.";
                                    exit();
                                }
                            }
                            $stmt_img = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($stmt_img, $update_img)) {
                                header("Location: ./podcast_settings?error=sqlerror");
                                    exit();
                            } else {
                                mysqli_stmt_bind_param($stmt_img, "sss", $target_file_img, $old_title, $_SESSION['userUid']);
                                mysqli_stmt_execute($stmt_img);
                                mysqli_stmt_close($stmt_img);
                                echo "<script type=\"text/javascript\">
                                    setCookieSubstring(\"memaudio\", \"img=\", \"".$target_file_img."\", 2)
                                    </script>";
                            }
                        } else {
                            rename(str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $old_dir), str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $target_dir));
                            $stmt_img = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($stmt_img, $update_img)) {
                                header("Location: ./podcast_settings?error=sqlerror");
                                    exit();
                            } else {
                                $newpath_img = $target_dir . substr($old_img, strlen($old_dir), strlen($old_img));
                                echo $newpath_img;
                                mysqli_stmt_bind_param($stmt_img, "sss", $newpath_img, $old_title, $_SESSION['userUid']);
                                mysqli_stmt_execute($stmt_img);
                                mysqli_stmt_close($stmt_img);
                                echo "<script type=\"text/javascript\">
                                    setCookieSubstring(\"memaudio\", \"img=\", \"".$newpath_img."\", 2)
                                    </script>";
                            }
                        }
                        $stmt_audio = mysqli_stmt_init($conn);
                        $new_audiopath = $target_dir . substr($old_audiopath, strlen($old_dir), strlen($old_audiopath));
                        if (!mysqli_stmt_prepare($stmt_audio, $update_audio)) {
                            header("Location: ./podcast_settings?error=sqlerror");
                                exit();
                        } else {
                            mysqli_stmt_bind_param($stmt_audio, "sss", $new_audiopath, $old_title, $_SESSION['userUid']);
                            mysqli_stmt_execute($stmt_audio);
                            mysqli_stmt_close($stmt_audio);
                        }
                        $stmt_title = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt_title, $update_title)) {
                            header("Location: ./podcast_settings?error=sqlerror");
                                exit();
                        } else {
                            mysqli_stmt_bind_param($stmt_title, "sss", $new_title, $old_title, $_SESSION['userUid']);
                            mysqli_stmt_execute($stmt_title);
                            mysqli_stmt_close($stmt_title);
                            echo "<script type=\"text/javascript\">
                                    setCookieSubstring(\"memaudio\", \"name=\", \"".str_replace("_", " ", $new_title)."\" , 2)
                                    </script>";
                        }
                    } else if($new_title != str_repeat("_", strlen($new_title)) || empty($new_title)) {
                        if($_FILES["img-file-mod"]["error"] != 4) {
                            if(file_exists(str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $old_img))){
                                echo "file exists\n";
                                unlink(str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $old_img));
                                $newimage_path = $old_dir . str_replace(' ','_',basename($_FILES["img-file-mod"]["name"]));
                                if (move_uploaded_file($_FILES["img-file-mod"]["tmp_name"], str_replace("./", $_SERVER['DOCUMENT_ROOT'].'/', $newimage_path))) {
                                    echo "The file ". htmlspecialchars( basename( $_FILES["img-file-mod"]["name"])). " has been uploaded.\n";
                                    $stmt_img = mysqli_stmt_init($conn);
                                    if (!mysqli_stmt_prepare($stmt_img, $update_img)) {
                                        header("Location: ./podcast_settings?error=sqlerror");
                                            exit();
                                    } else {
                                        mysqli_stmt_bind_param($stmt_img, "sss", $newimage_path, $old_title, $_SESSION['userUid']);
                                        mysqli_stmt_execute($stmt_img);
                                        mysqli_stmt_close($stmt_img);
                                        echo "<script type=\"text/javascript\">
                                            setCookieSubstring(\"memaudio\", \"img=\", \"".$newimage_path."\" , 2);
                                        </script>";
                                    }
                                } else {
                                    echo "Sorry, there was an error uploading your image file.";
                                    exit();
                                }
                            }
                        }
                    }
                    echo "<div id=\"message-container\">
                        <h4 style=\"font-family: 'caviar_dreamsbold';
                        text-align: center;
                        font-size: 30px;
                        margin-top: 3%;\">PODCAST WAS MODIFIED SUCCESFULLY.</h4>
                        <form action=\"../home.php\" method=\"post\" style=\"text-align: center;\">
                            <button id=\"back-home-button\" type=\"submit\" name=\"back-home-submit\" style=\"height: 80px;
                            width: 200px;\">
                                GO BACK HOME
                            </button>
                        </form>
                    </div>";
                }
            }
        } else {
            header("Location: ../podcast_settings?error=notfound".$old_title);
        }
    }
        mysqli_stmt_close($stmt_check);
        mysqli_close($conn);
}
?>