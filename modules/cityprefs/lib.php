<?PHP
function get_cityprefs_module($lookup,$value,$player=false){
	if($player>0){
		$sql1="select location from ".DB::prefix("accounts")." where acctid=$player";
		$res1=DB::query($sql1);
		$row1=DB::fetch_assoc($res1);
		$lookup="cityname";
		$value=$row1['location'];
	}

	if($lookup=='cityid'){$where="cityid=$value";}
	else{$where="cityname='".addslashes($value)."'";}

	$sql="select module from ".DB::prefix("cityprefs")." where $where";
	$res=DB::query($sql);
	$row=DB::fetch_assoc($res);
	return $row['module'];
}

function get_cityprefs_cityid($lookup,$value,$player=false){
	if($player>0){
		$sql1="select location from ".DB::prefix("accounts")." where acctid=$player";
		$res1=DB::query($sql1);
		$row1=DB::fetch_assoc($res1);
		$lookup="cityname";
		$value=$row1['location'];
	}

	if($lookup=='module'){$where="module='".addslashes($value)."'";}
	else{$where="cityname='".addslashes($value)."'";}

	$sql="select cityid from ".DB::prefix("cityprefs")." where $where";
	$res=DB::query($sql);
	$row=DB::fetch_assoc($res);
	return $row['cityid'];
}

function get_cityprefs_cityname($lookup,$value,$player=false){
	if($player>0){
		$sql1="select location from ".DB::prefix("accounts")." where acctid=$player";
		$res1=DB::query($sql1);
		$row1=DB::fetch_assoc($res1);
		return $row1['location'];
	}

	if($lookup=='module'){$where="module='".addslashes($value)."'";}
	else{$where="cityid=$value";}

	$sql="select cityname from ".DB::prefix("cityprefs")." where $where";
	$res=DB::query($sql);
	$row=DB::fetch_assoc($res);
	return $row['cityname'];
}
?>