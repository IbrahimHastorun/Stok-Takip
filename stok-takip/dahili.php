<?php 

try {

	$database = new PDO("mysql:host=localhost;dbname=stoktakipsistemi;charset=utf8", "root","");
	$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
} catch (PDOException $e) {
	die($e->getMessege());
}

class stoktakip {

    public function kullanicibilgial($database) {
        $kullanici_ad = $_COOKIE['kullanici_ad'];
        $kullanicibilgi = $database->prepare("SELECT * FROM kullanici WHERE kullanici_sifre = ? AND kullanici_ad = ?");
        $kullanicibilgi->bindParam(1,$_COOKIE['kullanici_sifre'],PDO::PARAM_STR);
        $kullanicibilgi->bindParam(2,$kullanici_ad,PDO::PARAM_STR);
        $kullanicibilgi->execute();
        $kullanicibilgicek = $kullanicibilgi->fetch();

        return $kullanicibilgicek;
    }

    public function kategorigetir($database) {
        $kullaniciizinbak = $this->kullanicibilgial($database);

        if ($kullaniciizinbak['kullanici_yetki'] == 1) {

            $kategorial = $database->prepare("SELECT * FROM kategori");
            $kategorial->execute();
            while($kategorialcek = $kategorial->fetch(PDO::FETCH_ASSOC)){ ?>

                <a class="btn btn-dark" id="link" href="index.php?islem=kategoriyegore&kategori_id=<?php echo $kategorialcek['id'] ?>" style="margin-bottom:10px;"><?php echo $kategorialcek['kategori_ad'] ?></a> <?php

            }
            
        }elseif ($kullaniciizinbak['kullanici_yetki'] == 2) { 
            
            $taleplerbak = $database->prepare("SELECT * FROM talepler WHERE talep_durum = 0");
            $taleplerbak->execute(); ?>

            <a style="margin-bottom:10px;" class="btn btn-dark" id="link" href="index.php?islem=italepsayfa">Stok Talepler <span class="badge badge-light"><?php echo $taleplerbak->rowCount(); ?></span></a><br> <?php
                
        }   
    }

    public function varsayilanurungetir($database) {
        $uruntoplam = $database->prepare("SELECT COUNT(*) AS toplam_urun FROM urunler");
        $uruntoplam->execute();

        if ($uruntoplam->rowCount() == 0) { ?>

           <div class="alert alert-danger">
                Stokta Ürün yok 
           </div> <?php
            
        }else {

            $uruntoplambak = $uruntoplam->fetch();
            $gosterilecekurunsay = 5;
            $toplam_urun = $uruntoplambak['toplam_urun'];
            $toplam_sayfa = ceil($toplam_urun / $gosterilecekurunsay);

            if (isset($_GET['sayfa']) && (int) $_GET['sayfa'] ) {

                $sayfa = $_GET['sayfa'];
                
            }else {

                $sayfa = 1;

            }

            if ($sayfa < 1) {

                $sayfa = 1;

            }

            if ($sayfa > $toplam_sayfa) {

                $sayfa = $toplam_sayfa;
                
            }

            $limit = ($sayfa - 1) * $gosterilecekurunsay;

            $urunal = $database->prepare("SELECT * FROM urunler LIMIT $limit,$gosterilecekurunsay");
            $urunal->execute();

            $tercihbakcek = $this->kullanicibilgial($database);

            if ($tercihbakcek['kullanici_tercih'] == 1) {

                while($urunalcek = $urunal->fetch(PDO::FETCH_ASSOC)){ ?>

                    <div class="col-md-1 table-bordered " id="kutuliste">        
                        <div class="row" style="text-align:center">
                            <div class="col-md-12" ><?php echo $urunalcek['urun_ad'] ?></div> 
                            <div class="col-md-12">
                                <b>Stok : </b> <?php echo $urunalcek['urun_stok'] ?>
                            </div> 
                            <div class="col-md-12">
                                <form action="index.php?islem=urunguncelleform" method="post">
                                    <input type="hidden" value="<?php echo $urunalcek['id'] ?>" name="hurun_id">
                                    <input name="guncelbtn" type="submit" class="btn btn-outline-success" value=">" style="margin-bottom:3px; "/>
                                </form>
                            </div>
                            <?php
                                $ayarbak = $database->prepare("SELECT * FROM ayarlar WHERE ayarlar_ad = ?");
                                $ayarbak->bindValue(1,"otomatik",PDO::PARAM_STR);
                                $ayarbak->execute();
                                $ayarbakcek = $ayarbak->fetch();

                                if ($ayarbakcek['ayarlar_durum'] == 1) { ?>
                                    <div class="col-md-12">
                                        <form action="index.php?islem=stoktalepform" method="post">
                                            <input type="hidden" value="<?php echo $urunalcek['id'] ?>" name="hurun_id">
                                            <input name="stoktalepbtn" type="submit" class="btn btn-outline-primary" value="+" style="margin-bottom:3px;"/>
                                        </form>
                                    </div> <?php            
                                }else {
                                    $ototalepbak = $database->prepare("SELECT * FROM talepler WHERE urun_id = ?");
                                    $ototalepbak->bindParam(1,$urunalcek['id'],PDO::PARAM_INT);
                                    $ototalepbak->execute();

                                    if ($ototalepbak->rowCount() == 0) {
                                        if ($urunalcek['urun_stok'] <= 50) {
                                            $ototalep = $database->prepare("INSERT INTO talepler (urun_id,talep_stok) VALUES (?,?)");
                                            $ototalep->bindParam(1,$urunalcek['id'],PDO::PARAM_INT);
                                            $ototalep->bindValue(2,1000,PDO::PARAM_INT);
                                            $ototalep->execute();
                                        } 
                                    }            
                                }
                            ?>    
                        </div>
                    </div> <?php

                } ?>

                <!-- SAYFALAMA BÖLÜM -->
                    <div class="container table-bordered table-info text-center">     
                        <div class="row" style="min-height:30px;">
                        <div class="col-md-12">
                            <?php
                            for ($i = 1; $i <= $toplam_sayfa ; $i++) { 
                                if ($sayfa == $i) {

                                    echo $i.' ';
                                
                                }else { ?>

                                    <a href="?sayfa=<?php echo $i ?>"><?php echo $i ?></a><?php
                                
                                }
                            }
                            ?>
                        </div>		
                        </div>
                    </div>
                <!-- SAYFALAMA BÖLÜM --> <?php
         
            } else { ?>

                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>Ürün Adı</th>
                            <th>Stok Durumu</th>
                            <th>Stok Güncelle</th>
                            <th>Stok Talep Et</th>
                        </tr>
                    </thead> 
                    <tbody> <?php
                        while($urunalcek = $urunal->fetch(PDO::FETCH_ASSOC)){ ?>
                            <tr>
                                <td><?php echo $urunalcek['urun_ad'] ?></td>
                                <td><b class="text-danger"><?php echo $urunalcek['urun_stok'] ?></b></td>
                                <td>
                                    <form action="index.php?islem=urunguncelleform" method="post">
                                        <input type="hidden" value="<?php echo $urunalcek['id'] ?>" name="hurun_id">
                                        <input name="guncelbtn" type="submit" class="btn btn-outline-success" value="Güncelle" style="margin-bottom:3px; "/>
                                    </form>
                                </td>
                                <?php
                                    $ayarbak = $database->prepare("SELECT * FROM ayarlar WHERE ayarlar_ad = ?");
                                    $ayarbak->bindValue(1,"otomatik",PDO::PARAM_STR);
                                    $ayarbak->execute();
                                    $ayarbakcek = $ayarbak->fetch();

                                    if ($ayarbakcek['ayarlar_durum'] == 1) { ?>
                                        <td>
                                            <form action="index.php?islem=stoktalepform" method="post">
                                                <input type="hidden" value="<?php echo $urunalcek['id'] ?>" name="hurun_id">
                                                <input name="stoktalepbtn" type="submit" class="btn btn-outline-info" value="Talep Et" style="margin-bottom:3px; "/>
                                            </form>
                                        </td> <?php            
                                    }else {
                                        $ototalepbak = $database->prepare("SELECT * FROM talepler WHERE urun_id = ?");
                                        $ototalepbak->bindParam(1,$urunalcek['id'],PDO::PARAM_INT);
                                        $ototalepbak->execute();

                                        if ($ototalepbak->rowCount() == 0) {
                                            if ($urunalcek['urun_stok'] <= 50) {
                                                $ototalep = $database->prepare("INSERT INTO talepler (urun_id,talep_stok) VALUES (?,?)");
                                                $ototalep->bindParam(1,$urunalcek['id'],PDO::PARAM_INT);
                                                $ototalep->bindValue(2,1000,PDO::PARAM_INT);
                                                $ototalep->execute();
                                            } 
                                        } ?>
                                        <td>
                                            <b class="text-info">Stok Talep Otomatik Durumda</b> 
                                        </td> <?php
                                    }
                                ?>   
                            </tr> <?php
                        } ?>
                    </tbody>
                </table>

                <!-- SAYFALAMA BÖLÜM -->
                <div class="container table-bordered table-info text-center">     
                    <div class="row" style="min-height:30px;">
                        <div class="col-md-12">
                            <?php
                                for ($i = 1; $i <= $toplam_sayfa ; $i++) { 
                                    if ($sayfa == $i) {

                                        echo $i.' ';
                                    
                                    }else { ?>

                                        <a href="?sayfa=<?php echo $i ?>"><?php echo $i ?></a><?php
                                    
                                    }
                                }
                            ?>
                        </div>		
                    </div>
                </div>
                <!-- SAYFALAMA BÖLÜM --> <?php
                
            }
         
        }
    }

    public function kategoriyegoreurungetir($database,$kategori_id) {
        $katgoreurunal = $database->prepare("SELECT * FROM urunler WHERE kategori_id = ? ");
        $katgoreurunal->bindParam(1,$kategori_id,PDO::PARAM_INT);
        $katgoreurunal->execute();

        if ($katgoreurunal->rowCount() == 0) { ?>

            <div class="alert alert-danger">
                Stokta Seçilen Kategoriye Göre Ürün Bulunamadı
            </div> <?php
     
        }else {

            $tercihbakcek = $this->kullanicibilgial($database);

            if ($tercihbakcek['kullanici_tercih'] == 1) {

                while($katgoreurunalcek = $katgoreurunal->fetch(PDO::FETCH_ASSOC)){ ?>

                    <div class="col-md-1 table-bordered " id="kutuliste">        
                        <div class="row" style="text-align:center">
                            <div class="col-md-12" ><?php echo $katgoreurunalcek['urun_ad'] ?></div> 
                            <div class="col-md-12">
                                <b>Stok : </b> <?php echo $katgoreurunalcek['urun_stok'] ?>
                            </div> 
                            <div class="col-md-12">
                                <form action="index.php?islem=urunguncelleform" method="post">
                                    <input type="hidden" value="<?php echo $katgoreurunalcek['id'] ?>" name="hurun_id">
                                    <input name="guncelbtn" type="submit" class="btn btn-outline-success" value=">" style="margin-bottom:3px; "/>
                                </form>
                            </div>
                            <?php
                                $ayarbak = $database->prepare("SELECT * FROM ayarlar WHERE ayarlar_ad = ?");
                                $ayarbak->bindValue(1,"otomatik",PDO::PARAM_STR);
                                $ayarbak->execute();
                                $ayarbakcek = $ayarbak->fetch();

                                if ($ayarbakcek['ayarlar_durum'] == 1) { ?>
                                    <div class="col-md-12">
                                        <form action="index.php?islem=stoktalepform" method="post">
                                            <input type="hidden" value="<?php echo $katgoreurunalcek['id'] ?>" name="hurun_id">
                                            <input name="stoktalepbtn" type="submit" class="btn btn-outline-primary" value="+" style="margin-bottom:3px; "/>
                                        </form>
                                    </div> <?php            
                                }else {
                                    $ototalepbak = $database->prepare("SELECT * FROM talepler WHERE urun_id = ?");
                                    $ototalepbak->bindParam(1,$katgoreurunalcek['id'],PDO::PARAM_INT);
                                    $ototalepbak->execute();

                                    if ($ototalepbak->rowCount() == 0) {
                                        if ($katgoreurunalcek['urun_stok'] <= 50) {
                                            $ototalep = $database->prepare("INSERT INTO talepler (urun_id,talep_stok) VALUES (?,?)");
                                            $ototalep->bindParam(1,$katgoreurunalcek['id'],PDO::PARAM_INT);
                                            $ototalep->bindValue(2,1000,PDO::PARAM_INT);
                                            $ototalep->execute();
                                        } 
                                    }
                                }
                            ?>          
                        </div>
                    </div> <?php
        
                }
      
            }else {  ?>

                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>Ürün Adı</th>
                            <th>Stok Durumu</th>    
                            <th>Stok Güncelle</th>
                            <th>Stok Talep Et</th>
                        </tr>
                    </thead> 
                    <tbody> <?php
                        while($katgoreurunalcek = $katgoreurunal->fetch(PDO::FETCH_ASSOC)){ ?>
                            <tr>
                                <td><?php echo $katgoreurunalcek['urun_ad'] ?></td>
                                <td><b class="text-danger"><?php echo $katgoreurunalcek['urun_stok'] ?></b></td>
                                <td>
                                    <form action="index.php?islem=urunguncelleform" method="post">
                                        <input type="hidden" value="<?php echo $katgoreurunalcek['id'] ?>" name="hurun_id">
                                        <input name="guncelbtn" type="submit" class="btn btn-outline-success" value="Güncelle" style="margin-bottom:3px; "/>
                                    </form>
                                </td>
                                <?php
                                    $ayarbak = $database->prepare("SELECT * FROM ayarlar WHERE ayarlar_ad = ?");
                                    $ayarbak->bindValue(1,"otomatik",PDO::PARAM_STR);
                                    $ayarbak->execute();
                                    $ayarbakcek = $ayarbak->fetch();

                                    if ($ayarbakcek['ayarlar_durum'] == 1) { ?>
                                        <td>
                                            <form action="index.php?islem=stoktalepform" method="post">
                                                <input type="hidden" value="<?php echo $katgoreurunalcek['id'] ?>" name="hurun_id">
                                                <input name="stoktalepbtn" type="submit" class="btn btn-outline-info" value="Talep Et" style="margin-bottom:3px; "/>
                                            </form>
                                        </td> <?php            
                                    }else {
                                        $ototalepbak = $database->prepare("SELECT * FROM talepler WHERE urun_id = ?");
                                        $ototalepbak->bindParam(1,$katgoreurunalcek['id'],PDO::PARAM_INT);
                                        $ototalepbak->execute();

                                        if ($ototalepbak->rowCount() == 0) {
                                            if ($katgoreurunalcek['urun_stok'] <= 50) {
                                                $ototalep = $database->prepare("INSERT INTO talepler (urun_id,talep_stok) VALUES (?,?)");
                                                $ototalep->bindParam(1,$katgoreurunalcek['id'],PDO::PARAM_INT);
                                                $ototalep->bindValue(2,1000,PDO::PARAM_INT);
                                                $ototalep->execute();
                                            } 
                                        } ?>
                                        <td>
                                            <b class="text-info">Stok Talep Otomatik Durumda</b> 
                                        </td> <?php     
                                    }
                                ?>   
                            </tr> <?php
                        } ?>
                    </tbody>
                </table> <?php
      
            }

        }       
    }

    public function stoktalepform($database) {
        @$stoktalepbtn = $_POST['stoktalepbtn'];
        @$urun_id = $_POST['hurun_id'];

        if ($stoktalepbtn) {

            $stoktalepbak = $database->prepare("SELECT * FROM urunler WHERE id = ?");
            $stoktalepbak->bindParam(1,$urun_id,PDO::PARAM_INT);
            $stoktalepbak->execute();
            $stoktalepbakcek = $stoktalepbak->fetch(); ?>

            <div class="col-md-4"></div>
            <div class="col-md-4 table-bordered " id="kutuliste">        
                <div class="row" style="text-align:center">
                    <div class="col-md-12" >
                        <b>Talep Edilen Ürün : </b>
                        <?php echo $stoktalepbakcek['urun_ad'] ?><br>
                    </div> 
                    <div class="col-md-12">
                        <form action="index.php?islem=stoktalep" method="post">
                        <b>İstenen Adet : </b>
                        <input class="form-group" type="txt" name="urun_stok">
                    </div> 
                    <div class="col-md-12">
                        <input value="<?php echo $stoktalepbakcek['id'] ?>" type="hidden" name="hurun_id">
                        <input name="stoktalepbtn" type="submit" class="btn btn-outline-info" value="Talep Et" style="margin-bottom:3px; "/>
                        </form>
                    </div>        
                </div>
            </div>
            <div class="col-md-4"></div> <?php
            
        }else {

            echo "Hata Var";
            header("refresh:2,url=index.php");
            
        }

    }

    public function stoktalep($database) {
        @$stoktalepbtn = $_POST['stoktalepbtn'];
        @$urun_id = $_POST['hurun_id'];
        @$urun_stok = $_POST['urun_stok'];
        
        if ($stoktalepbtn) {

            $stoktalep = $database->prepare("INSERT INTO talepler (urun_id,talep_stok) VALUES (?,?)");
            $stoktalep->bindParam(1,$urun_id,PDO::PARAM_INT);
            $stoktalep->bindParam(2,$urun_stok,PDO::PARAM_INT);
            $stoktalepdurum = $stoktalep->execute();

            if ($stoktalepdurum) {

                echo "Stok Talep Başarılı"."<br>"."Yönlendiriliyor..";
                header("refresh:2,url=index.php");
                
            }else {
            
                echo "Hata Var";
                header("refresh:2,url=index.php");
    
            }
           
        } else {
            
            echo "Hata Var";
            header("refresh:2,url=index.php");

        }
        

    }

    public function urunguncelleform($database) {
        @$gbuton = $_POST['guncelbtn'];
        @$urun_id = $_POST['hurun_id'];

        if ($gbuton) {

            $urunguncellebak = $database->prepare("SELECT * FROM urunler WHERE id = ?");
            $urunguncellebak->bindParam(1,$urun_id,PDO::PARAM_INT);
            $urunguncellebak->execute();
            $urunguncellebakcek = $urunguncellebak->fetch(); ?>

            <div class="col-md-4"></div>
            <div class="col-md-4 table-bordered " id="kutuliste">        
                <div class="row" style="text-align:center">
                    <div class="col-md-12" ><?php echo $urunguncellebakcek['urun_ad'] ?><br></div> 
                    <div class="col-md-12">    
                        <b>Stok : </b>
                        <?php echo $urunguncellebakcek['urun_stok'] ?>
                    </div>
                    <div class="col-md-12">
                        <form action="index.php?islem=urunguncelle" method="post">
                        <b>Satılan Adet : </b>
                        <input class="form-group" type="txt" name="urun_stok">
                    </div> 
                    <div class="col-md-12">
                        <input value="<?php echo $urunguncellebakcek['urun_stok'] ?>" type="hidden" name="hurun_stok">
                        <input value="<?php echo $urunguncellebakcek['id'] ?>" type="hidden" name="hurun_id">
                        <input name="guncelbtn" type="submit" class="btn btn-outline-warning" value="GÜNCELLE" style="margin-bottom:3px; "/>
                        </form>
                    </div>        
                </div>
            </div>
            <div class="col-md-4"></div> <?php
            
        }else {

            echo "Hata Var";
            header("refresh:2,url=index.php");
            
        }


    }

    public function urunguncelle($database) {
        @$gbuton = $_POST['guncelbtn'];
        @$urun_id= $_POST['hurun_id'];
        @$mevcut_urun_stok = $_POST['hurun_stok'];
        @$satılan_mal = $_POST['urun_stok'];

        if ($gbuton) {

            $urun_stok = $mevcut_urun_stok - $satılan_mal;
            $urunguncelle = $database->prepare("UPDATE urunler SET urun_stok = ? WHERE id = ?");
            $urunguncelle->bindParam(1,$urun_stok,PDO::PARAM_INT);
            $urunguncelle->bindParam(2,$urun_id,PDO::PARAM_INT);
            $urunguncelledurum = $urunguncelle->execute();

            if ($urunguncelledurum) {  ?>

                <div class="alert alert-success">
                    Stok Güncelleme İşlemi Başarılı
                </div> <?php

                header("refresh:2,url=index.php");
                
            }else {

                echo "Hata Var";
                header("refresh:2,url=index.php");
                
            }


            
        }else {

            echo "Hata Var";
            header("refresh:2,url=index.php");
            
        }

    }

    public function linkizinkontrol($database) {
        $izinkontrolcek = $this->kullanicibilgial($database);

        if (@$izinkontrolcek['kullanici_yetki'] == 1) { ?>

            <li class="nav-item" id="islem">
                <a class="nav-link btn btn-outline-dark" href="index.php?islem=sifreguncelleform">Şifre Değiştir</a>
            </li>
            <li class="nav-item" id="islem">
                <a class="nav-link btn btn-outline-dark"  href="index.php?islem=cikis">Çıkış</a>
            </li> <?php
                
        }else { ?>

            <li class="nav-item"  id="islem">
                <a class="nav-link btn btn-outline-dark" href="index.php?islem=islemler">İşlemler</a>
            </li>
            <li class="nav-item" id="islem">
                <a class="nav-link btn btn-outline-dark" href="index.php?islem=sifreguncelleform">Şifre Değiştir</a>
            </li>
            <li class="nav-item" id="islem">
                <a class="nav-link btn btn-outline-dark"  href="index.php?islem=cikis">Çıkış</a>
            </li> <?php
                
        }

        
    }

    public function sifreguncelleform($database) {  ?>
        <div class="row" style="text-align:center">
            <form action="index.php?islem=sifreguncelle" method="post">
                <div class="col-md-12">
                    Eski Şifre<br>
                    <input type="password" name="eski_sifre">
                </div>
                <div class="col-md-12">
                    Yeni Şifre<br>
                    <input type="password" name="yeni_sifre1">
                </div>
                <div class="col-md-12">
                    Yeni Şifre Tekrar<br>
                    <input type="password" name="yeni_sifre2">
                </div>
                <div class="col-md-12"><br>
                    <input value="Güncelle" class="btn btn-outline-warning" type="submit" name="sifreguncellebtn">
                </div>
            </form>
        </div> <?php
    }

    public function sifreguncelle($database) {
        @$sifreguncellebtn = $_POST['sifreguncellebtn'];
        @$eski_sifre = $_POST['eski_sifre'];
        @$yeni_sifre1 = $_POST['yeni_sifre1'];
        @$yeni_sifre2 = $_POST['yeni_sifre2'];

        if ($sifreguncellebtn) {

            $eski_sifre = md5(sha1(md5($eski_sifre)));
            $kullanici_ad = $_COOKIE['kullanici_ad'];

            if ($eski_sifre == $_COOKIE['kullanici_sifre']) {

                if ($yeni_sifre1 == $yeni_sifre2) {

                    $yeni_sifre1 = md5(sha1(md5($yeni_sifre1)));

                    $sifreguncelle = $database->prepare("UPDATE kullanici set kullanici_sifre = ? WHERE kullanici_ad = ? AND kullanici_sifre = ?");
                    $sifreguncelle->bindParam(1,$yeni_sifre1,PDO::PARAM_STR);
                    $sifreguncelle->bindParam(2,$kullanici_ad,PDO::PARAM_STR);
                    $sifreguncelle->bindParam(3,$_COOKIE['kullanici_sifre'],PDO::PARAM_STR);
                    $sifreguncelledurum = $sifreguncelle->execute();

                    if ($sifreguncelledurum) {

                        setcookie("kullanici_sifre",$yeni_sifre1,time() + 60*60*24);

                        echo "Şifre Güncelleme Başarılı"."<br>"."Yönlendiriliyor..";
                        header("refresh:2,url=index.php");
                       
                    }else {

                        echo "Hata";
                        header("refresh:2,url=index.php");
                        
                    }


                    
                }else {

                    echo "Yeni Şifreler Uyuşmuyor";
                    header("refresh:2,url=index.php");

                }


                
            }else {

                echo "Mevcut Şifrede Hata Var";
                header("refresh:2,url=index.php");
                
            }

            
            
        }else {

            echo "Hata Var";
            header("refresh:2,url=index.php");
            
        }

    }

    public function cikis($database) {
        setcookie("kullanici_ad",$_COOKIE['kullanici_ad'],time() - 10 );
		setcookie("kullanici_sifre",$_COOKIE['kullanici_sifre'],time() - 10);

        echo "Çıkış Başarılı"."<br>"."Yönlendiriliyor..";
        header("refresh:2,url=index.php");

    }

    public function tercihkontrol($database) {
        $tercihkontrolcek = $this->kullanicibilgial($database);
    
        if ($tercihkontrolcek['kullanici_tercih'] == 1) { ?>

            <option selected="selected" value="1">Kutu</option>
            <option value="2">Liste</option> <?php
                
        } else { ?>
    
            <option value="1">Kutu</option>
            <option selected="selected" value="2">Liste</option> <?php
        
        }
    }

    public function tercihguncelle($database) {
        @$tercihbtn = $_POST['tercihbtn'];
        @$gelen_tercih = $_POST['gelen_tercih'];

        $tercihguncellebakcek = $this->kullanicibilgial($database);
        $kullanici_id = $tercihguncellebakcek['id'];

        if ($tercihbtn) {

            $tercihguncelle = $database->prepare("UPDATE kullanici set kullanici_tercih = ? WHERE id = ?");
            $tercihguncelle->bindParam(1,$gelen_tercih,PDO::PARAM_INT);
            $tercihguncelle->bindParam(2,$kullanici_id,PDO::PARAM_INT);
            $tercihguncelledurum = $tercihguncelle->execute();

            if ($tercihguncelledurum) {

                echo "Tercih Güncelleme Başarılı"."<br>"."Yönlendiriliyor..";
                header("refresh:2,url=index.php");
                
            }

        }else {

            echo "Hata Var";
            header("refresh:2,url=index.php");

        }

    }

    public function islemler($database) {
        $islemlerbakcek = $this->kullanicibilgial($database);

        if ($islemlerbakcek['kullanici_yetki'] == 2) { ?>

            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-danger" href="index.php?islem=ikategoriekleform">Kategori Ekle</a>
                    </div>
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-primary" href="index.php?islem=iurunlistele">Ürün Listele</a>
                    </div>
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-info" href="index.php?islem=italepsayfa">Talepler</a>
                    </div>
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-warning" href="index.php?islem=iurunrapor">Ürün Rapor</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>  <?php
            
        }elseif ($islemlerbakcek['kullanici_yetki'] == 3) { ?>

            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-danger" href="index.php?islem=ikategoriekleform">Kategori Ekle</a>
                    </div>
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-primary" href="index.php?islem=iurunlistele">Ürün Listele</a>
                    </div>
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-warning" href="index.php?islem=iurunrapor">Ürün Rapor</a>
                    </div>
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-success" href="index.php?islem=ikullanicilistele">Kullanıcı Listele</a>
                    </div>
                    <div class="col-md-6" style="padding:5px;">
                        <a class="btn btn-outline-secondary" href="index.php?islem=iayarlar">Ayarlar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>  <?php
            
        }else {

            echo "Hata Var";
            header("refresh:2,url=index.php");
            
        }
    }

    // İŞLEMLER MENÜSÜ FONKSİYONLARI

    public function ikategoriekleform($database) {  ?>
        <div class="row" style="text-align:center">
            <form action="index.php?islem=ikategoriekle" method="post">
                <div class="col-md-12">
                    Kategori Ad <br>
                    <input placeholder="Kategori İsmi Giriniz..." type="text" name="kategori_ad">
                </div>
                <div class="col-md-12"><br>
                    <input value="Ekle" class="btn btn-outline-success" type="submit" name="kategoriformbtn">
                </div>
            </form>
        </div> <?php
    }

    public function ikategoriekle($database) {
        @$kategori_ad = $_POST['kategori_ad'];
        @$kategoriformbtn = $_POST['kategoriformbtn'];

        if ($kategoriformbtn) {

            $kategoriekle = $database->prepare("INSERT into kategori (kategori_ad) VALUES (?)");
            $kategoriekle->bindParam(1,$kategori_ad,PDO::PARAM_STR);
            $kategoriekledurum = $kategoriekle->execute();

            if ($kategoriekledurum) {

                echo "Kategori Ekleme Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                header("refresh:2,url=index.php");
               
            }else {

                echo "Hata Var";
                header("refresh:2,url=index.php");

            }
 
        }else {

            echo "Hata Var";
            header("refresh:2,url=index.php");

        }

    }

    public function iurunlistele($database) {
        $urunal = $database->prepare("SELECT * FROM urunler");
        $urunal->execute(); ?>
        <table class="table table-hover text-center">
            <thead>
                <tr>
                    <th colspan="4">
                        <a class="btn btn-outline-primary" href="index.php?islem=iurunekleform">Ürün Ekle</a>
                    </th>
                </tr>
            </thead> 
            <thead>
                <tr>
                    <th>Ürün Adı</th>
                    <th>Stok Durumu</th>
                    <th>Ürün Güncelle</th>
                    <th>Ürün Sil</th>
                </tr>
            </thead> 
                <tbody> <?php
                    while($urunalcek = $urunal->fetch(PDO::FETCH_ASSOC)){ ?>
                        <tr>
                            <td><?php echo $urunalcek['urun_ad'] ?></td>
                            <td><b class="text-danger"><?php echo $urunalcek['urun_stok'] ?></b></td>
                            <td>
                                <form action="index.php?islem=iurunguncelleform" method="post">
                                    <input type="hidden" value="<?php echo $urunalcek['id'] ?>" name="hurun_id">
                                    <input name="guncelbtn" type="submit" class="btn btn-outline-success" value="Ürün Güncelle" style="margin-bottom:3px; "/>
                                </form>
                            </td>
                            <td>
                                <form action="index.php?islem=iurunsil" method="post">
                                    <input type="hidden" value="<?php echo $urunalcek['id'] ?>" name="hurun_id">
                                    <input name="silbtn" type="submit" class="btn btn-outline-danger" value="Ürün Sil" style="margin-bottom:3px; "/>
                                </form>
                            </td>
                        </tr> <?php
                    } ?>
                </tbody>
        </table> <?php
    }

    public function iurunguncelleform($database) {
        @$urun_id = $_POST['hurun_id'];
        $urunguncellebak = $database->prepare("SELECT * FROM urunler WHERE id = ?");
        $urunguncellebak->bindParam(1,$urun_id,PDO::PARAM_INT);
        $urunguncellebak->execute();
        $urunguncellebakcek = $urunguncellebak->fetch();?>
        <div class="row" style="text-align:center">
            <form action="index.php?islem=iurunguncelle" method="post">
                <div class="col-md-12">
                    Ürün Ad <br>
                    <input value="<?php echo $urunguncellebakcek['urun_ad'] ?>" type="text" name="urun_ad">
                </div>
                <div class="col-md-12">
                    Stok Durum <br>
                    <input value="<?php echo $urunguncellebakcek['urun_stok'] ?>" type="text" name="urun_stok">
                </div>
                <div class="col-md-12"><br>
                    <input value="<?php echo $urunguncellebakcek['id'] ?>" type="hidden" name="hurun_id">
                    <input value="GÜNCELLE" class="btn btn-outline-success" type="submit" name="urunguncellebtn">
                </div>
            </form>
        </div> <?php
    }

    public function iurunguncelle($database) {
        @$urun_id = $_POST['hurun_id'];
        @$urun_ad = $_POST['urun_ad'];
        @$urun_stok = $_POST['urun_stok'];
        @$urunguncellebtn = $_POST['urunguncellebtn'];

        if ($urunguncellebtn) {
            
            $urunguncelle = $database->prepare("UPDATE urunler set urun_ad = ? , urun_stok = ? WHERE id = ?");
            $urunguncelle->bindParam(1,$urun_ad,PDO::PARAM_STR);
            $urunguncelle->bindParam(2,$urun_stok,PDO::PARAM_INT);
            $urunguncelle->bindParam(3,$urun_id,PDO::PARAM_INT);
            $urunguncelledurum = $urunguncelle->execute();

            if ($urunguncelledurum) {

                echo "Ürün Güncelleme Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                header("refresh:2,url=index.php?islem=iurunlistele");
                
            }else {

                echo "Hata Var";
                header("refresh:2,url=index.php");

            }
           
        }
        
    }

    public function iurunsil($database) {
        @$silbtn = $_POST['silbtn'];
        @$urun_id = $_POST['hurun_id'];

        if ($silbtn) {

            $sil = $database->prepare("DELETE FROM urunler WHERE id = ?");
            $sil->bindParam(1,$urun_id,PDO::PARAM_STR);
            $sildurum = $sil->execute();

            if ($sildurum) {

                echo "Ürün Silme Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                header("refresh:2,url=index.php?islem=iurunlistele");
                
            }else {
                
                echo "Hata Var";
                header("refresh:2,url=index.php");

            }

        }else {
            
            echo "Hata Var";
            header("refresh:2,url=index.php");

        }

    }

    public function iurunekleform($database) { ?>
        <div class="row" style="text-align:center">
            <form action="index.php?islem=iurunekle" method="post">
                <div class="col-md-12">
                    Ürün Adı <br>
                    <input placeholder="Lütfen Ürün Adı Giriniz..." type="text" name="urun_ad">
                </div>
                <div class="col-md-12">
                    Stok Durumu <br>
                    <input placeholder="Lütfen Ürün Stok Durumu Giriniz..." type="text" name="urun_stok">
                </div>
                <div class="col-md-12">
                    Ürün Kategorisi <br>
                    <select name="kategori_id"> <?php
                        $kategoribak = $database->prepare("SELECT * FROM kategori");
                        $kategoribak->execute();
                        while($kategoribakcek = $kategoribak->fetch(PDO::FETCH_ASSOC)){?>

                            <option value="<?php echo $kategoribakcek['id']  ?>"><?php echo $kategoribakcek['kategori_ad']  ?></option> <?php

                        } ?>                  
                    </select>
                </div>
                <div class="col-md-12"><br>
                    <input value="EKLE" class="btn btn-outline-success" type="submit" name="uruneklebtn">
                </div>
            </form>
        </div> <?php
    }

    public function iurunekle($database) {
        @$uruneklebtn = $_POST['uruneklebtn'];
        @$urun_ad = $_POST['urun_ad'];
        @$urun_stok = $_POST['urun_stok'];
        @$kategori_id = $_POST['kategori_id'];

        if($uruneklebtn){

            $urunekle = $database->prepare("INSERT INTO urunler (kategori_id,urun_ad,urun_stok) VALUES (?,?,?)");
            $urunekle->bindParam(1,$kategori_id,PDO::PARAM_INT);
            $urunekle->bindParam(2,$urun_ad,PDO::PARAM_STR);
            $urunekle->bindParam(3,$urun_stok,PDO::PARAM_INT);
            $urunekledurum = $urunekle->execute();

            if ($urunekledurum) {

                echo "Ürün Ekleme Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                header("refresh:2,url=index.php?islem=iurunlistele");
                
            } else {

                echo "Hata Var";
                header("refresh:2,url=index.php");
               
            }
            


        }else {
            
            echo "Hata Var";
            header("refresh:2,url=index.php");

        }
    }

    public function italepsayfa($database) {
        $talepler = $database->prepare("SELECT urunler.id AS urun_id , urunler.urun_ad AS urun_ad , talepler.talep_stok AS talep_stok , talepler.talep_durum AS talep_durum , talepler.id AS talep_id FROM talepler JOIN urunler ON urunler.id = talepler.urun_id ORDER BY talep_durum ASC");
        $talepler->execute(); ?>
        <table class="table table-hover text-center">
            <thead>
                <tr>
                    <th>Talep Edilen Ürün</th>
                    <th>Talep Edilen Stok</th>
                    <th>Talep Durum</th>
                    <th>Talep Onay</th>
                    <th>Talep Sil</th>
                </tr>
            </thead> 
            <tbody> <?php
                while($taleplercek = $talepler->fetch(PDO::FETCH_ASSOC)){ ?>
                    <tr>
                        <td><?php echo $taleplercek['urun_ad'] ?></td>
                        <td><b class="text-danger"><?php echo $taleplercek['talep_stok'] ?></b></td>
                        <td> <?php
                            if ($taleplercek['talep_durum'] == 0) { ?>
                                <b class="text-danger">Onaylanmadı</b> <?php
                            }else { ?>
                                <b class="text-success">Onaylandı</b> <?php
                            } ?>
                        </td>
                        <td> <?php
                            if ($taleplercek['talep_durum'] == 0) { ?>
                                <form action="index.php?islem=italeponay" method="post">
                                    <input type="hidden" value="<?php echo $taleplercek['talep_id'] ?>" name="htalep_id">
                                    <input type="hidden" value="<?php echo $taleplercek['urun_id'] ?>" name="hurun_id">
                                    <input type="hidden" value="<?php echo $taleplercek['talep_stok'] ?>" name="hurun_stok">
                                    <input name="taleponaybtn" type="submit" class="btn btn-outline-warning" value="Talep Onayla" style="margin-bottom:3px; "/>
                                </form> <?php
                            }else { ?>
                                <b class="text-success">Talep Onaylanmış</b><?php
                            } ?>
                        </td>
                        <td>                  
                            <form action="index.php?islem=italepsil" method="post">
                                <input type="hidden" value="<?php echo $taleplercek['talep_id'] ?>" name="htalep_id">
                                <input name="talepsilbtn" type="submit" class="btn btn-outline-danger" value="Talep Sil" style="margin-bottom:3px; "/>
                            </form>
                        </td>
                    </tr> <?php
                } ?>
            </tbody>
        </table> <?php

    }

    public function italeponay($database) {
        @$taleponaybtn = $_POST['taleponaybtn'];
        @$talep_id = $_POST['htalep_id'];
        @$urun_id = $_POST['hurun_id'];
        @$urun_stok = $_POST['hurun_stok'];

        if ($taleponaybtn) {

            $taleponay = $database->prepare("UPDATE talepler SET talep_durum = 1 WHERE id = ?");
            $taleponay->bindParam(1,$talep_id,PDO::PARAM_INT);
            $taleponaydurum = $taleponay->execute();

            if ($taleponaydurum) {

                $urunstokbak = $database->prepare("SELECT * FROM urunler WHERE id = ? ");
                $urunstokbak->bindParam(1,$urun_id,PDO::PARAM_INT);
                $urunstokbak->execute();
                $urunstokbakcek = $urunstokbak->fetch();

                $mevcut_stok = $urunstokbakcek['urun_stok'];
                $yeni_stok = $mevcut_stok + $urun_stok;

                $urunstokguncelle = $database->prepare("UPDATE urunler set urun_stok = ? WHERE id = ?");
                $urunstokguncelle->bindParam(1,$yeni_stok,PDO::PARAM_INT);
                $urunstokguncelle->bindParam(2,$urun_id,PDO::PARAM_INT);
                $urunstokguncelledurum = $urunstokguncelle->execute();

                if ($urunstokguncelledurum) {

                    echo "Talep Onayı Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                    header("refresh:2,url=index.php");

                    
                } else {

                    echo "Hata Var";
                    header("refresh:2,url=index.php");
                    
                }        
                
            }else {

                echo "Hata Var";
                header("refresh:2,url=index.php");

            }
            
        } else {

            echo "Hata Var";
            header("refresh:2,url=index.php");

        }
        
    }

    public function italepsil($database) {
        @$talepsilbtn = $_POST['talepsilbtn'];
        @$talep_id = $_POST['htalep_id'];

        if ($talepsilbtn) {

            $talepsil = $database->prepare("DELETE from talepler WHERE id = ?");
            $talepsil->bindParam(1,$talep_id,PDO::PARAM_INT);
            $talepsildurum = $talepsil->execute();

            if ($talepsildurum) {

                echo "Talep Silme Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                header("refresh:2,url=index.php");

            }else {

                echo "Hata Var";
                header("refresh:2,url=index.php");

            }
            
        } else {

            echo "Hata Var";
            header("refresh:2,url=index.php");

        }

    }

    public function iurunrapor($database) {
        $toplamkategori = $database->prepare("SELECT * FROM kategori");
        $toplamkategori->execute();

        $toplamurun = $database->prepare("SELECT * FROM urunler");
        $toplamurun->execute();

        $toplamstok = $database->prepare("SELECT SUM(urun_stok) FROM urunler");
        $toplamstok->execute();
        $toplamstokcek = $toplamstok->fetch();

        $stokazalan = $database->prepare("SELECT * FROM urunler ORDER BY urun_stok ASC LIMIT 5");
        $stokazalan->execute();

        $stokartan = $database->prepare("SELECT * FROM urunler ORDER BY urun_stok DESC LIMIT 5");
        $stokartan->execute();
        
        ?>
        <table class="table text-center">
            <thead>
                <tr>
                    <th>Toplam Kategori</th>
                    <th>Toplam Ürün</th>
                    <th>Toplam Ürün Stok Adet</th>
                </tr>
            </thead> 
            <tbody> 
                <tr>
                    <td><b class="text-danger"><?php echo $toplamkategori->rowCount(); ?></b></td>
                    <td><b class="text-warning"><?php echo $toplamurun->rowCount(); ?></b></td>
                    <td><b class="text-success"><?php echo $toplamstokcek['SUM(urun_stok)'] ?></b></td>
                </tr>
            </tbody>
        </table>
        <div class="col-md-4">
            <table class="table text-center">
                <thead>
                    <tr>
                        <th colspan=2>Kategori Adet</th>
                    </tr>
                </thead>
                <thead>
                    <tr>
                        <th>Kategori Ad</th>
                        <th>Ürün Adet</th>
                    </tr>
                </thead>  
                <tbody><?php
                    while($toplamkategoricek = $toplamkategori->fetch(PDO::FETCH_ASSOC)){ ?>
                        <tr>
                            <td><b class="text-danger"><?php echo $toplamkategoricek['kategori_ad'] ?></b></td> <?php
                            $kategori_id = $toplamkategoricek['id'];
                            $kategoriadet = $database->prepare("SELECT * FROM urunler WHERE kategori_id = ?");
                            $kategoriadet->bindParam(1,$kategori_id,PDO::PARAM_INT);
                            $kategoriadet->execute(); ?>
                            <td><b class="text-info"><?php echo $kategoriadet->rowCount(); ?></b></td>
                        </tr> <?php
                    } ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table text-center">
                <thead>
                    <tr>
                        <th colspan=2>Stoğu - Azalan</th>
                    </tr>
                </thead>
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Stok Adeti</th>
                    </tr>
                </thead>  
                <tbody><?php
                    while($stokazalancek = $stokazalan->fetch(PDO::FETCH_ASSOC)){ ?>
                        <tr>
                            <td><b class="text-secondary"><?php echo $stokazalancek['urun_ad'] ?></b></td>
                            <td><b class="text-danger"><?php echo $stokazalancek['urun_stok'] ?></b></td>
                        </tr> <?php
                    } ?>         
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table text-center">
                <thead>
                    <tr>
                        <th colspan=2>Stoğu - Artan</th>
                    </tr>
                </thead>
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Stok Adeti</th>
                    </tr>
                </thead>  
                <tbody><?php
                    while($stokartancek = $stokartan->fetch(PDO::FETCH_ASSOC)){ ?>
                        <tr>
                            <td><b class="text-secondary"><?php echo $stokartancek['urun_ad'] ?></b></td>
                            <td><b class="text-success"><?php echo $stokartancek['urun_stok'] ?></b></td>
                        </tr> <?php
                    } ?>         
                </tbody>
            </table>
        </div><?php

    }

    public function ikullanicilistele($database) { 
        $kullanicial = $database->prepare("SELECT * FROM kullanici ORDER BY kullanici_yetki DESC");
        $kullanicial->execute(); ?>
        <table class="table table-hover text-center">
            <thead>
                <tr>
                    <th colspan="3">
                        <a class="btn btn-outline-primary" href="index.php?islem=ikullaniciekleform">Kullanıcı Ekle</a>
                    </th>
                </tr>
            </thead> 
            <thead>
                <tr>
                    <th>Kullanıcı Adı</th>
                    <th>Kullanıcı Yetki</th>
                    <th>Kullanıcı Sil</th>
                </tr>
            </thead> 
                <tbody> <?php
                    while($kullanicialcek = $kullanicial->fetch(PDO::FETCH_ASSOC)){ ?>
                        <tr>
                            <td><?php echo $kullanicialcek['kullanici_ad'] ?></td>
                            <td><?php
                                if ($kullanicialcek['kullanici_yetki'] == 1) { ?>
                                    <b class="text-danger">Depo</b> <?php
                                }elseif ($kullanicialcek['kullanici_yetki'] == 2) {?>
                                    <b class="text-warning">Satın Alma</b> <?php
                                }else {?>
                                    <b class="text-success">Yönetici</b> <?php   
                                }         
                            ?></td>
                            <td>
                                <form action="index.php?islem=ikullanicisil" method="post">
                                    <input type="hidden" value="<?php echo $kullanicialcek['id'] ?>" name="hkullanici_id">
                                    <input name="kullanicisilbtn" type="submit" class="btn btn-outline-danger" value="Kullanıcı Sil" style="margin-bottom:3px; "/>
                                </form>
                            </td>
                        </tr> <?php
                    } ?>
                </tbody>
        </table> <?php

    }

    public function ikullaniciekleform($database) { ?>
        <div class="row" style="text-align:center">
            <form action="index.php?islem=ikullaniciekle" method="post">
                <div class="col-md-12">
                    Kullanıcı Adı <br>
                    <input placeholder="Lütfen Kullanıcı Adı Giriniz..." type="text" name="kullanici_ad">
                </div>
                <div class="col-md-12">
                    Kullanıcı Şifre <br>
                    <input placeholder="Lütfen Kullanıcı Şifre Giriniz..." type="password" name="kullanici_sifre">
                </div>
                <div class="col-md-12">
                    Kullanıcı Yetki <br>
                    <select name="kullanici_yetki">
                        <option value="1">Depo</option>      
                        <option value="2">Satın Alma</option>        
                        <option value="3">Yönetici</option>      
                    </select>
                </div>
                <div class="col-md-12"><br>
                    <input value="Kullanıcı Ekle" class="btn btn-outline-success" type="submit" name="kullanıcıeklebtn">
                </div>
            </form>
        </div> <?php
    }

    public function ikullaniciekle($database) {
        @$kullanici_ad = $_POST['kullanici_ad'];
        @$kullanici_sifre = md5(sha1(md5($_POST['kullanici_sifre'])));
        @$kullanici_yetki = $_POST['kullanici_yetki'];

        if ($kullanici_ad) {
            $kullaniciekle = $database->prepare("INSERT INTO kullanici (kullanici_ad,kullanici_sifre,kullanici_yetki) VALUES (?,?,?)");
            $kullaniciekle->bindParam(1,$kullanici_ad,PDO::PARAM_STR);
            $kullaniciekle->bindParam(2,$kullanici_sifre,PDO::PARAM_STR);
            $kullaniciekle->bindParam(3,$kullanici_yetki,PDO::PARAM_INT);
            $kullaniciekledurum = $kullaniciekle->execute();

            if ($kullaniciekledurum) {

                echo "Kullanıcı Ekleme Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                header("refresh:2,url=index.php?islem=ikullanicilistele");
              
            }else {

                echo "Hata Var";
                header("refresh:2,url=index.php");
                
            }
            
        } else {

            echo "Hata Var";
            header("refresh:2,url=index.php");
            
        }
        


    }

    public function ikullanicisil($database) {
        @$kullanicisilbtn = $_POST['kullanicisilbtn'];
        @$kullanici_id = $_POST['hkullanici_id'];

        if ($kullanicisilbtn) {

            $sil = $database->prepare("DELETE FROM kullanici WHERE id = ?");
            $sil->bindParam(1,$kullanici_id,PDO::PARAM_STR);
            $sildurum = $sil->execute();

            if ($sildurum) {

                echo "Kullanıcı Silme Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                header("refresh:2,url=index.php?islem=ikullanicilistele");
                
            }else {
                
                echo "Hata Var";
                header("refresh:2,url=index.php");

            }

        }else {
            
            echo "Hata Var";
            header("refresh:2,url=index.php");

        }

    }

    public function iayarlar($database) {
        @$ayardegistirbtn = $_POST['ayardegistirbtn'];
        @$ayarlar_durum = $_POST['ayarlar_durum'];
        @$ayar_id = $_POST['hayar_id'];

        if (!$ayardegistirbtn) { ?>

            <div class="col-md-4"></div>
            <div class="col-md-4 table-bordered table-light" style="margin-top:50px; border-radius:10px;">
                <div class="row" style="text-align:center;">
                    <div class="col-md-12">
                        <b class="text-danger">
                            <form action="index.php?islem=iayarlar" method="post">
                                Otomatik Tercih :
                                <select name="ayarlar_durum">
                                    <?php
                                        $ayarbak = $database->prepare("SELECT * FROM ayarlar");
                                        $ayarbak->execute();
                                        $ayarbakcek = $ayarbak->fetch();

                                        if ($ayarbakcek['ayarlar_durum'] == 1) { ?>

                                            <option selected="selected" value="1">Manuel</option>
                                            <option value="2">Otomatik</option> <?php
                                            
                                        }else { ?>
                                            
                                            <option value="1">Manuel</option>
                                            <option selected="selected" value="2">Otomatik</option> <?php

                                        }
                                    ?>
                                </select><br><br>
                        </b>
                    </div>
                    <div class="col-md-12">
                        <input name="hayar_id" type="hidden" value="<?php echo  $ayarbakcek['id']; ?>">
                        <input class="btn btn-outline-success" name="ayardegistirbtn" type="submit" value="DEĞİŞTİR" style="margin-bottom:3px;">
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div> <?php
            
        }else {

            $ayardegistir = $database->prepare("UPDATE ayarlar SET ayarlar_durum = ? WHERE id = ?");
            $ayardegistir->bindParam(1,$ayarlar_durum,PDO::PARAM_INT);
            $ayardegistir->bindParam(2,$ayar_id,PDO::PARAM_INT);
            $ayardegistirdurum = $ayardegistir->execute();

            if ($ayardegistirdurum) {

                echo "Ayar Güncelleme Başarılı"."<br>"."Yönlendiriliyorsunuz...";
                header("refresh:2,url=index.php?islem=iayarlar");
                
            }else {

                echo "Hata Var";
                header("refresh:2,url=index.php");

            }


            
        }

    }



}


?>