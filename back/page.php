 <?php
//on teste si la variable de session S_SESSION['id_page'] existe
if(isset($_SESSION['id_compte']))
    {
    $titre="Gestion des pages";
    $form="form_page.html";
    //action par défaut du formulaire
    $action_form="inserer_page";
    //pour cocher par défaut visible à oui
    $check[1]="checked";
    //on définit les variables par défaut pour le redimensionnement des images
    $quality=80;
    $largeur_b=1600;
    $largeur_m=800;
    $largeur_s=60;

    if(isset($_SESSION['id_rubrique']))
        {
        //supprime la valeur de la variable
        unset($_SESSION['id_rubrique']); 
        }

    if(isset($_GET['cas']))
        {
        //on switche sur la valeur contenue dans $_GET['action']
        switch($_GET['cas'])
            {
            case "inserer_page":

            if(empty($_POST['id_rubrique']))
                {
                $confirmation="<p class=\"pas_ok\">La rubrique associée est obligatoire</p>";  
                $color_champ['id_rubrique']="color_champ"; 
                }
            else{
                //on stocke le id_rubrique en session
                $_SESSION['id_rubrique']=$_POST['id_rubrique']; 
                }
            if(empty($_POST['titre_page']))
                {
                $confirmation="<p class=\"pas_ok\">Le titre est obligatoire</p>";  
                $color_champ['titre_page']="color_champ"; 
                }
            elseif(empty($_POST['contenu_page']))
                {
                $confirmation="<p class=\"pas_ok\">Le contenu est obligatoire</p>";     
                $color_champ['contenu_page']="color_champ"; 
                }
            else{
                //on enregistre le compte dans la table comptes
                $requete="INSERT INTO pages SET id_compte='".$_SESSION['id_compte']."',
                                                id_rubrique='".$_POST['id_rubrique']."',
                                                titre_page='".security($_POST['titre_page'])."',
                                                contenu_page='".security($_POST['contenu_page'])."',
                                                visible='".$_POST['visible']."',
                                                date_page=NOW()";
                                                echo $requete;
                $resultat=mysqli_query($connexion,$requete);
                //on récupere le dernier id_compte qui vient d'être créé
                $dernier_id_cree=mysqli_insert_id($connexion);

                //si le champ parcourir est utilisé (pas vide)
                if(!empty($_FILES['img_page']['name']))
                    {
                    $tab_img=pathinfo($_FILES['img_page']['name']);
                    $extension=$tab_img['extension'];
                    //on teste si l'esxtension est aurorisé
                    if($extension=="png" OR $extension=="gif" OR $extension=="jpg" OR $extension=="webp")
                        {
                        //si le fichier est bien uploadé du local vers le distant
                        if(is_uploaded_file($_FILES['img_page']['tmp_name'])) 
                            {
                            //on détermine les chemins des 3 images à générer
                            $chemin_b="../medias/media" . $dernier_id_cree . "_b." . $extension; 
                            $chemin_m="../medias/media" . $dernier_id_cree . "_m." . $extension; 
                            $chemin_s="../medias/media" . $dernier_id_cree . "_s." . $extension; 
                            
                            if(copy($_FILES['img_page']['tmp_name'], $chemin_b))
                                {
                                //on prend les mesures du fichier image
                                $size=GetImageSize($chemin_b);	
                                $largeur=$size[0];
                                $hauteur=$size[1];
                                $rapport=$largeur/$hauteur;
                                
                                //si la largeur de l'image uploadée est inférieure à 1600 (voir ligne 9)
                                if($largeur<$largeur_b)
                                    {
                                    $largeur_b=$largeur;
                                    $hauteur_b=$hauteur;
                                    }
                                else{
                                    $hauteur_b=$largeur_b/$rapport;
                                    }
                                //on créé une copie en redimensionnant et en appliquant un taux de compression
                                redimage($chemin_b,$chemin_b,$largeur_b,$hauteur_b,$quality);

                                //si la largeur de l'image uploadée est inférieure à 800 (voir ligne 10)
                                if($largeur<$largeur_m)
                                    {
                                    $largeur_m=$largeur;
                                    $hauteur_m=$hauteur;
                                    }
                                else{
                                    $hauteur_m=$largeur_m/$rapport;
                                    }
                                redimage($chemin_b,$chemin_m,$largeur_m,$hauteur_m,$quality);

                                //on cree la miniature 
                                $hauteur_s=$largeur_s/$rapport;
                                redimage($chemin_b,$chemin_s,$largeur_s,$hauteur_s,$quality);

                                //on met à jour le champ img_compte de la table comptes 
                                $requete2="UPDATE pages SET img_page='" . $chemin_s . "' WHERE id_page='" . $dernier_id_cree . "'";
                                $resultat2=mysqli_query($connexion,$requete2);
                                $confirmation="<p class=\"ok\">La page a bien été enregistrée</p>";  
                                }

                            } 
                        }
                    else{
                        $confirmation="<p class=\"pas_ok\">Cette extension n'est pas autorisée</p>";  
                        }
                    }
                else{
                    //on confirme l'enregistrement
                    $confirmation="<p class=\"ok\">La page a bien été enregistrée</p>";  
                    }

                //on vide les champs du formulaire
                foreach($_POST AS $cle => $valeur)
                    {
                    //unset supprime une variable
                    unset($_POST[$cle]); 
                    }
                }


            break;

            case "avertir_page":
            
            if(isset($_GET['id_page']))
                {
                $confirmation="<p>Voulez-vous supprimer la page n°" . $_GET['id_page'] . "</p>"; 
                $confirmation.="<a href=\"back.php?action=page&cas=supprimer_page&id_page=" . $_GET['id_page'] . "\">OUI</a>&nbsp;&nbsp;&nbsp;";
                $confirmation.="<a href=\"back.php?action=page\">NON</a>";   
                }

            break;

            case "supprimer_page":

            if(isset($_GET['id_page']))
                {
                //on vérifie si il y a une image associée au compte
                $requete="SELECT * FROM pages WHERE id_page='" . $_GET['id_page'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                $ligne=mysqli_fetch_object($resultat);
                //si il y a une image
                if(!empty($ligne->img_page))
                    {
                    $chemin_b=str_replace("_s","_b",$ligne->img_page);
                    $chemin_m=str_replace("_s","_m",$ligne->img_page);
                    $chemin_s=$ligne->img_compte;
                    //on supprime les fichiers image (le @ désactive les warning)
                    @unlink($chemin_b);
                    @unlink($chemin_m);
                    @unlink($chemin_s);  
                    }    
                $requete2="DELETE FROM pages WHERE id_page='" . $_GET['id_page'] . "'";
                $resultat2=mysqli_query($connexion,$requete2);
                $confirmation="<p class=\"ok\">La page a bien été supprimée</p>";                        
                }

            break;

            case "changer_etat":

            if(isset($_GET['id_page']))
                {   
                if(isset($_GET['etat']))
                    {
                    $requete="UPDATE pages SET visible='" . $_GET['etat'] . "' WHERE id_page='" . $_GET['id_page'] . "'";
                    $resultat=mysqli_query($connexion,$requete);   
                    }
                }

            break;

            case "recharger_page":
            
            if(isset($_GET['id_page']))
                {
                $action_form="modifier_page&id_page=" . $_GET['id_page'];
                //on recharge les champs du formulaire
                $requete="SELECT * FROM pages WHERE id_page='" . $_GET['id_page'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                $ligne=mysqli_fetch_object($resultat);
                //on réattribue à chaque champ du formulaire la valeur récupérée dans la base
                $_POST['titre_page']=$ligne->titre_page;
                $_POST['contenu_page']=$ligne->contenu_page;
                $check[$ligne->visible]="checked";
                $_POST['date_page']=$ligne->date_page;
                $_SESSION['id_rubrique']=$ligne->id_rubrique; 

                //si le champ img_compte n'est pas vide
                if(!empty($ligne->img_page))
                    {
                    $miniature="<div><img src=\"". $ligne->img_page ."\" alt=\"\" />
                    <a href=\"back.php?action=page&cas=supprimer_img_page&id_page=" . $ligne->id_page . "\">supprimer</a></div>";
                    }
                }

            break;

            case "supprimer_img_page":

                if(isset($_GET['id_page']))
                    {
                    //on va chercher les élements dans la table pages
                    $requete="SELECT * FROM pages WHERE id_page='" . $_GET['id_page'] . "'";
                    $resultat=mysqli_query($connexion,$requete);
                    $ligne=mysqli_fetch_object($resultat);
                    $chemin_b=str_replace("_s","_b",$ligne->img_page);
                    $chemin_m=str_replace("_s","_m",$ligne->img_page);
                    @unlink($ligne->img_page);
                    @unlink($chemin_b);
                    @unlink($chemin_m);
    
                    $requete2="UPDATE pages SET img_page=NULL WHERE id_page='" . $_GET['id_page'] . "'";
                    $resultat2=mysqli_query($connexion,$requete2);
                    $confirmation="<p class=\"ok\">L'illustration de la page a bien été supprimée</p>";    
                    }
    
                break;

            case "modifier_page":
         
            if(isset($_GET['id_page']))
                {
                if(empty($_POST['id_rubrique']))
                    {
                    $confirmation="<p class=\"pas_ok\">La rubrique associée est obligatoire</p>";  
                    $color_champ['id_rubrique']="color_champ"; 
                    }
                elseif(empty($_POST['titre_page']))
                    {
                    $confirmation="<p class=\"pas_ok\">Le titre est obligatoire</p>";   
                    $color_champ['titre_page']="color_champ";
                    }
                elseif(empty($_POST['contenu_page']))
                    {
                    $confirmation="<p class=\"pas_ok\">Le contenu est obligatoire</p>";     
                    $color_champ['contenu_page']="color_champ";
                    }
                else{
                    $requete="UPDATE pages SET id_compte='".$_SESSION['id_compte']."',
                                                id_rubrique='".$_POST['id_rubrique']."',
                                                titre_page='".security($_POST['titre_page'])."',
                                                contenu_page='".security($_POST['contenu_page'])."',
                                                visible='".$_POST['visible']."',
                                                date_page=NOW() WHERE id_page='". $_GET['id_page'] ."'"; 
                    //echo $requete;
                    $resultat=mysqli_query($connexion, $requete);

                    //si le champ parcourir est utilisé (pas vide)
                    if(!empty($_FILES['img_page']['name']))
                        {
                        $tab_img=pathinfo($_FILES['img_page']['name']);
                        $extension=$tab_img['extension'];
                        //on teste si l'esxtension est aurorisé
                        if($extension=="png" OR $extension=="gif" OR $extension=="jpg" OR $extension=="webp")
                            {
                            //si le fichier est bien uploadé du local vers le distant
                            if(is_uploaded_file($_FILES['img_page']['tmp_name'])) 
                                {
                                //on détermine les chemins des 3 images à générer
                                $chemin_b="../medias/media" . $_GET['id_page'] . "_b." . $extension; 
                                $chemin_m="../medias/media" . $_GET['id_page'] . "_m." . $extension; 
                                $chemin_s="../medias/media" . $_GET['id_page'] . "_s." . $extension; 
                                
                                if(copy($_FILES['img_page']['tmp_name'], $chemin_b))
                                    {
                                    //on prend les mesures du fichier image
                                    $size=GetImageSize($chemin_b);	
                                    $largeur=$size[0];
                                    $hauteur=$size[1];
                                    $rapport=$largeur/$hauteur;
                                    
                                    //si la largeur de l'image uploadée est inférieure à 1600 (voir ligne 9)
                                    if($largeur<$largeur_b)
                                        {
                                        $largeur_b=$largeur;
                                        $hauteur_b=$hauteur;
                                        }
                                    else{
                                        $hauteur_b=$largeur_b/$rapport;
                                        }
                                    //on créé une copie en redimensionnant et en appliquant un taux de compression
                                    redimage($chemin_b,$chemin_b,$largeur_b,$hauteur_b,$quality);

                                    //si la largeur de l'image uploadée est inférieure à 800 (voir ligne 10)
                                    if($largeur<$largeur_m)
                                        {
                                        $largeur_m=$largeur;
                                        $hauteur_m=$hauteur;
                                        }
                                    else{
                                        $hauteur_m=$largeur_m/$rapport;
                                        }
                                    redimage($chemin_b,$chemin_m,$largeur_m,$hauteur_m,$quality);

                                    //on cree la miniature 
                                    $hauteur_s=$largeur_s/$rapport;
                                    redimage($chemin_b,$chemin_s,$largeur_s,$hauteur_s,$quality);

                                    //on met à jour le champ img_compte de la table comptes 
                                    $requete2="UPDATE pages SET img_page='" . $chemin_s . "' WHERE id_page='" . $_GET['id_page'] . "'";
                                    $resultat2=mysqli_query($connexion,$requete2);
                                    $confirmation="<p class=\"ok\">La page a bien été modifiée</p>";  
                                    }

                                } 
                            }
                        else{
                            $confirmation="<p class=\"pas_ok\">Cette extension n'est pas autorisée</p>";  
                            }
                        }
                    else{
                        //on confirme l'enregistrement
                        $confirmation="<p class=\"ok\">Le compte a bien été modifié</p>";  
                        }
                    }

                //on vide les champs du formulaire
                foreach($_POST AS $cle => $valeur)
                    {
                    //unset supprime une variable
                    unset($_POST[$cle]);    
                    }
                }
            break;
            }     
        }

    //on créé la liste déroulante dynamique des rubriques
    $requete0="SELECT * FROM rubriques ORDER BY rang";
    $resultat0=mysqli_query($connexion,$requete0);
    //tant que $resultat contient des lignes (uplets)
    $list_rubriques="<option value=\"\">Rubrique [obligatoire]</option>";
    while($ligne0=mysqli_fetch_object($resultat0))
        {
        if(isset($_SESSION['id_rubrique']) && $_SESSION['id_rubrique']==$ligne0->id_rubrique)
            {
            $list_rubriques.="<option selected value=\"". $ligne0->id_rubrique ."\">" . $ligne0->nom_rubrique . "</option>";   
            }
        else{
            $list_rubriques.="<option value=\"". $ligne0->id_rubrique ."\">" . $ligne0->nom_rubrique . "</option>";   
            }
        }
//=============================================================================

    //tableau d'affichage des pages
    //on selectionne tous les pages triés par date de création et le compte correspondant
    $requete="SELECT r.*, p.*, c.* 
                FROM rubriques AS r 
                INNER JOIN pages AS p 
                INNER JOIN comptes AS c 
                ON r.id_rubrique=p.id_rubrique    
                AND p.id_compte=c.id_compte 
                ORDER BY r.rang, p.rang ASC";



    $resultat=mysqli_query($connexion,$requete);
    //tant que $resultat contient des lignes (uplets)
    $content="";

    $tab_rubrique=array();
    $i=0;

    while($ligne=mysqli_fetch_object($resultat))
        {
        $tab_rubrique[$i]=$ligne->id_rubrique;
        if($i==0 || ($i>0 && $tab_rubrique[$i]!=$tab_rubrique[$i-1])){
            $content.="<div>".$ligne->nom_rubrique."</div>";
        }


        $content.="<details class=\"tab_results\">";


        $content.="<summary>";
        $content.="<div>". $ligne->id_page ." - ". $ligne->titre_page ."</div>";
        //si il y a un avatar
        if(!empty($ligne->img_page))
            {
            $content.="<div><img src=\"". $ligne->img_page ."\" alt=\"\" /></div>";   
            }
        if($ligne->visible==1)
            {
            $content.="<div class=\"actions\"><a href=\"back.php?action=page&cas=changer_etat&etat=0&id_page=" . $ligne->id_page . "\"><span class=\"dashicons dashicons-visibility\"></span></a>";    
            }
        else{
            $content.="<div class=\"actions\"><a href=\"back.php?action=page&cas=changer_etat&etat=1&id_page=" . $ligne->id_page . "\"><span class=\"dashicons dashicons-hidden\"></span></a>"; 
            }
        $content.="<a href=\"back.php?action=page&cas=recharger_page&id_page=" . $ligne->id_page . "#form_back\"><span class=\"dashicons dashicons-admin-customizer\"></span></a>"; 
        $content.="<a href=\"back.php?action=page&cas=avertir_page&id_page=" . $ligne->id_page . "\"><span class=\"dashicons dashicons-no\"></span></a></div>"; 
        $content.="</summary>"; 

        $content.="<div class=\"all\">Auteur : " . $ligne->prenom_compte . " " . $ligne->nom_compte . "<br>Créée le : ".$ligne->date_page ."<br><br>".$ligne->contenu_page ."</div>";

        $content.="</details>";
        $i++;
        }

    }
else{
    //l'utilisateur n'est pas autorisé
    header("Location:../log/login.php");
    }

?>
