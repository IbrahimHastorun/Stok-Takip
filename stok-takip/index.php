<?php
  require_once("dahili.php");
  $stoktakip = new stoktakip();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>STOK TAKİP SİSTEMİ</title>

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="dosya/tasarim.css" />
</head>
<body>
  <div class="container table-bordered" id="cont">
    <?php
      if (@$_COOKIE['kullanıcı_ad'] == "" && @$_COOKIE['kullanici_sifre'] == "") { ?>

        <!--Giriş Formu-->
        <div class="row" style="text-align:center">
          <form action="loginkontrol.php" method="post">
              <div class="col-md-12">
                Kullanıcı Adı<br>
                <input type="text" name="kullanici_ad">
              </div>
              <div class="col-md-12">
                Şifre<br>
                <input type="password" name="kullanici_sifre">
              </div>
              <div class="col-md-12"><br>
                <input value="GİRİŞ YAP" class="btn btn-outline-info" type="submit" name="loginbtn">
              </div>
          </form>
        </div>
        <!--Giriş Formu--> <?php

      } else { ?>

        <!-- ÜST BÖLÜM -->
        <div class="row border-bottom" style="min-height:60px">
          <div class="col-md-3" id="ustbolum">
            <h4 class="text-danger">Hoşgeldin : <?php echo $_COOKIE['kullanici_ad'] ?></h4>
          </div>
          <div class="col-md-9" id="ustbolum">
            <ul class="nav justify-content-end" >
              <?php $stoktakip->linkizinkontrol($database);?>
            </ul>
          </div>
        </div>
        <!-- ÜST BÖLÜM -->
        <!-- KATEGORİ BÖLÜM -->
        <div class="row border-bottom">
          <div class="col-md-9"><br />
            <?php $stoktakip->kategorigetir($database); ?>
          </div>
          <?php
            $kullaniciyetkibak = $stoktakip->kullanicibilgial($database);
            if ($kullaniciyetkibak['kullanici_yetki'] == 1) { ?>

              <div class="col-md-3">
                <form method="post" action="index.php?islem=tercih" class="form-inline">
                  <label class="my-1 mr-2" for="inlineFormCustomSelectPref">Listeleme</label>
                  <select name="gelen_tercih" class="custom-select my-1 mr-sm-2" id="inlineFormCustomSelectPref">
                    <?php $stoktakip->tercihkontrol($database); ?>
                  </select>
                  <input name="tercihbtn" type="submit" class="btn btn-success my-1" value="Uygula"></input>
                </form>
              </div> <?php
           
            }
          ?>     
        </div>
        <!-- KATEGORİ BÖLÜM -->
        <!-- ORTA BÖLÜM -->
        <div class="row">
          <?php
            @$islem = $_GET['islem'];

            switch ($islem) {
              case 'kategoriyegore':

                if ($_GET['kategori_id'] != "") {

                  $kategori_id = $_GET['kategori_id'];
                  $stoktakip->kategoriyegoreurungetir($database,$kategori_id);
                  
                }else {

                  echo "Hata Var";
                  header("refresh:2,url=index.php");

                }

              break;

              case 'stoktalepform':

                $stoktakip->stoktalepform($database);

              break;

              case 'stoktalep':

                $stoktakip->stoktalep($database);

              break;

              case 'urunguncelleform':

                $stoktakip->urunguncelleform($database);
                
              break;

              case 'urunguncelle':

                $stoktakip->urunguncelle($database);
                
              break;

              case 'sifreguncelleform':

                $stoktakip->sifreguncelleform($database);

              break;

              case 'sifreguncelle':

                $stoktakip->sifreguncelle($database);

              break;

              case 'cikis':

                $stoktakip->cikis($database);

              break;

              case 'tercih':

                $stoktakip->tercihguncelle($database);

              break;

              case 'islemler':

                $stoktakip->islemler($database);

              break;

              case 'ikategoriekleform':

                $stoktakip->ikategoriekleform($database);

              break;

              case 'ikategoriekle':

                $stoktakip->ikategoriekle($database);

              break;

              case 'iurunlistele':

                $stoktakip->iurunlistele($database);

              break;

              case 'iurunguncelleform':

                $stoktakip->iurunguncelleform($database);

              break;

              case 'iurunguncelle':

                $stoktakip->iurunguncelle($database);

              break;

              case 'iurunsil':

                $stoktakip->iurunsil($database);

              break;

              case 'iurunekleform':

                $stoktakip->iurunekleform($database);

              break;

              case 'iurunekle':

                $stoktakip->iurunekle($database);

              break;

              case 'italepsayfa':

                $stoktakip->italepsayfa($database);

              break;

              case 'italeponay':

                $stoktakip->italeponay($database);

              break;

              case 'italepsil':

                $stoktakip->italepsil($database);

              break;

              case 'iurunrapor':

                $stoktakip->iurunrapor($database);

              break;

              case 'ikullanicilistele':

                $stoktakip->ikullanicilistele($database);

              break;

              case 'ikullaniciekleform':

                $stoktakip->ikullaniciekleform($database);

              break;

              case 'ikullaniciekle':
                
                $stoktakip->ikullaniciekle($database);

              break;

              case 'ikullanicisil':

                $stoktakip->ikullanicisil($database);

              break;

              case 'iayarlar':

                $stoktakip->iayarlar($database);

              break;
      
              default:

                $defaultsayfacek = $stoktakip->kullanicibilgial($database);

                if ($defaultsayfacek['kullanici_yetki'] == 1) {

                  $stoktakip->varsayilanurungetir($database);
                  
                }elseif ($defaultsayfacek['kullanici_yetki'] == 2) {

                  $stoktakip->italepsayfa($database);
                  
                }elseif ($defaultsayfacek['kullanici_yetki'] == 3) {

                  $stoktakip->iurunlistele($database);
                  
                }else {

                  echo "Hata Var";
                  header("refresh:2,url=index.php");
                  
                }  

              break;
            }

          ?>   		
        </div>
        <!-- ORTA BÖLÜM --> <?php
      }
    ?>   
  </div>
</body>
</html>