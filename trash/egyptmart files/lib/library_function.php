<?php	
	class LibraryFunction{
	
		public static function entry_structure_content($title,$lang = NULL)
		{
			$sql = "select * from cms where title='".$title."'";
			$qry = mysqli_query($con, $sql);
			$arr = mysqli_fetch_array( $qry);
			if($lang == "hi")
				return $arr['ln_hi'];
			else
				return $arr['ln_en'];
		}
	}
	
		
function Rand_String($digits)
{
	$alphanum = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	
	// generate the random string
	$rand = substr(str_shuffle($alphanum), 0, $digits);
	return $rand;
}
?>