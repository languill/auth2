<?php
namespace App;


class Helpers {
    
    
    public static function redirect_to($path) {
        header("Location: {$path}");
        exit;
    }

    public static function text_validate($string) {
        $data = trim($string);
        $data = stripslashes($string);
        $data = htmlspecialchars($string);
        return $string;
    }

    public static function canAddUser($auth, $role) {
        return $auth->hasAnyRole(        
            $role,        
        );
    }

    public static function upload_file($file_name, $file_tmp) {
        $avatar = self::setUniqueFileName($file_name);	
        $uploadfile = "images/".$avatar;
        move_uploaded_file($file_tmp, $uploadfile);	
        return $avatar;
    }

    public static function setUniqueFileName($path) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $avatar = uniqid().'.'.$ext;
        return $avatar;
    }

   public static function delete_image($image, $path) {
	if($image !== NULL) {			
			if (file_exists($path)){
				unlink($path);
			}
	}
}

}