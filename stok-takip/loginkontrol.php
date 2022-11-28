<?php

require_once("dahili.php");

@$loginbtn = $_POST['loginbtn'];
@$kullanici_ad = $_POST['kullanici_ad'];
@$kullanici_sifre = $_POST['kullanici_sifre'];

if ($loginbtn) {

	$kullanici_sifre = md5(sha1(md5($kullanici_sifre)));

	$loginkontrol = $database->prepare("SELECT * FROM kullanici WHERE kullanici_ad = ? AND kullanici_sifre = ?");
	$loginkontrol->bindParam(1,$kullanici_ad,PDO::PARAM_STR);
	$loginkontrol->bindParam(2,$kullanici_sifre,PDO::PARAM_STR);
	$loginkontrol->execute();
	$loginkontrolcek = $loginkontrol->fetch();

	if ($loginkontrol->rowCount() != 0) {

		setcookie("kullanici_ad",$kullanici_ad,time() + 60*60*24);
		setcookie("kullanici_sifre",$kullanici_sifre,time() + 60*60*24);

		echo "Giriş Başarılı"."<br>"."Yönlendiriliyorsunuz...";
		header("refresh:2,url=index.php");
		
	}else {

		echo "Giriş Başarısız"."<br>"."Yönlendiriliyorsunuz...";
		header("refresh:2,url=index.php");

	}
	
}else {

	echo "HATA"."<br>"."Yönlendiriliyorsunuz...";
	header("refresh:2,url=index.php");

	
}






?>