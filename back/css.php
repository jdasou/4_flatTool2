 <?php
//on teste si la variable de session S_SESSION['id_compte'] existe
if(isset($_SESSION['id_compte']))
    {
    $titre="Gestion des feuilles de style personnalisées";
    $form="form_css.html";
    //action par défaut du formulaire
    $action_form="inserer_css";

    if(isset($_GET['cas']))
        {
        //on switche sur la valeur contenue dans $_GET['action']
        switch($_GET['cas'])
            {
            case "inserer_css":

            if(empty($_POST['titre_css']))
                {
                $confirmation="<p class=\"pas_ok\">Le titre est obligatoire</p>";  
                $color_champ['titre_css']="color_champ"; 
                }
            elseif(empty($_FILES['fichier_css']['name']))
                {
                $confirmation="<p class=\"pas_ok\">Le fichier css est obligatoire</p>";     
                $color_champ['fichier_css']="color_champ";     
                }                
            else{
                //on enregistre le compte dans la table comptes
                $requete="INSERT INTO css SET titre_css='".security($_POST['titre_css'])."'";                         
                $resultat=mysqli_query($connexion,$requete);

                //on récupere le dernier id_compte qui vient d'être créé
                $dernier_id_cree=mysqli_insert_id($connexion);

                //si le champ parcourir est utilisé (pas vide)
                if(!empty($_FILES['fichier_css']['name']))
                    {
                    $tab_img=pathinfo($_FILES['fichier_css']['name']);
                    $extension=$tab_img['extension'];
                    //on teste si l'esxtension est aurorisé
                    if($extension=="css")
                        {
                        //si le fichier est bien uploadé du local vers le distant
                        if(is_uploaded_file($_FILES['fichier_css']['tmp_name'])) 
                            {
                            //on détermine les chemins des 3 images à générer
                            $chemin="../css/style" . $dernier_id_cree . "." . $extension; 
                            
                            if(copy($_FILES['fichier_css']['tmp_name'], $chemin))
                                {
                                //on met à jour le champ img_compte de la table comptes 
                                $requete2="UPDATE css SET fichier_css='" . $chemin . "' WHERE id_css='" . $dernier_id_cree . "'";
                                $resultat2=mysqli_query($connexion,$requete2);
                                $confirmation="<p class=\"ok\">Le fichier css a bien été enregistré</p>";  
                                }

                            } 
                        }
                    else{
                        $confirmation="<p class=\"pas_ok\">Cette extension n'est pas autorisée</p>";  
                        }
                    }
                else{
                    //on confirme l'enregistrement
                    $confirmation="<p class=\"ok\">Le fichier css a bien été enregistré</p>";  
                    }

                //on vide les champs du formulaire
                foreach($_POST AS $cle => $valeur)
                    {
                    //unset supprime une variable
                    unset($_POST[$cle]); 
                    }
                }


            break;

            case "avertir_css":
            
            if(isset($_GET['id_css']))
                {
                $confirmation="<p>Voulez-vous supprimer la feuille de style n°" . $_GET['id_css'] . "</p>"; 
                $confirmation.="<a href=\"back.php?action=css&cas=supprimer_css&id_css=" . $_GET['id_css'] . "\">OUI</a>&nbsp;&nbsp;&nbsp;";
                $confirmation.="<a href=\"back.php?action=css\">NON</a>";   
                }

            break;

            case "supprimer_css":

            if(isset($_GET['id_css']))
                {
                //on recalcule le chemin du fichier css pour supprimer le fichier
                $chemin="../css/style" . $_GET['id_css'] . ".css";
                @unlink($chemin);
                
                //on supprime la ligne de la table
                $requete="DELETE FROM slider WHERE id_css='" . $_GET['id_css'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                $confirmation="<p class=\"ok\">Le fichier css a bien été supprimé</p>";  
                }

            break;

            case "recharger_css":
            
            if(isset($_GET['id_css']))
                {
                $action_form="modifier_css&id_css=" . $_GET['id_css'];
                //on recharge les champs du formulaire
                $requete="SELECT * FROM css WHERE id_css='" . $_GET['id_css'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                $ligne=mysqli_fetch_object($resultat);
                //on réattribue à chaque champ du formulaire la valeur récupérée dans la base
                $_POST['titre_css']=$ligne->titre_css;
                }

            break;

            case "modifier_css":
         
            if(isset($_GET['id_css']))
                {
                if(empty($_POST['titre_css']))
                    {
                    $confirmation="<p class=\"pas_ok\">Le titre est obligatoire</p>";  
                    $color_champ['titre_css']="color_champ"; 
                    }
                else{
                    $requete="UPDATE css SET titre_css='".security($_POST['titre_css'])."'  
                                                WHERE id_css='". $_GET['id_css'] ."'"; 
                    //echo $requete;
                    $resultat=mysqli_query($connexion, $requete);

                    //si le champ parcourir est utilisé (pas vide)
                    if(!empty($_FILES['fichier_css']['name']))
                        {
                        $tab_img=pathinfo($_FILES['fichier_css']['name']);
                        $extension=$tab_img['extension'];
                        //on teste si l'esxtension est aurorisé
                        if($extension=="css")
                            {
                            //si le fichier est bien uploadé du local vers le distant
                            if(is_uploaded_file($_FILES['fichier_css']['tmp_name'])) 
                                {
                                //on détermine les chemins des 3 images à générer
                                $chemin="../medias/slider" . $_GET['id_css'] . "." . $extension; 
                                
                                if(copy($_FILES['fichier_css']['tmp_name'], $chemin))
                                    {
                                    //on met à jour le champ img_compte de la table comptes 
                                    $requete2="UPDATE css SET fichier_css='" . $chemin . "' WHERE id_css='" . $_GET['id_css'] . "'";
                                    $resultat2=mysqli_query($connexion,$requete2);
                                    $confirmation="<p class=\"ok\">Le fichier a bien été modifié</p>";  
                                    }
                                } 
                            }
                        else{
                            $confirmation="<p class=\"pas_ok\">Cette extension n'est pas autorisée</p>";  
                            }
                        }
                    else{
                        //on confirme l'enregistrement
                        $confirmation="<p class=\"ok\">Le fichier a bien été modifié</p>";  
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

//======================================================================================================
    //tableau d'affichage des pages
    //on selectionne tous les pages triés par date de création et le compte correspondant
    $requete="SELECT * FROM css ORDER BY titre_css ASC";
    $resultat=mysqli_query($connexion,$requete);
    //tant que $resultat contient des lignes (uplets)
    $content="";
    $i=0;
    while($ligne=mysqli_fetch_object($resultat))
        {
        $content.="<details class=\"tab_results\">"; 
        $content.="<summary>";
        $content.="<div>". $ligne->id_css ." - ". $ligne->titre_css ."</div>";  
        $content.="<div class=\"actions\">";   
        $content.="<a href=\"back.php?action=css&cas=recharger_css&id_css=" . $ligne->id_css . "#form_back\"><span class=\"dashicons dashicons-admin-customizer\"></span></a>"; 
        $content.="<a href=\"back.php?action=css&cas=avertir_css&id_css=" . $ligne->id_css . "\"><span class=\"dashicons dashicons-no\"></span></a></div>"; 
        $content.="</div>";
        $content.="</summary>"; 
        $content.="<div class=\"all\"><a href=\"". $ligne->fichier_css ."\" target=\"_blank\">" . $ligne->fichier_css . "</a></div>"; 
        $content.="</details>";
        }

    }
else{
    //l'utilisateur n'est pas autorisé
    header("Location:../log/login.php");
    }

?>
