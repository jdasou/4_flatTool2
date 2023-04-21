 <?php
//on teste si la variable de session S_SESSION['id_rubrique'] existe
if(isset($_SESSION['id_compte']))
    {
    $titre="Gestion des rubriques";
    $form="form_rubrique.html";
    //action par défaut du formulaire
    $action_form="inserer_rubrique";
    //pour cocher par défaut visible à oui ou non
    $check1[1]="checked";//visible
    $check2[0]="checked";//slider

    if(isset($_GET['cas']))
        {
        //on switche sur la valeur contenue dans $_GET['action']
        switch($_GET['cas'])
            {
            case "inserer_rubrique":

            if(empty($_POST['nom_rubrique']))
                {
                $confirmation="<p class=\"pas_ok\">Le nom de la rubrique est obligatoire</p>";   
                }
            elseif(empty($_POST['titre_rubrique']))
                {
                $confirmation="<p class=\"pas_ok\">Le titre est obligatoire</p>";     
                }
            else{
                //on enregistre la rubrique dans la table rubriques
                $requete="INSERT INTO rubriques SET id_compte='" . $_SESSION['id_compte'] ."',
                                                nom_rubrique='".security($_POST['nom_rubrique'])."',
                                                titre_rubrique='".security($_POST['titre_rubrique'])."',
                                                lien_rubrique='".security($_POST['lien_rubrique'])."',
                                                visible='".$_POST['visible']."',
                                                slider='".$_POST['slider']."',
                                                date_rubrique=NOW()";
                $resultat=mysqli_query($connexion,$requete);

                //on confirme l'enregistrement
                $confirmation="<p class=\"ok\">La rubrique a bien été enregistrée</p>";  

                //on vide les champs du formulaire
                foreach($_POST AS $cle => $valeur)
                    {
                    //unset supprime une variable
                    unset($_POST[$cle]);    
                    }

                }  

            break;

            case "avertir_rubrique":
            
            if(isset($_GET['id_rubrique']))
                {
                $confirmation="<p>Voulez-vous supprimer la rubrique n°" . $_GET['id_rubrique'] . "</p>"; 
                $confirmation.="<a href=\"back.php?action=rubrique&cas=supprimer_rubrique&id_rubrique=" . $_GET['id_rubrique'] . "\">OUI</a>&nbsp;&nbsp;&nbsp;";
                $confirmation.="<a href=\"back.php?action=rubrique\">NON</a>";   
                }

            break;

            case "supprimer_rubrique":

            if(isset($_GET['id_rubrique']))
                {
                //on vérifie si il y a des pages associées à cette rubrique
                $requete="SELECT * FROM pages WHERE id_rubrique='" . $_GET['id_rubrique'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                //on calcule le nombre de lignes qu'il y a dans $resultat
                $nb=mysqli_num_rows($resultat);
                //si il y a des pages
                if($nb>0)
                    {
                    $confirmation="<p class=\"pas_ok\">Des pages sont encore associées à cette rubrique</p>";    
                    }
                else{
                    $requete2="DELETE FROM rubriques WHERE id_rubrique='" . $_GET['id_rubrique'] . "'";
                    $resultat2=mysqli_query($connexion,$requete2);
                    $confirmation="<p class=\"ok\">La rubrique a bien été supprimée</p>";  
                    }     
                }

            break;

            case "changer_etat":

            if(isset($_GET['id_rubrique']))
                {   
                if(isset($_GET['etat']))
                    {
                    $requete="UPDATE rubriques SET visible='" . $_GET['etat'] . "' WHERE id_rubrique='" . $_GET['id_rubrique'] . "'";
                    $resultat=mysqli_query($connexion,$requete);   
                    }
                }

            break;

            case "recharger_rubrique":
            
            if(isset($_GET['id_rubrique']))
                {
                $action_form="modifier_rubrique&id_rubrique=" . $_GET['id_rubrique'];
                //on recharge les champs du formulaire
                $requete="SELECT * FROM rubriques WHERE id_rubrique='" . $_GET['id_rubrique'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                $ligne=mysqli_fetch_object($resultat);
                //on réattribue à chaque champ du formulaire la valeur récupérée dans la base
                $_POST['nom_rubrique']=$ligne->nom_rubrique;
                $_POST['titre_rubrique']=$ligne->titre_rubrique;
                $_POST['lien_rubrique']=$ligne->lien_rubrique;
                $check1[$ligne->visible]="checked";
                $check2[$ligne->slider]="checked";
                }

            break;

            case "modifier_rubrique":
         
            if(isset($_GET['id_rubrique']))
                {
                //on met à jour la table

                if(empty($_POST['nom_rubrique']))
                    {
                    $confirmation="<p class=\"pas_ok\">Le nom de la rubrique est obligatoire</p>";   
                    }
                elseif(empty($_POST['titre_rubrique']))
                    {
                    $confirmation="<p class=\"pas_ok\">Le titre est obligatoire</p>";     
                    }
                else{
                    $requete="UPDATE rubriques SET id_compte='" . $_SESSION['id_compte'] ."',
                                                nom_rubrique='".$_POST['nom_rubrique']."',
                                                titre_rubrique='".security($_POST['titre_rubrique'])."',
                                                lien_rubrique='".security($_POST['lien_rubrique'])."',
                                                visible='".$_POST['visible']."',
                                                slider='".$_POST['slider']."',
                                                date_rubrique=NOW() WHERE id_rubrique='". $_GET['id_rubrique'] ."'"; 
                    //echo $requete;
                    $resultat=mysqli_query($connexion, $requete);

                    //on confirme l'enregistrement
                    $confirmation="<p class=\"ok\">La rubrique a bien été modifiée</p>";  
                        
                    //on vide les champs du formulaire
                    foreach($_POST AS $cle => $valeur)
                        {
                        //unset supprime une variable
                        unset($_POST[$cle]);    
                        }
                    }
                }
            break;
            }     
        }

    //tabelau d'affichage des rubriques
    //on selectionne tous les rubriques triés par date de création
    $requete="SELECT r.*, c.* FROM rubriques AS r 
                INNER JOIN comptes AS c 
                ON r.id_compte=c.id_compte 
                ORDER BY r.id_rubrique ASC";

    $resultat=mysqli_query($connexion,$requete);
    //tant que $resultat contient des lignes (uplets)
    $content="";
    while($ligne=mysqli_fetch_object($resultat))
        {
        $content.="<details class=\"tab_results\">"; 

        $content.="<summary>"; 
        //pour le tri
        $content.="<div class=\"actions\">";
        $content.="<a href=\"#\"><span class=\"dashicons dashicons-arrow-up\"></span></a>";
        $content.="<a href=\"#\"><span class=\"dashicons dashicons-arrow-down\"></span></a>";
        $content.="&nbsp;&nbsp;";
        $content.=$ligne->id_rubrique ." - ". $ligne->nom_rubrique ." / " . $ligne->titre_rubrique;
        $content.="</div>"; 

        if($ligne->visible==1)
            {
            $content.="<div class=\"actions\"><a href=\"back.php?action=rubrique&cas=changer_etat&etat=0&id_rubrique=" . $ligne->id_rubrique . "\"><span class=\"dashicons dashicons-visibility\"></span></a>";    
            }
        else{
            $content.="<div class=\"actions\"><a href=\"back.php?action=rubrique&cas=changer_etat&etat=1&id_rubrique=" . $ligne->id_rubrique . "\"><span class=\"dashicons dashicons-hidden\"></span></a>"; 
            }
        $content.="<a href=\"back.php?action=rubrique&cas=recharger_rubrique&id_rubrique=" . $ligne->id_rubrique . "#form_back\"><span class=\"dashicons dashicons-admin-customizer\"></span></a>"; 
        $content.="<a href=\"back.php?action=rubrique&cas=avertir_rubrique&id_rubrique=" . $ligne->id_rubrique . "\"><span class=\"dashicons dashicons-no\"></span></a></div>"; 
        $content.="</summary>"; 

        $content.="<div class=\"all\">Auteur : " . $ligne->prenom_compte . " " . $ligne->nom_compte . "<br>Créée le : ".$ligne->date_rubrique ."<br>Lien : " . $ligne->lien_rubrique . "</div>";

        $content.="</details>"; 
        }

    }
else{
    //l'utilisateur n'est pas autorisé
    header("Location:../log/login.php");
    }

?>
