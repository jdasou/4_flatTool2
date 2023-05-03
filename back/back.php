 <?php
//permet d'autoriser l'usage des variables de session
session_start();

//on calcule le message de bienvenue pour la personne connectée
$titre="";
if(isset($_SESSION['img_compte']))
    {
    $titre.="<img class=\"avatar\" src=\"" . $_SESSION['img_compte'] . "\" alt=\"\" />";   
    }
$titre.="Bienvenue " . $_SESSION['prenom_compte'] . " " . $_SESSION['nom_compte'] . " [" . $_SESSION['statut_compte'] . "]";


//on teste si la variable de session S_SESSION['id_compte'] 
//existe
if(isset($_SESSION['id_compte']))
    {
    //on connecte le fichier de fonctions
    require_once("../outils/fonctions.php");

    //on établit une connexion avec la BDD
    $connexion=connexion();

    if(isset($_GET['action']))
        {

        //on switche sur la valeur contenue dans $_GET['action']
        switch($_GET['action'])
            {
            case "logout":
            //détruit toutes les variables de SESSION qui ont été enregistrées
            session_destroy();
            //on redirige vers la page d'accueil du site
            header("Location:../index.php");
            break;

            case "messagerie":

            include("messagerie.php");

            break;

            case "compte":

            include("compte.php");
    
            break;

            case "rubrique":

            include("rubrique.php");
        
            break;

            case "page":

            include("page.php");
        
            break;

            case "slider":

                include("slider.php");
            
            break;

            case "reset":

                include("reset.php");
            
            break;

            case "css":
                include ("css.php");

            break;

            }

        }


    //permet de relier front.php avec front.html
    include("back.html");

    //on referme la connexion
    mysqli_close($connexion);
    }
else{
    //l'utilisateur n'est pas autorisé
    header("Location:../log/login.php");
    }

?>
