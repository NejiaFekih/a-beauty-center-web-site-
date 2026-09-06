<?php  
    session_start(); 
    $np=$_POST["np"];
    $email=$_POST["email"];
    $mp=$_POST["mp"];
    require_once 'config.php';
    $c = getConnexionDB();
    $req=" SELECT * FROM client where np='$np' or email='$email' ;";
    $res=mysqli_query($c,$req);
    $nl=mysqli_num_rows($res);
    if ($nl>=1)
        {
            echo("utilisatur déjà existe!!");
        }
    else{
        $req="INSERT INTO client  VALUES('$np','$email','$mp');";
        mysqli_query($c,$req);
        // On enregistre les infos dans la session
        $_SESSION["connecte"] = true;
        $_SESSION["nom"] = $np;
        $_SESSION["email"] = $email;

        header("Location: page_d_utilisateur.php"); // redirige vers la page d'accueil
        exit();
    }
    mysqli_close($c);
?>