<?

include("_gestionErreurs.inc.php");
include("gestionDonnees/_gestionBaseFonctionsCommunes.inc.php");

function chargementFichier(){

    $target_dir = "images/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;

    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

    // Check if image file is a actual image or fake image

    // Check file size
    if ($_FILES["fileToUpload"]["error"] == 1) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }


    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (! move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}



// 1ère étape (donc pas d'action choisie) : affichage du tableau des 
// établissements 
if (! isset($_REQUEST['action']))    
{
   $_REQUEST['action']='initial'; 
}

$action=$_REQUEST['action'];

// Aiguillage selon l'étape
switch ($action)
{
   case 'initial' :
      include("vues/GestionEtablissements/vObtenirEtablissements.php");  
      break;
   
   case 'detailEtab':
      $id=$_REQUEST['id'];  
      include("vues/GestionEtablissements/vObtenirDetailEtablissement.php");
      break;
   
   case 'demanderSupprimerEtab':   
      $id=$_REQUEST['id']; 
      include("vues/GestionEtablissements/vSupprimerEtablissement.php");
      break;
      
   case 'demanderCreerEtab':
      include("vues/GestionEtablissements/vCreerModifierEtablissement.php");   
      break;
   
   case 'demanderModifierEtab':   
      $id=$_REQUEST['id']; 
      include("vues/GestionEtablissements/vCreerModifierEtablissement.php");
      break;
      
   case 'validerSupprimerEtab':
      $id=$_REQUEST['id'];
      supprimerEtablissement($id);
      include("vues/GestionEtablissements/vObtenirEtablissements.php");   
      break;

   case 'validerCreerEtab':case 'validerModifierEtab':
         $id=$_REQUEST['id'];
         $nom=$_REQUEST['nom'];
         $adresseRue=$_REQUEST['adresseRue'];
         $codePostal=$_REQUEST['codePostal'];
         $ville=$_REQUEST['ville'];
         $tel=$_REQUEST['tel'];
         $adresseElectronique=$_REQUEST['adresseElectronique'];
         $type=$_REQUEST['type'];
         $civiliteResponsable=$_REQUEST['civiliteResponsable'];
         $nomResponsable=$_REQUEST['nomResponsable'];
         $prenomResponsable=$_REQUEST['prenomResponsable'];

         if ($action=='validerCreerEtab')
         {
            verifierDonneesEtabC($id, $nom, $adresseRue, $codePostal, $ville, $tel,
                                 $nomResponsable);
            if (nbErreurs()==0)
            {
               creerModifierEtablissement('C', $id, $nom, $adresseRue, $codePostal,
                                          $ville, $tel, $adresseElectronique, $type,
                                          $civiliteResponsable, $nomResponsable,
                                          $prenomResponsable);
               chargementFichier();

               include("vues/GestionEtablissements/vObtenirEtablissements.php");
            }
            else
            {
               include("vues/GestionEtablissements/vCreerModifierEtablissement.php");
            }
         }
         else
         {
            verifierDonneesEtabM($id, $nom, $adresseRue, $codePostal, $ville, $tel,
                                 $nomResponsable);
            if (nbErreurs()==0)
            {
               creerModifierEtablissement('M', $id, $nom, $adresseRue, $codePostal,
                                          $ville, $tel, $adresseElectronique, $type,
                                          $civiliteResponsable, $nomResponsable,
                                          $prenomResponsable);
               include("vues/GestionEtablissements/vObtenirEtablissements.php");
            }
            else
            {
               include("vues/GestionEtablissements/vCreerModifierEtablissement.php");
            }
         }
         break;


}   



function verifierDonneesEtabC($id, $nom, $adresseRue, $codePostal, $ville, $tel,
                              $nomResponsable)
{
   if ($id=="" || $nom=="" || $adresseRue=="" || $codePostal=="" || 
       $ville=="" || $tel=="" || $nomResponsable=="")
   {
      ajouterErreur('Chaque champ suivi du caractère * est obligatoire');
   }
   if($id!="")
   {
      // Si l'id est constitué d'autres caractères que de lettres non accentuées 
      // et de chiffres, une erreur est générée
      if (!estChiffresOuEtLettres($id))
      {
         ajouterErreur
         ("L'identifiant doit comporter uniquement des lettres non accentuées et des chiffres");
      }
      else
      {
         if (estUnIdEtablissement($id))
         {
            ajouterErreur("L'établissement $id existe déjà");
         }
      }
   }
   if ($nom!="" && estUnNomEtablissement('C', $id, $nom))
   {
      ajouterErreur("L'établissement $nom existe déjà");
   }
   if ($codePostal!="" && !estUnCp($codePostal))
   {
      ajouterErreur('Le code postal doit comporter 5 chiffres');   
   }
}

function verifierDonneesEtabM($id, $nom, $adresseRue, $codePostal, $ville, $tel,
                              $nomResponsable)
{
   if ($nom=="" || $adresseRue=="" || $codePostal=="" || $ville=="" || 
       $tel=="" || $nomResponsable=="")
   {
      ajouterErreur('Chaque champ suivi du caractère * est obligatoire');
   }
   if ($nom!="" && estUnNomEtablissement('M', $id, $nom))
   {
      ajouterErreur("L'établissement $nom existe déjà");
   }
   if ($codePostal!="" && !estUnCp($codePostal))
   {
      ajouterErreur('Le code postal doit comporter 5 chiffres');   
   }
}

function estUnCp($codePostal)
{
   // Le code postal doit comporter 5 chiffres
   return strlen($codePostal)== 5 && estEntier($codePostal);
}

?>
