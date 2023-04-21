<?php
//on teste si la variable de session S_SESSION['id_compte'] existe
if(isset($_SESSION['id_compte']))
    {
    $titre="Gestion des comptes";
    $form="form_compte.html";
    //on définit les variables par défaut pour le redimensionnement des images
    $quality=80;
    $largeur_b=1600;
    $largeur_m=800;
    $largeur_s=60;

    //action par défaut du formulaire
    $action_form="inserer_compte";

    if(isset($_GET['cas']))
        {
        //on switche sur la valeur contenue dans $_GET['action']
        switch($_GET['cas'])
            {
            case "inserer_compte":

            //pour maintenir la selection de la liste déroulante
            if(isset($_POST['statut_compte']))
                {
                $select[$_POST['statut_compte']]="selected";   
                }

            if(empty($_POST['nom_compte']))
                {
                $confirmation="<p class=\"pas_ok\">Le nom est obligatoire</p>";   
                }
            elseif(empty($_POST['statut_compte']))
                {
                $confirmation="<p class=\"pas_ok\">Le statut est obligatoire</p>";     
                }               
            elseif(empty($_POST['email_compte']))
                {
                $confirmation="<p class=\"pas_ok\">L'email est obligatoire</p>";     
                }
            elseif(empty($_POST['login_compte']))
                {
                $confirmation="<p class=\"pas_ok\">Le login est obligatoire</p>";       
                }
            elseif(empty($_POST['pass_compte']))
                {
                $confirmation="<p class=\"pas_ok\">Le mot de passe est obligatoire</p>";       
                }  
            else{
                //on enregistre le compte dans la table comptes
                $requete="INSERT INTO comptes SET nom_compte='".security($_POST['nom_compte'])."',
                                                prenom_compte='".security($_POST['prenom_compte'])."',
                                                statut_compte='".$_POST['statut_compte']."',
                                                email_compte='".security($_POST['email_compte'])."',
                                                login_compte='".security($_POST['login_compte'])."',
                                                pass_compte=SHA1('".$_POST['pass_compte']."')";
                                                //echo $requete;
                $resultat=mysqli_query($connexion,$requete);
                //on récupere le dernier id_compte qui vient d'être créé
                $dernier_id_cree=mysqli_insert_id($connexion);

                //================================================================================
                if($dernier_id_cree==0)
                    {
                    $confirmation="<p class=\"pas_ok\">Chaque compte doit avoir un email unique</p>";   
                    }
                else{
                    //si le champ parcourir est utilisé (pas vide)
                    if(!empty($_FILES['img_compte']['name']))
                        {
                        $tab_img=pathinfo($_FILES['img_compte']['name']);
                        $extension=$tab_img['extension'];
                        //on teste si l'esxtension est aurorisé
                        if($extension=="png" OR $extension=="gif" OR $extension=="jpg" OR $extension=="webp")
                            {
                            //si le fichier est bien uploadé du local vers le distant
                            if(is_uploaded_file($_FILES['img_compte']['tmp_name'])) 
                                {
                                //on détermine les chemins des 3 images à générer
                                $chemin_b="../medias/avatar" . $dernier_id_cree . "_b." . $extension; 
                                $chemin_m="../medias/avatar" . $dernier_id_cree . "_m." . $extension; 
                                $chemin_s="../medias/avatar" . $dernier_id_cree . "_s." . $extension; 
                                
                                if(copy($_FILES['img_compte']['tmp_name'], $chemin_b))
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
                                    $requete2="UPDATE comptes SET img_compte='" . $chemin_s . "' WHERE id_compte='" . $dernier_id_cree . "'";
                                    $resultat2=mysqli_query($connexion,$requete2);
                                    $confirmation="<p class=\"ok\">Le compte a bien été enregistré</p>";  
                                    }

                                } 
                            }
                        else{
                            $confirmation="<p class=\"pas_ok\">Cette extension n'est pas autorisée</p>";  
                            }
                        }
                    else{
                        //on confirme l'enregistrement
                        $confirmation="<p class=\"ok\">Le compte a bien été enregistré</p>";  
                        }
                    }
                //========================================================================


                //on vide les champs du formulaire
                foreach($_POST AS $cle => $valeur)
                    {
                    //unset supprime une variable
                    unset($_POST[$cle]);    
                    }

                }  

            break;

            case "avertir_compte":
            
            if(isset($_GET['id_compte']))
                {
                $confirmation="<p>Voulez-vous supprimer le compte n°" . $_GET['id_compte'] . "</p>"; 
                $confirmation.="<a href=\"back.php?action=compte&cas=supprimer_compte&id_compte=" . $_GET['id_compte'] . "\">OUI</a>&nbsp;&nbsp;&nbsp;";
                $confirmation.="<a href=\"back.php?action=compte\">NON</a>";   
                }

            break;

            case "supprimer_compte":

            if(isset($_GET['id_compte']))
                {
                //on vérifie que ce n'est pas le dernier compte autorisé
                $requete="SELECT COUNT(*) AS nb_compte FROM comptes";   
                $resultat=mysqli_query($connexion,$requete);
                $ligne=mysqli_fetch_object($resultat);
                //si c'est le dernier compte de la table (1 seule ligne dans la table)
                if($ligne->nb_compte==1)
                    {
                    $confirmation="<p class=\"pas_ok\">Le dernier compte autorisé ne peut pas être supprimé</p>";   
                    }
                else{
                    //on vérifie si il y a une image associée au compte
                    $requete="SELECT * FROM comptes WHERE id_compte='" . $_GET['id_compte'] . "'";
                    $resultat=mysqli_query($connexion,$requete);
                    $ligne=mysqli_fetch_object($resultat);
                    //si il y a une image
                    if(!empty($ligne->img_compte))
                        {
                        $chemin_b=str_replace("_s","_b",$ligne->img_compte);
                        $chemin_m=str_replace("_s","_m",$ligne->img_compte);
                        $chemin_s=$ligne->img_compte;
                        //on supprime les fichiers image (le @ désactive les warning)
                        @unlink($chemin_b);
                        @unlink($chemin_m);
                        @unlink($chemin_s);  
                        }
                
                    $requete2="DELETE FROM comptes WHERE id_compte='" . $_GET['id_compte'] . "'";
                    $resultat2=mysqli_query($connexion,$requete2);
                    $confirmation="<p class=\"ok\">Le compte a bien été supprimé</p>";                    
                    }      
                }

            break;

            case "recharger_compte":
            
            if(isset($_GET['id_compte']))
                {
                $action_form="modifier_compte&id_compte=" . $_GET['id_compte'];
                //on recharge les champs du formulaire
                $requete="SELECT * FROM comptes WHERE id_compte='" . $_GET['id_compte'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                $ligne=mysqli_fetch_object($resultat);
                //on réattribue à chaque champ du formulaire la valeur récupérée dans la base
                $_POST['nom_compte']=$ligne->nom_compte;
                $_POST['prenom_compte']=$ligne->prenom_compte;
                $_POST['email_compte']=$ligne->email_compte;
                $_POST['login_compte']=$ligne->login_compte;
                //si le champ img_compte n'est pas vide
                if(!empty($ligne->img_compte))
                    {
                    $miniature="<div><img src=\"". $ligne->img_compte ."\" alt=\"\" />
                    <a href=\"back.php?action=compte&cas=supprimer_img_compte&id_compte=" . $ligne->id_compte . "\">supprimer</a></div>";
                    }
                //pour maintenir la selection de la liste déroulante
                if(isset($ligne->statut_compte))
                    {
                    $select[$ligne->statut_compte]="selected";   
                    }
                }

            break;

            case "supprimer_img_compte":

            if(isset($_GET['id_compte']))
                {
                //on va chercher les élements dans la table comptes
                $requete="SELECT * FROM comptes WHERE id_compte='" . $_GET['id_compte'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                $ligne=mysqli_fetch_object($resultat);
                $chemin_b=str_replace("_s","_b",$ligne->img_compte);
                $chemin_m=str_replace("_s","_m",$ligne->img_compte);
                @unlink($ligne->img_compte);
                @unlink($chemin_b);
                @unlink($chemin_m);

                $requete2="UPDATE comptes SET img_compte=NULL WHERE id_compte='" . $_GET['id_compte'] . "'";
                $resultat2=mysqli_query($connexion,$requete2);
                $confirmation="<p class=\"ok\">L'avatar a bien été supprimé</p>";    
                }

            break;

            case "modifier_compte":
         
            if(isset($_GET['id_compte']))
                {
                //pour maintenir la selection de la liste déroulante
                if(isset($_POST['statut_compte']))
                    {
                    $select[$_POST['statut_compte']]="selected";   
                    }

                //on met à jour la table
                if(empty($_POST['nom_compte']))
                    {
                    $confirmation="<p class=\"pas_ok\">Le nom est obligatoire</p>";   
                    }
                elseif(empty($_POST['statut_compte']))
                    {
                    $confirmation="<p class=\"pas_ok\">Le statut est obligatoire</p>";     
                    }
                elseif(empty($_POST['email_compte']))
                    {
                    $confirmation="<p class=\"pas_ok\">L'email est obligatoire</p>";     
                    }
                elseif(empty($_POST['login_compte']))
                    {
                    $confirmation="<p class=\"pas_ok\">Le login est obligatoire</p>";       
                    }
                else{
                    $requete="UPDATE comptes SET nom_compte='".security($_POST['nom_compte'])."',
                    prenom_compte='".security($_POST['prenom_compte'])."',
                    statut_compte='".$_POST['statut_compte']."',
                    email_compte='".security($_POST['email_compte'])."',
                    login_compte='".security($_POST['login_compte'])."'";

                    //CAS 1 : le mot de passe a été ressaisi
                    if(!empty($_POST['pass_compte']))
                        {
                        $requete.=",pass_compte=SHA1('" . $_POST['pass_compte'] . "')";  
                        }
                    $requete.=" WHERE id_compte='". $_GET['id_compte'] ."'"; 
                    //echo $requete;
                    $resultat=mysqli_query($connexion, $requete);

                    //si le champ parcourir est utilisé (pas vide)
                    if(!empty($_FILES['img_compte']['name']))
                        {
                        $tab_img=pathinfo($_FILES['img_compte']['name']);
                        $extension=$tab_img['extension'];
                        //on teste si l'esxtension est aurorisé
                        if($extension=="png" OR $extension=="gif" OR $extension=="jpg" OR $extension=="webp")
                            {
                            //si le fichier est bien uploadé du local vers le distant
                            if(is_uploaded_file($_FILES['img_compte']['tmp_name'])) 
                                {
                                //on détermine les chemins des 3 images à générer
                                $chemin_b="../medias/avatar" . $_GET['id_compte'] . "_b." . $extension; 
                                $chemin_m="../medias/avatar" . $_GET['id_compte'] . "_m." . $extension; 
                                $chemin_s="../medias/avatar" . $_GET['id_compte'] . "_s." . $extension; 
                                
                                if(copy($_FILES['img_compte']['tmp_name'], $chemin_b))
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
                                    $requete2="UPDATE comptes SET img_compte='" . $chemin_s . "' WHERE id_compte='" . $_GET['id_compte'] . "'";
                                    $resultat2=mysqli_query($connexion,$requete2);
                                    $confirmation="<p class=\"ok\">Le compte a bien été modifié</p>";  
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

    //on selectionne tous les comptes triés par date de création
    $requete="SELECT * FROM comptes ORDER BY id_compte ASC";
    $resultat=mysqli_query($connexion,$requete);
    //tant que $resultat contient des lignes (uplets)
    $content="";
    while($ligne=mysqli_fetch_object($resultat))
        {
        $content.="<details class=\"tab_results\">"; 

        $content.="<summary>"; 
        $content.="<div>". $ligne->id_compte ." - ". $ligne->login_compte ." / ". $ligne->email_compte ."</div>"; 
        $content.="<div>". $ligne->statut_compte ."</div>"; 
        //si il y a un avatar
        if(!empty($ligne->img_compte))
            {
            $content.="<div><img src=\"". $ligne->img_compte ."\" alt=\"\" /></div>";   
            }
        $content.="<div class=\"actions\"><a href=\"back.php?action=compte&cas=recharger_compte&id_compte=" . $ligne->id_compte . "\"><span class=\"dashicons dashicons-admin-customizer\"></a>"; 
        $content.="<a href=\"back.php?action=compte&cas=avertir_compte&id_compte=" . $ligne->id_compte . "\"><span class=\"dashicons dashicons-no\"></span></a></div>"; 
        $content.="</summary>"; 

        $content.="<div class=\"all\">".$ligne->nom_compte . " " . $ligne->prenom_compte ."</div>";

        $content.="</details>"; 
        }

    }
else{
    //l'utilisateur n'est pas autorisé
    header("Location:../log/login.php");
    }

?>
