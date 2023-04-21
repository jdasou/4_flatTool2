 <?php
//on teste si la variable de session S_SESSION['id_compte'] existe
if(isset($_SESSION['id_compte']))
    {
    $titre="Gestion de la messagerie";

    if(isset($_GET['cas']))
        {
        //on switche sur la valeur contenue dans $_GET['action']
        switch($_GET['cas'])
            {
            case "avertir_message":
            
            if(isset($_GET['id_contact']))
                {
                $confirmation="<p>Voulez-vous supprimer le message du contact n°" . $_GET['id_contact'] . "</p>"; 
                $confirmation.="<a href=\"back.php?action=messagerie&cas=supprimer_message&id_contact=" . $_GET['id_contact'] . "\">OUI</a>&nbsp;&nbsp;&nbsp;";
                $confirmation.="<a href=\"back.php?action=messagerie\">NON</a>";   
                }

            break;

            case "supprimer_message":

            if(isset($_GET['id_contact']))
                { 
                $requete="DELETE FROM contacts WHERE id_contact='" . $_GET['id_contact'] . "'";
                $resultat=mysqli_query($connexion,$requete);
                $confirmation="<p>Le message a bien été supprimé</p>";
                }

            break;
            }     
        }

    //on selectionne tous les contacts triés par date décroissante
    $requete="SELECT * FROM contacts ORDER BY date_contact DESC";
    $resultat=mysqli_query($connexion,$requete);
    //tant que $resultat contient des lignes (uplets)
    $content="";
    while($ligne=mysqli_fetch_object($resultat))
        {
        $content.="<details class=\"tab_results\">"; 

        $content.="<summary>"; 
        $content.="<div>". $ligne->date_contact ." - ".$ligne->email_contact ."</div>"; 
        $content.="<div class=\"actions\"><a href=\"back.php?action=messagerie&cas=avertir_message&id_contact=" . $ligne->id_contact . "\"><span class=\"dashicons dashicons-no\"></span></a></div>"; 
        $content.="</summary>"; 

        $content.="<div class=\"all\">Nom : " . $ligne->prenom_contact . " " . $ligne->nom_contact . "<br><br>Message : " . $ligne->message_contact . "</div>";

        $content.="</details>"; 
        }

    }
else{
    //l'utilisateur n'est pas autorisé
    header("Location:../log/login.php");
    }

?>
